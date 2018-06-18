<?php


use Rox\Tools\RoxMigration;

class DropHcvolConfigTable extends RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('hcvol_config')->drop()->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // No down migration needed in this case as table isn't used anymore.
    }
}
