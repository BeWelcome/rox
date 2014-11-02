<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class NewMembersBeWelcome
 *
 * Adds a column to members to show the number of messages a member got from the New Members Be Welcome team
 *
 * See ticket: #2214
 *
 */
class StatusWordCodes extends Rox\Tools\RoxMigration
{
    private $_statuses = array (
        "Active" => "Active",
        "OutOfRemind" => "Out of remind",
        "PassedAway" => "Passed away",
        "SuspendedBeta" => "Suspended",
        "MailToConfirm" => "Not confirmed yet",
        "Banned" => "Banned",
        "Pending" => "Pending (obsolete)",
        'DuplicateSigned' => "Duplicate",
        'NeedMore' => "Need more (obsolete)",
        'ChoiceInactive' => "Inactive (own choice)",
        'Rejected' => "Rejected (obsolete)",
        'CompletedPending' => "Complete (obsolete)",
        'TakenOut' => "Taken out (obsolete)",
        'Sleeper' => "Sleeper (obsolete)",
        'Renamed' => "Renamed (not used)",
        'ActiveHidden' => "Admin profile",
        'AskToLeave' => "Retired",
        'StopBoringMe' => "Bored (obsolete)",
        'Buggy' => "Buggy (obsolete)"
    );

    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach($this->_statuses as $status => $sentence) {
            $this->AddWordCode("MemberStatus" . $status, $sentence, "One of the possible profile statuses");
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach($this->_statuses as $status => $sentence) {
            $this->RemoveWordCode("MemberStatus" . $status);
        }
    }
}