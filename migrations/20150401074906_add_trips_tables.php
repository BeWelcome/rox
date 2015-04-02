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
        $this->execute("
            ALTER TABLE trips ADD COLUMN `oldTripId` INT;
            INSERT INTO trips SELECT NULL, td.trip_name, td.trip_descr, NULL, t.IdMember, t.trip_touched, NULL, t.trip_id from trip t, trip_data td WHERE t.trip_id = td.trip_id;
            INSERT INTO subtrips select NULL, t.id, bd.blog_geonameid, blog_start, blog_end, NULL from trips t, trip_data td, blog b, blog_data bd WHERE t.oldTripId = td.trip_id AND td.trip_id = b.trip_id_foreign AND b.blog_id = bd.blog_id AND bd.blog_start IS NOT NULL AND bd.blog_geonameid IS NOT NULL ORDER BY td.trip_id, bd.blog_start;
            ALTER TABLE trips DROP COLUMN `oldTripId`;
         ");
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