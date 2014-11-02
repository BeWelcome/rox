<?php

use Phinx\Migration\AbstractMigration;

/*************************************
 * Class FlagsMigration
 *
 * Removes the PhotoFilePath column that was replaced by membersphotos table a long time ago
 *
 * See ticket: #todo
 */
class RemovePhotoFilePath extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table("members");
        $table->removeColumn("PhotoFilePath");
        $table->update();
    }
}