<?php


use Rox\Tools\RoxMigration;

class AddGroupMembershipTradsTable extends RoxMigration
{
    public function up()
    {
        $groupMembershipComments = $this->table('group_membership_trads',  ['id' => false, 'primary_key' => ['group_membership_id', 'members_trad_id']]);
        $groupMembershipComments
            ->addColumn('group_membership_id', 'integer')
            ->addColumn('members_trad_id', 'integer')
            ->addIndex(['group_membership_id'])
            ->addIndex(['members_trad_id'], ['unique' => true])
            ->addForeignKey('group_membership_id', 'membersgroups', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('members_trad_id', 'memberstrads', 'id', ['delete' => 'CASCADE'])
            ->create();
        $this->execute('INSERT INTO group_membership_trads ( group_membership_id, members_trad_id) SELECT mg.id, mt.id FROM membersgroups mg, memberstrads mt where mg.Comment = mt.IdTrad and mt.IdRecord <> -1');
    }

    public function down()
    {
        $groupMembershipComments = $this->table('group_membership_trads');
        $groupMembershipComments->drop()
            ->save();
    }
}
