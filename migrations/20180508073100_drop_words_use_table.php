<?php


use Rox\Tools\RoxMigration;

class DropWordsUseTable extends RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('words_use');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // No down migration needed in this case as table isn't used anymore.
    }
}
