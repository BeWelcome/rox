<?php


use Rox\Tools\RoxMigration;

class AddPreferenceForLandingMessageFilter extends RoxMigration
{
    public function up()
    {
        $preferences = $this->table('preferences');
        $preferences
            ->insert([
                'position' => 100,
                'codeName' => 'PreferenceMessageFilter',
                'codeDescription' => 'PreferenceMessageFilter',
                'Description' => 'Stores the filter for messages and requests applied on the landing page',
                'DefaultValue' => 'Unread',
                'PossibleValues' => 'All:Unread',
                'Status' => 'Inactive',
            ])
            ->save();
    }

    public function down()
    {
        // Do nothing
    }
}
