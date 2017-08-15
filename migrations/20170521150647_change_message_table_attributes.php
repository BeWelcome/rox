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
            CHANGE COLUMN 
                `IdParent` `IdParent` INT(11) NULL DEFAULT NULL COMMENT 'specific for chained messages (when someone reply to a previous message)' ;
        ");

    }
}
