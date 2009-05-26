<?php

class GroupWikiPage extends GroupsBasePage
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

            $wiki = new WikiController();

            if ($this->isGroupMember()) {
                $wiki->editProcess();
            }
            $wikipage = 'Group_'.str_replace(' ', '', ucwords($this->group->Name));
            
            include "templates/groupwiki.column_col3.php";
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'wiki';
    }
    
}

?>
