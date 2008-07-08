<?php



//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */
class GroupsBasePage extends GroupsAppbasePage
{
    protected function getSubmenuItems()
    {
        return array(
            array('overview', 'groups', 'Overview'),
            array('new', 'groups/new', 'Create'),
        );
    }
    
}


?>