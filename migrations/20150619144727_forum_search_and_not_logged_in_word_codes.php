<?php

use Phinx\Migration\AbstractMigration;

class ForumSearchAndNotLoggedInWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->AddWordCode('ForumSearchNotLoggedIn', 'Forum search is only accessible when you\'re logged in', 'Shown when someone uses a search link for the forum but isn\'t logged in');
        $this->AddWordCode('ForumSearchNoResults', 'No forum posts match the search word please.', 'Shown when the keyword(s) don\'t match any forum thread or post');
        $this->AddWordCode('ForumSearchNoSphinx', 'Unfortunately there is a technical problem (Sphinx engine not running). Please inform the support team.', 'In case the search engine stop working this is shown to the users.');
        $this->AddWordCode('ForumNotLoggedIn', 'The forums are only accessible for members of BeWelcome. Please login to see them', 'Information shown when someone tries to access the forum while not logged in');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('ForumSearchNotLoggedIn');
        $this->RemoveWordCode('ForumSearchNoResults');
        $this->RemoveWordCode('ForumSearchNoSphinx');
        $this->RemoveWordCode('ForumNotLoggedIn');
    }
}