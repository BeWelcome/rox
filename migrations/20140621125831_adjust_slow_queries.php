<?php

use Phinx\Migration\AbstractMigration;

/************************************************
 * Class AdjustSlowQueries
 *
 * Adjusts some indices to reduce the number of slow queries
 *
 * See ticket: #2216
 */
class AdjustSlowQueries extends AbstractMigration
{
    /**
     * Change.
     */
    public function change()
    {
        // add some indices
        $table = $this->table('forums_threads');
        $table->addIndex(array("ThreadVisibility"))
            ->addIndex(array("ThreadDeleted"))
            ->update();

        $table = $this->table('forums_posts');
        $table->addIndex(array("PostVisibility"))
            ->addIndex(array("PostDeleted"))
            ->addIndex(array("create_time"))
            ->update();

        $table = $this->table('messages');
        $table->addIndex(array("DeleteRequest"))
            ->addIndex(array("WhenFirstRead"))
            ->update();
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}

