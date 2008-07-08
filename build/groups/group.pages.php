<?php


  /* 
   * groups.pages.php is for code related to showing more groups
   * group.pages.php is for code related to showing one group */

//------------------------------------------------------------------------------------
/**
 * base class for all pages showing a group
 *
 */
class GroupBasePage extends GroupsAppbasePage
{
    private $_group = false;
    private $_members = false;
    
    public function setGroup($group) {
        $this->_group = $group;
    }
    
    protected function getGroupTitle() {
        return $this->getWords()->getBuffered(
            'Group_'.$this->_group->getData()->Name
        );
    }
    
    protected function getGroupDescription() {
        return $this->getWords()->getBuffered(
            'GroupDesc_'.$this->_group->getData()->Name
        );
    }
    
    protected function getGroupId() {
        return $this->_group->getData()->id;
    }
    
    protected function isGroupMember() {
        if (!isset($_SESSION['IdMember'])) {
            return false;
        } else {
            return $this->_group->isMember($_SESSION['IdMember']);
        }
    }
    
    protected function getGroup() {
        return $this->_group;
    }
    
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups">Groups</a> &raquo; <a href="groups/<?=$this->getGroup()->getData()->id ?>"><?=$this->getGroupTitle() ?></a></h1>
        </div>
        </div><?php
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'groups';
    }
    
    protected function getSubmenuItems()
    {
        $group_id = $this->getGroup()->getData()->id;
        $items = array();
        $items[] = array('start', 'groups/'.$group_id, $this->getGroup()->getData()->Name);
        $items[] = array('members', 'groups/'.$group_id.'/members', 'Members');
        if (!$this->isGroupMember()) {
            $items[] = array('join', 'groups/'.$group_id.'/join', 'Join');
        } else {
            $items[] = array('settings', 'groups/'.$group_id.'/settings', 'Member settings');
            $items[] = array('leave', 'groups/'.$group_id.'/leave', 'Leave');
        }
        return $items;
    }
    
}

//------------------------------------------------------------------------------------
/**
 * This page shows an overview of the group
 *
 */
class GroupStartPage extends GroupBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        
        $memberlist_widget = new GroupMemberlistWidget();
        $memberlist_widget->setGroup($this->getGroup());
        
        $forums_widget = new GroupForumWidget();
        $forums_widget->setGroup($this->getGroup());

        include "templates/groupstart.php";
    }
    
    protected function getSubmenuActiveItem() {
        return 'start';
    }
}



//------------------------------------------------------------------------------------
/**
 * This page asks if the user wants to leave the group
 *
 */
class GroupLeavePage extends GroupBasePage
{
    protected function column_col3()
    {
        ?><h3>Leave the group "<?=$this->getGroupTitle() ?>" ?</h3>
        Your choice.<br>
        <span class="button"><a href="groups/<?=$this->getGroupId() ?>/leave/yes">Leave</a></span>
        <span class="button"><a href="groups/<?=$this->getGroupId() ?>/leave/no">Cancel</a></span>
        <?php
    }
    
    protected function getSubmenuActiveItem() {
        return 'leave';
    }
}


?>