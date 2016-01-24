<?php

use Phinx\Migration\AbstractMigration;

class GroupsForumsNotLoggedInWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->AddWordCode('GroupsFullFunctionalityLoggedIn', 'The groups are only fully functional after you login.', 'Hint shown to visitors to the site that aren\'t logged in.');
        $this->AddWordCode('GroupsNoPublicPosts', 'There are no posts visible to non members in this group.', 'Info text shown to a non member of the group if there are no visible posts.');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('GroupsFullFunctionalityLoggedIn');
        $this->RemoveWordCode('GroupsNoPublicPosts');
    }
}