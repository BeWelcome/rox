<?php

class MemberGroupsPage extends ProfilePage
{
    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'groups';
    }
}
