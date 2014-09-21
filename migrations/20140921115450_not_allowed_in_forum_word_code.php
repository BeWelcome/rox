<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class NotAllowedInForumWordCode
 *
 * Add new word code to be able to show a translatable message to a member that has been banned by the forum mods.
 *
 * see ticket #2206
 */
class NotAllowedInForumWordCode extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('NotAllowedToPostInForum', "Sorry, but you're not allowed to post in the BW forums due to a ban issued by the forum moderators.", 'Message shown to a banned member.');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('NotAllowedToPostInForum');
    }
}