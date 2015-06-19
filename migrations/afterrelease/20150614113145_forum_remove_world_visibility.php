<?php

use Phinx\Migration\AbstractMigration;

class ForumRemoveWorldVisibility extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("
            UPDATE
                forums_posts
            SET
                PostVisibility = 'MembersOnly'
            WHERE
                PostVisibility = 'NoRestriction';
        ");
        $this->execute("
            UPDATE
                forums_threads
            SET
                ThreadVisibility = 'MembersOnly'
            WHERE
                ThreadVisibility = 'NoRestriction';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // This migration isn't reversible
    }
}