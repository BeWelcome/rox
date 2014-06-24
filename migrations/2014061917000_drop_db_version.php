<?php

use Phinx\Migration\AbstractMigration;

class DropDbVersion extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('dbversion');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // No down migration needed in this case as table was never used.
    }
}