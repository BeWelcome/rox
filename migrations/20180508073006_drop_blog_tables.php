<?php


use Rox\Tools\RoxMigration;

class DropBlogTables extends RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('blog_tags_seq')->drop()->save();
        $this->table('blog_tags')->drop()->save();
        echo "Dropped table";
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // No down migration needed in this case as table isn't used anymore.
    }
}
