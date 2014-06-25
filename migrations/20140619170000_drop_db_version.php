<?php

use Phinx\Migration\AbstractMigration;

/************************************
 * Class DropDbVersion
 *
 * Removes the spurious table dbversion which became obsolete thanks to phinx
 *
 * See ticket: #2219
 *
 */
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