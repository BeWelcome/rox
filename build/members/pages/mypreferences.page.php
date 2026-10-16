<?php


class MyPreferencesPage extends ProfilePage
{
    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'mypreferences';
    }

}
