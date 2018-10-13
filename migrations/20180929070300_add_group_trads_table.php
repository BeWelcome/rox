<?php


use Rox\Tools\RoxMigration;

class AddGroupTradsTable extends RoxMigration
{
    public function up()
    {
        $groupTrads = $this->table('groups_trads',  ['id' => false, 'primary_key' => ['group_id', 'trad_id']]);
        $groupTrads
            ->addColumn('group_id', 'integer')
            ->addColumn('trad_id', 'integer')
            ->addIndex(['group_id'])
            ->addIndex(['trad_id'], ['unique' => true])
            ->create();
        $this->execute('INSERT INTO groups_trads ( group_id, trad_id) SELECT g.id, mt.id FROM groups g, memberstrads mt where g.IdDescription = mt.IdTrad');
    }

    public function down()
    {
        $groupTrads = $this->table('groups_trads');
        $groupTrads->drop()
            ->save();
    }
}
