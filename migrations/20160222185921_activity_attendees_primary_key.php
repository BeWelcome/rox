<?php

use Rox\Tools\RoxMigration;

class ActivityAttendeesPrimaryKey extends RoxMigration
{
    public function up() {
        $this->execute("
            ALTER TABLE 
                `activitiesattendees` 
            ADD COLUMN `id` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key' AFTER `comment`,
            ADD PRIMARY KEY (`id`)  COMMENT '',
            ADD UNIQUE INDEX `id_UNIQUE` (`id` ASC)  COMMENT '';
        ");
    }

    public function down() {
        $this->execute("
            ALTER TABLE `activitiesattendees` 
            DROP COLUMN `id`;
        ");
    }
}
