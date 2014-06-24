<?php

use Phinx\Migration\AbstractMigration;

// Ticket #2216
class AdjustSlowQueries extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('forums_threads');
        $table->addIndex(array("ThreadVisibility"))
            ->addIndex(array("ThreadDeleted"))
            ->save();

        $table = $this->table('forums_posts');
        $table->addIndex(array("PostVisibility"))
            ->addIndex(array("PostDeleted"))
            ->addIndex(array("create_time"))
            ->save();

        $table = $this->table('messages');
        $table->addIndex(array("DeleteRequest"))
            ->addIndex(array("WhenFirstRead"))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('messages');
        $table->removeIndex(array("DeleteRequest"));
        $table->removeIndex(array("WWhenFirstRead"));

        $table = $this->table('forums_posts');
        $table->removeIndex(array("PostVisibility"))
            ->removeIndex(array("PostDeleted"))
            ->removeIndex(array("create_time"))
            ->save();

        $table = $this->table('forums_threads');
        $table->removeIndex(array("ThreadVisibility"))
            ->removeIndex(array("ThreadDeleted"))
            ->save();

    }
}