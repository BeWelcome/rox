<?php

class GroupForumPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        
        if (!$this->isGroupMember() && $this->group->Type != 'Public')
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

            include "templates/groupforum.column_col3.php";
        }
    }
    protected function getSubmenuActiveItem() {
        return 'forum';
    }
    
}

?>
