<?php

use Rox\Tools\RoxMigration;

class ChangeMessageTableAttributes extends RoxMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE
                messages
            MODIFY
                WhenFirstRead TIMESTAMP NULL
            CHANGE COLUMN 
                `IdParent` `IdParent` INT(11) NULL DEFAULT NULL COMMENT 'specific for chained messages (when someone reply to a previous message)' ;
        ");

    }
}
