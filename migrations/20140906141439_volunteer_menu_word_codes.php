<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class NewMembersBeWelcome
 *
 * Adds a column to members to show the number of messages a member got from the New Members Be Welcome team
 *
 * See ticket: #2243
 *
 */
class VolunteerMenuWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('AdminFAQ', 'FAQ', 'Menu item in the volunteer menu');
        $this->AddWordCode('AdminFAQInfo', 'Manage FAQs', 'Alt text of the item in the volunteer menu');
        $this->AddWordCode('AdminSQLForVolunteers', 'Useful queries', 'Menu item in the volunteer menu');
        $this->AddWordCode('AdminSQLForVolunteersInfo', 'Execute database queries', 'Alt text of the item in the volunteer menu');
        $this->AddWordCode('AdminWordInfo', '', 'Alt text of the item in the volunteer menu');
        $this->AddWordCode('AdminMassMailInfo', '', 'Alt text of the item in the volunteer menu');
        $this->AddWordCode('AdminLogsInfo', '', 'Alt text of the item in the volunteer menu');
        $this->AddWordCode('AdminRightsInfo', '', 'Alt text of the item in the volunteer menu');
        $this->AddWordCode('AdminFlagsInfo', '', 'Alt text of the item in the volunteer menu');
        $this->AddWordCode('AdminNewMembersBeWelcomeInfo', '', 'Alt text of the item in the volunteer menu');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('AdminFAQ');
        $this->RemoveWordCode('AdminFAQInfo');
        $this->RemoveWordCode('AdminSQLForVolunteers');
        $this->RemoveWordCode('AdminSQLForVolunteersInfo');
        $this->RemoveWordCode('AdminWordInfo');
        $this->RemoveWordCode('AdminMassMailInfo');
        $this->RemoveWordCode('AdminLogsInfo');
        $this->RemoveWordCode('AdminRightsInfo');
        $this->RemoveWordCode('AdminFlagsInfo');
        $this->RemoveWordCode('AdminNewMembersBeWelcomeInfo');
    }
}