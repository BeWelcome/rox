<?php


use Rox\Tools\RoxMigration;

class RemoveOldGeonamesTables extends RoxMigration
{
    public function up()
    {
        $this->table('geonames_cache')->drop()->save();
        $this->table('geonames_admincodes')->drop()->save();
        $this->table('geonames_countries')->drop()->save();
        $this->table('geonames_timezones')->drop()->save();
    }

    public function down()
    {
        // tables no longer needed in the code so no need to rebuild them
    }
}
