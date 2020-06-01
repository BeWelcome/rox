<?php

class GroupsSubPage extends GroupsBasePage
{
    public function __construct($group)
    {
        parent::__construct();
        $this->group = $group;
        $this->crumbs['groups/' . $group->id] = htmlspecialchars($group->Name, ENT_QUOTES);
    }
}

