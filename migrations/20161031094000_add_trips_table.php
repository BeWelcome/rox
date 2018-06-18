<?php

use Illuminate\Database\Capsule\Manager;
use Phinx\Migration\AbstractMigration;
use Rox\Member\Model\Member;
use Rox\Trip\Model\Trip;
use Rox\Trip\Model\SubTrip;

/**
 * Class AddTripsTables
 *
 * Adds the trips and subtrips tables.
 * Migrates the old trips into new ones.
 *
 * Ticket (github): #1
 */
class AddTripsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("
            CREATE TABLE `trips` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `summary` VARCHAR(150) NULL,
                `description` VARCHAR(4096) NULL,
                `countOfTravellers` INT NULL,
                `created_by` INT NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT NULL ,
                `deleted_at` DATETIME DEFAULT NULL,
                `additionalInfo` INT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `id_UNIQUE` (`id` ASC),
                INDEX `memberId_idx` (`created_by` ASC),
                CONSTRAINT `created_by`
                  FOREIGN KEY (`created_by`)
                  REFERENCES `members` (`id`)
                  ON DELETE CASCADE
                  ON UPDATE NO ACTION
            )
            ENGINE = InnoDB
            COMMENT = 'Stores the information about the general trip parts. The subtrips tables links into it.';
        ");
        $this->execute("
            CREATE TABLE `sub_trips` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `trip_id` INT NOT NULL,
                `geonameId` INT NULL,
                `arrival` DATE NULL,
                `departure` DATE NULL,
                `options` INT NULL,
                PRIMARY KEY (`id`),
                INDEX `trip_id_idx` (`trip_id` ASC),
                CONSTRAINT `tripId`
                  FOREIGN KEY (`trip_id`)
                  REFERENCES `trips` (`id`)
                  ON DELETE CASCADE
                  ON UPDATE NO ACTION
            )
            ENGINE = InnoDB
            COMMENT = 'Stores the information about subtrips. Links into trips.';"
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('sub_trips')->drop()->save();
        $this->table('trips')->drop()->save();
    }
}