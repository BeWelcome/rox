<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The coordinators of the teams of Get active (App\Entity\TeamCoordinator), set by the admins from now on. They
 * start as the members the English contact texts linked to (getactive{team}contact: a link to a new message or to
 * the profile of the member). A team whose text links elsewhere, or to a member that doesn't exist, starts without.
 */
final class Version20261112100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create team_coordinator, filled from the contacts of the Get active page.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS team_coordinator (team VARCHAR(32) NOT NULL, member_id INT NOT NULL, INDEX IDX_FECEE1277597D3FE (member_id), PRIMARY KEY (team)) DEFAULT CHARACTER SET utf8mb4');
        $exists = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = 'team_coordinator' AND constraint_name = 'FK_FECEE1277597D3FE'",
        );
        if (!$exists) {
            $this->connection->executeStatement('ALTER TABLE team_coordinator ADD CONSTRAINT FK_FECEE1277597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE');
        }

        // the teams of App\Model\About\VolunteerTeams
        foreach (['dev', 'testing', 'sysadmin', 'design', 'support', 'translate', 'pr', 'nmbw', 'mod', 'local', 'donation', 'bv'] as $team) {
            $contact = $this->connection->fetchOne(
                "SELECT Sentence FROM word WHERE code = ? AND ShortCode = 'en'",
                ['getactive' . $team . 'contact'],
            );
            if (!\is_string($contact) || !preg_match('#(?:new/message|members)/([^"/?\#]+)#', $contact, $match)) {
                continue;
            }
            $this->connection->executeStatement(
                'INSERT IGNORE INTO team_coordinator (team, member_id) SELECT ?, id FROM member WHERE Username = ?',
                [$team, rawurldecode($match[1])],
            );
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS team_coordinator');
    }
}
