<?php


use Rox\Tools\RoxMigration;

class SetPreferencesToInactive extends RoxMigration
{
    public function up()
    {
        // disable DaylightSaving
        $this->setStatusForPreference('PreferenceDayLight', false);
        $this->setStatusForPreference('PreferenceAdvanced', false);
    }

    public function down()
    {
        // enable DaylightSaving
        $this->setStatusForPreference('PreferenceDayLight', true);
        $this->setStatusForPreference('PreferenceAdvanced', true);
    }

    private function setStatusForPreference(string $codename, bool $enable)
    {
        if ($enable) {
            $this->execute("UPDATE preferences SET `Status` = 'Normal' WHERE codename = '" . $codename . "'");
        } else {
            $this->execute("UPDATE preferences SET `Status` = 'Inactive' WHERE codename = '" . $codename . "'");
        }
    }
}
