<?php

use Rox\Tools\RoxMigration;

class ChangeMessageTableAttributes extends RoxMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE
                messages
            CHANGE COLUMN
                `WhenFirstRead` `WhenFirstRead` TIMESTAMP, 
            CHANGE COLUMN `InFolder` `InFolder` 
                ENUM('Normal', 'junk', 'Spam', 'Draft', 'requests') NOT NULL DEFAULT 'Normal' 
                COMMENT 'The folder where the message is to be stored, note that Comment, Broadcast, evaluate, meeting will be sent through notifications' ;
            CHANGE COLUMN 
                `IdParent` `IdParent` INT(11) NULL DEFAULT NULL COMMENT 'specific for chained messages (when someone reply to a previous message)' ;
        ");

    }
}
