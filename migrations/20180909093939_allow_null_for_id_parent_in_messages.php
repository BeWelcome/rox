<?php


use Rox\Tools\RoxMigration;

class AllowNullForIdParentInMessages extends RoxMigration
{
    public function up()
    {
        // Allow IdParent to be null
        $this->execute("
            ALTER TABLE `messages` 
            CHANGE COLUMN `IdParent` `IdParent` INT(11) NULL");
    }

    public function down()
    {
        // Allow IdParent to be null
        $this->execute("
            ALTER TABLE `messages` 
            CHANGE COLUMN `IdParent` `IdParent` INT(11) NOT NULL");
    }
}
