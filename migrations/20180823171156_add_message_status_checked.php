<?php


use Rox\Tools\RoxMigration;

class AddMessageStatusChecked extends RoxMigration
{
    /**
     * Add 'Checked' status
     */
    public function up()
    {
        $this->execute("
            ALTER TABLE `messages` 
            CHANGE COLUMN `Status` `Status` ENUM('Draft', 'ToCheck', 'Checked', 'ToSend', 'Sent', 'Freeze') NOT NULL DEFAULT 'ToCheck' COMMENT 'Status for sending attempts'");
    }

    /**
     * Remove 'Checked' status
     */
    public function down()
    {
        $this->execute("
            ALTER TABLE `messages` 
            CHANGE COLUMN `Status` `Status` ENUM('Draft', 'ToCheck', 'ToSend', 'Sent', 'Freeze') NOT NULL DEFAULT 'ToCheck' COMMENT 'Status for sending attempts'");
    }
}
