<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GroupsBasePage extends RoxPageView
{
    protected function leftSidebar()
    {
        ?><h3>Groups Overview sidebar</h3><?php
    }
    
    protected function getSubmenuItems()
    {
        if (isset($_SESSION['IdMember'])) {
            return array(
                         array('overview', 'groups', 'Overview'),
                         array('new', 'groups/new', 'Create'),
                         );
        }
        else {
            return array(
                         array('overview', 'groups', 'Overview'),
                         );
        }
    }
    
}

?>