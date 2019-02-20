<?php


use Rox\Tools\RoxMigration;

class AddPurgeOptionsToMessagesAndRequests extends RoxMigration
{
    public function up()
    {
        $this->execute("ALTER TABLE `messages` CHANGE COLUMN `DeleteRequest` `DeleteRequest` 
            SET('senderdeleted', 'receiverdeleted', 'senderpurged', 'receiverpurged') NOT NULL 
            COMMENT 'Mark who has ask to deleted/purged the message (and thus will not be able to see it anymore)
            both values can coexist'");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `messages` CHANGE COLUMN `DeleteRequest` `DeleteRequest` 
            SET('senderdeleted', 'receiverdeleted') NOT NULL 
            COMMENT 'Mark who has ask to purged the message (and thus will not be able to see it anymore)
            both values can coexist'");
    }
}
