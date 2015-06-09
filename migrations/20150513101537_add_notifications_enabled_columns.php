<?php

use Phinx\Migration\AbstractMigration;

class AddNotificationsEnabledColumns extends AbstractMigration
{
    /**
     * Migrate Up.
     */public function up()
    {
        $this->execute("
            ALTER TABLE `membersgroups`
            ADD COLUMN `notificationsEnabled` TINYINT NOT NULL DEFAULT 1 COMMENT 'Boolean flag that temporarily enables or disables notifications' AFTER `CanSendGroupMessage`;
        ");
        $this->execute("
            UPDATE `membersgroups` SET `notificationsEnabled` = 0 WHERE `IacceptMassMailFromThisGroup` = 'yes'
        ");
        $this->execute("
            ALTER TABLE `members_threads_subscribed`
            ADD COLUMN `notificationsEnabled` TINYINT NOT NULL DEFAULT 1 COMMENT 'Boolean flag that temporarily enables or disables notifications' AFTER `created` ;
        ");
        $this->execute("
            ALTER TABLE `members_tags_subscribed`
            ADD COLUMN `notificationsEnabled` TINYINT NOT NULL DEFAULT 1 COMMENT 'Boolean flag that temporarily enables or disables notifications' AFTER `created`;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->execute('
            TABLE `membersgroups`
            DROP COLUMN `notificationsEnabled`;
        ');
        $this->execute('
            TABLE `members_threads_subscribed`
            DROP COLUMN `notificationsEnabled`;
        ');
        $this->execute('
            TABLE `members_tags_subscribed`
            DROP COLUMN `notificationsEnabled`;
        ');
    }
}