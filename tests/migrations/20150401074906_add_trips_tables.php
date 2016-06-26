<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class AddTripsTables
 *
 * Adds the trips and subtrips tables.
 * Migrates the old trips into new ones.
 *
 * Ticket (github): #1
 */
class AddTripsTables extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("
            CREATE TABLE `trips` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(150) NULL,
                `description` VARCHAR(4096) NULL,
                `countOfTravellers` INT NULL,
                `memberId` INT NULL,
                `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `additionalInfo` INT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `id_UNIQUE` (`id` ASC),
                INDEX `memberId_idx` (`memberId` ASC),
                CONSTRAINT `memberId`
                  FOREIGN KEY (`memberId`)
                  REFERENCES `members` (`id`)
                  ON DELETE CASCADE
                  ON UPDATE NO ACTION
            )
            ENGINE = InnoDB
            COMMENT = 'Stores the information about the general trip parts. The subtrips tables links into it.';
        ");
        $this->execute("
            CREATE TABLE `subtrips` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `tripId` INT NULL,
                `geonameId` INT NULL,
                `arrival` DATE NULL,
                `departure` DATE NULL,
                `options` INT NULL,
                PRIMARY KEY (`id`),
                INDEX `tripId_idx` (`tripId` ASC),
                CONSTRAINT `tripId`
                  FOREIGN KEY (`tripId`)
                  REFERENCES `trips` (`id`)
                  ON DELETE CASCADE
                  ON UPDATE NO ACTION
            )
            ENGINE = InnoDB
            COMMENT = 'Stores the information about subtrips. Links into trips.';"
        );
        // Migrate as much as possible of the old trips
        // this needs to be done programatically to avoid missing geonameID, or locations like mountains or countries
        // \todo Write migration code
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('trips');
        $this->dropTable('subtrips');
    }
}