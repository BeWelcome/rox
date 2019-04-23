<?php

use Doctrine\DBAL\Driver\PDOConnection;
use Symfony\Component\Dotenv\Dotenv;
use Rox\Tools\RoxMigration;

class JoinMembersAndCryptedfieldsTable extends RoxMigration
{
    public function up()
    {
        $members = $this->table('members');
        $members
            ->addColumn('EmailText', 'string', [ 'after' => 'Email'])
            ->addColumn('FirstNameText', 'string', [ 'after' => 'FirstName' ])
            ->addColumn('SecondNameText', 'string', [ 'after' => 'SecondName', 'null' => true ])
            ->addColumn('LastNameText', 'string', [ 'after' => 'LastName' ])
            ->addColumn('HideAttribute', 'biginteger')
            ->save();
        ;

        $dotenv = new Dotenv();
        $dotenv->load('.env', '.env.local');

        $connection = new PDOConnection(
            'mysql:mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );

        // Collect information for 'active' members and update members table. Don't bother with asktoleave and the other statuses
        $sql = "SELECT id, FirstName, SecondName, LastName, Email fROM members WHERE Status IN ('Active', 'OutOfRemind', 'Banned', 'ChoiceInactive', 'Pending', 'SuspendedBeta', 'MailToConfirm') and EmailText is null ORDER BY Id";
        $stmt = $connection->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cryptedSql = "SELECT * FROM cryptedfields WHERE id IN (" . $row['FirstName'] . ", " . $row['SecondName'] . ", " . $row['LastName'] . ", " . $row['Email'] . ")";
            $cryptedStmt = $connection->query($cryptedSql);
            $cryptedRows = $cryptedStmt->fetchAll();
            $updateSql = 'UPDATE members SET ';
            $hideAttribute = 0;
            $updateNeeded = false;
            foreach ($cryptedRows as $cryptedRow) {
                if ($cryptedRow['TableColumn'] <> 'NotSet') {
                    if (!empty($cryptedRow['MemberCryptedValue'])) {
                        $updateNeeded = true;
                        $updateSql .= str_replace('members.', '', $cryptedRow['TableColumn']) . "Text = " .
                            $connection->quote(strip_tags(
                                urldecode(
                                    $cryptedRow['MemberCryptedValue']
                                )
                            )) . ", ";
                        if (($cryptedRow['IsCrypted'] == 'crypted') || ($cryptedRow['IsCrypted'] == 'always')) {
                            switch ($cryptedRow['TableColumn']) {
                                case 'members.FirstName':
                                    $hideAttribute |= \Member::MEMBER_FIRSTNAME_HIDDEN;
                                    break;
                                case 'members.SecondName':
                                    $hideAttribute |= \Member::MEMBER_SECONDNAME_HIDDEN;
                                    break;
                                case 'members.LastName':
                                    $hideAttribute |= \Member::MEMBER_LASTNAME_HIDDEN;
                                    break;
                                case 'members.Email':
                                    $hideAttribute |= \Member::MEMBER_EMAIL_HIDDEN;
                                    break;
                            }
                        }
                    }
                }
            }
            $updateSql = substr($updateSql, 0, -2);
            $updateSql .= ", HideAttribute = " . $hideAttribute;
            $updateSql .= " WHERE id = " . $row['id'];
            file_put_contents('members.sql', $updateSql . PHP_EOL, FILE_APPEND);
            // echo $updateSql . PHP_EOL;
            if ($updateNeeded) {
                $connection->query($updateSql);
            }
        };

        // drop original fields
        $members
            ->removeColumn('email')
            ->removeColumn('FirstName')
            ->removeColumn('SecondName')
            ->removeColumn('LastName')
            ->save();
        // rename new fields
        $members
            ->renameColumn('emailText', 'Email')
            ->renameColumn('FirstNameText', 'FirstName')
            ->renameColumn('SecondNameText', 'SecondName')
            ->renameColumn('LastNameText', 'LastName')
            ->save();
    }

    public function down()
    {
        // Can't be undone
    }
}
