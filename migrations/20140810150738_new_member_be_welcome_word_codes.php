<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class NewMembersBeWelcome
 *
 * Adds a column to members to show the number of messages a member got from the New Members Be Welcome team
 *
 * See ticket: #2240
 *
 */
class NewMemberBeWelcomeWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('AdminNewMembers', 'New Members BeWelcome', 'Teaser item in admin/newmembers');
        $this->AddWordCode('AdminNewMembersMemberDetails', 'Member details', 'Header text on admin/newmembers');
        $this->AddWordCode('AdminNewMembersGlobalGreetingUsername', 'Send an international greeting to %1$s', 'Alt text for the local greeting icon in admin/newmembers');
        $this->AddWordCode('AdminNewMembersGlobalGreeting', 'Global greeting', 'Text shown in admin/newmembers');
        $this->AddWordCode('AdminNewMembersLocalGreetingUsername', 'Send an international greeting to %1$s', 'Alt text for the global greeting in admin/newmembers');
        $this->AddWordCode('AdminNewMembersLocalGreeting', 'Local greeting', 'Text shown in admin/newmembers');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Remove word codes
        $this->RemoveWordCode('AdminNewMembers');
        $this->RemoveWordCode('AdminNewMembersMemberDetails');
        $this->RemoveWordCode('AdminNewMembersGlobalGreetingUsername');
        $this->RemoveWordCode('AdminNewMembersGlobalGreeting');
        $this->RemoveWordCode('AdminNewMembersLocalGreetingUsername');
        $this->RemoveWordCode('AdminNewMembersLocalGreeting');
    }
}