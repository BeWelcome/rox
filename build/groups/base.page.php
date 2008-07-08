<?php


class GroupsBasePage extends RoxPageView
{
    protected function leftSidebar()
    {
        ?><h3>Groups Overview sidebar</h3><?php
    }
    
    protected function getSubmenuItems()
    {
        return array(
            array('overview', 'groups', 'Overview'),
            array('new', 'groups/new', 'Create'),
        );
    }
    
}

?>