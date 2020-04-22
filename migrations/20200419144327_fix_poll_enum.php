<?php

use Rox\Tools\RoxMigration;

class FixPollEnum extends RoxMigration
{
    public function up()
    {
        $this->execute("ALTER TABLE `polls` CHANGE COLUMN `Status` `Status`
            ENUM('Project', 'Open', 'Closed') NOT NULL DEFAULT 'Project' COMMENT 'Status of the poll'");
        $this->execute("UPDATE `polls` SET `Status` = 'Closed' where `Status` = ''");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `polls` CHANGE COLUMN `Status` `Status`
            ENUM('Project', 'Open', 'Close') NOT NULL DEFAULT 'Project' COMMENT 'Status of the poll'");
        $this->execute("UPDATE `polls` SET `Status` = 'Close' where `Status` = ''");
    }
}
