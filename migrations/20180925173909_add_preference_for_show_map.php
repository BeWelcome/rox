<?php


use Rox\Tools\RoxMigration;

class AddPreferenceForShowMap extends RoxMigration
{
    public function up()
    {
        $preferences = $this->table('preferences');
        $preferences
            ->insert([
                'position' => 100,
                'codeName' => 'PreferenceShowMap',
                'codeDescription' => 'PreferenceShowMap',
                'Description' => 'Stores if the user wants to see the map on find members',
                'DefaultValue' => 'Yes',
                'PossibleValues' => 'Yes:No',
                'Status' => 'Normal',
            ])
            ->save();
    }

    public function down()
    {
        // Do nothing
    }
}
