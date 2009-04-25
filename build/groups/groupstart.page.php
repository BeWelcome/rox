<?php

//------------------------------------------------------------------------------------
/**
 * This page shows an overview of the group
 *
 */
class GroupStartPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
		
		if (!$this->isGroupMember() && $this->group->Type == 'NeedInvitation')
		{
			echo "not public";
		}
		else
		{
	        $group_id = $this->group->id;
			$memberlist_widget = new GroupMemberlistWidget();
	        $memberlist_widget->setGroup($this->group);

	        $Forums = new ForumsController;
	        //$forums_widget->setGroup($this->getGroup());

			$wiki = new WikiController();
	        if ($this->isGroupMember()) {

	            $actionurl = 'group/'.$group_id;
	            $wiki->editProcess($actionurl);
	        }
			$wikipage = 'Group_'.str_replace(' ', '', ucwords($this->getGroupTitle()));
	        
	        include "templates/groupstart.php";
		}
    }
    
    protected function getSubmenuActiveItem() {
        return 'start';
    }
}


