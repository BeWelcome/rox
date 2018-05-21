<?php


use Rox\Tools\RoxMigration;

class DropHcvolConfigTable extends RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('hcvol_config');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // No down migration needed in this case as table isn't used anymore.
    }
}
