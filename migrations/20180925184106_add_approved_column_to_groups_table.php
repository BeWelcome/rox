<?php


use Rox\Tools\RoxMigration;

class AddApprovedColumnToGroupsTable extends RoxMigration
{
    public function up()
    {
        // Remove on update on create
        $this->execute('ALTER TABLE `groups` CHANGE COLUMN `created` `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;');

        $groups = $this->table('groups');
        $groups
            ->addColumn('approved', 'boolean')
            ->save();

        // Set all existing groups to approved!
        $this->execute('UPDATE `groups` SET `approved`  = 1');
    }

    public function down()
    {
        $groups = $this->table('groups');
        $groups
            ->removeColumn('approved')
            ->save();
    }
}
