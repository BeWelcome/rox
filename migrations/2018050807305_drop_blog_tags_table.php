<?php


use Rox\Tools\RoxMigration;

class DropBlogTagsTable extends RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('blog_tags');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // No down migration needed in this case as table isn't used anymore.
    }
}
