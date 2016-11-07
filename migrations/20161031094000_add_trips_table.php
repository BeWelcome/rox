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
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `deleted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
        // Migrate as much as possible of the old trips
        // this needs to be done programatically to avoid missing geonameID, or locations like mountains or countries
        //
        // We use the Eloquent model for this

        require_once 'bootstrap/autoload.php';


        $capsule = new Manager();

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'options' => [
                PDO::MYSQL_ATTR_INIT_COMMAND =>
                    "SET NAMES 'UTF8', time_zone = '+00:00', sql_mode='NO_ENGINE_SUBSTITUTION';",
            ],
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // Migrate as much as possible of the old trips
        // this needs to be done in code to avoid missing geonameID, or locations like mountains or countries
                $trips = $capsule::select('
            SELECT
                b.IdMember,
                t.trip_id,
                td.trip_name,
                td.trip_descr,
                bd.blog_start,
                bd.blog_end,
                bd.blog_geonameId
            FROM
                blog b
            LEFT JOIN trip t ON b.trip_id_foreign = t.trip_id
            LEFT JOIN trip_data td ON t.trip_id = td.trip_id
            LEFT JOIN blog_data bd ON b.blog_id = bd.blog_id
            WHERE
                NOT (trip_id_foreign IS NULL)
                AND NOT (bd.blog_geonameID IS NULL)
                AND NOT(bd.blog_start IS NULL AND bd.blog_end IS NULL)
            ORDER BY
                b.trip_id_foreign, b.blog_id, bd.blog_start, bd.blog_end
        ');

        $lastTrip = -1;
        $trip = null;
        foreach ($trips as $tripRaw) {
            $curTrip = $tripRaw->trip_id;
            if ($lastTrip <> $curTrip) {
                $trip = new Trip();
                $trip->summary = $tripRaw->trip_name;
                $trip->description = '('.$curTrip.') '.$tripRaw->trip_descr;
                $trip->created_by = $tripRaw->IdMember;
                $trip->countOfTravellers = 1;
                $trip->save();
                $lastTrip = $curTrip;
            }
            $trip->subtrips()->create([
                'geonameId' => $tripRaw->blog_geonameId,
                'arrival' => $tripRaw->blog_start,
                'departure' => $tripRaw->blog_end,
            ]);
            $trip->save();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('sub_trips');
        $this->dropTable('trips');
    }
}