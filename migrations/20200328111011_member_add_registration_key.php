<?php

use App\Doctrine\MemberStatusType;
use Rox\Tools\RoxMigration;

class MemberAddRegistrationKey extends RoxMigration
{
    public function up()
    {
        $members = $this->table('members');
        $members
            ->addColumn('registration_key', 'string', [
                'after' => 'created',
            ])
            ->update()
        ;

        $this->query("UPDATE members SET `registration_key` = SHA2(CONCAT(LOWER(email), ' - ', LOWER(username)), 256) WHERE Status = '" . MemberStatusType::AWAITING_MAIL_CONFIRMATION . "';");
    }

    public function down()
    {
        $members = $this->table('members');
        $members
            ->removeColumn('registration_key')
            ->update()
        ;
    }
}
