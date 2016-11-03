<?php

use Illuminate\Database\Capsule\Manager;
use Rox\Member\Model\Member;
use Rox\Trip\Model\Trip;
use Rox\Trip\Model\SubTrip;

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
    echo print_r($tripRaw, true);
    $curTrip = $tripRaw->trip_id;
    if ($lastTrip <> $curTrip) {
        $trip = new Trip();
        $trip->title = $tripRaw->trip_name;
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
