<?php

use Phinx\Migration\AbstractMigration;

class SubscriptionsRight extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("
            INSERT INTO
                rights
            SET
                Name = 'ManageSubscriptions',
                Description = 'Members with this right can change subscriptionssettings for a member. Level = 1, Scope = \"All\"'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
            DELETE FROM
                rights
            WHERE
                Name = 'ManageSubscriptions'");
    }
}