<?php

use Rox\Tools\RoxMigration;

class SetThreadTimeStamps extends RoxMigration
{
    public function up() {
        $table = $this->table('forums_threads');
        $table->addColumn('created_at', 'timestamp');
        $table->addColumn('updated_at', 'timestamp');
        $table->addColumn('deleted_at', 'timestamp');
        $table->update();

        $this->execute('
            UPDATE
                forums_threads t
            INNER JOIN forums_posts p ON p.id = t.first_postid
            SET
                created_at = forums_posts.create_time
        ');
    }

    public function down() {
        $table = $this->table('forums_threads');
        $table->removeColumn('created_at');
        $table->removeColumn('updated_at');
        $table->removeColumn('deleted_at');
        $table->update();
    }
}
