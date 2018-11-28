<?php


use Rox\Tools\RoxMigration;

class AddPreferenceForLandingForumFilter extends RoxMigration
{
    public function up()
    {
        $preferences = $this->table('preferences');
        $preferences
            ->insert([
                'position' => 100,
                'codeName' => 'PreferenceForumFilter',
                'codeDescription' => 'PreferenceForumFilter',
                'Description' => 'Stores the filter for forum posts applied on the landing page',
                'DefaultValue' => 'GroupsAndForums',
                'PossibleValues' => 'GroupsAndForums:Groups:Forums',
                'Status' => 'Inactive',
            ])
            ->save();
    }

    public function down()
    {
        // Do nothing
    }
}
