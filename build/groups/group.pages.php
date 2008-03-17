<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages showing a group
 *
 */
class GroupBasePage extends GroupsAppBasePage
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
        
        ?><h3>Group Description</h3>
        <?=$this->getGroupDescription() ?><br>
        <?php
        /* ?><div><pre><?php print_r($this->getGroup()->getData()); ?></pre></div><?php */
        ?>
        <h3>Group Members</h3>
        <div><?php $memberlist_widget->render() ?></div>
        <h3>Group Forum</h3>
        <div><?php $forums_widget->render() ?></div><?php
    }
    
    protected function getSubmenuActiveItem() {
        return 'start';
    }
}


//------------------------------------------------------------------------------------
/**
 * This page asks if the user wants to join the group
 *
 */
class GroupJoinPage extends GroupBasePage
{
    protected function column_col3()
    {
        ?><h3>Join the group "<?=$this->getGroupTitle() ?>" ?</h3>
        Your choice.<br>
        <span class="button"><a href="groups/<?=$this->getGroupId() ?>/join/yes">Join</a></span>
        <span class="button"><a href="groups/<?=$this->getGroupId() ?>/join/no">Cancel</a></span>
        <?php
    }
    
    protected function getSubmenuActiveItem() {
        return 'join';
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


class GroupMembersPage extends GroupBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        ?><h3>Group Description</h3>
        <?=$this->getGroupDescription() ?><br>
        <?php
        /* ?><div><pre><?php print_r($this->getGroup()->getData()); ?></pre></div><?php */
        ?>
        <h3>Group Members</h3>
        <div><?php
        $members = $this->getGroup()->getMembers();
        foreach ($members as $member) {
            ?><div style="margin:2px; border:1px solid #eee; padding:2px;">
            <div style="float:left; padding: 4px">
            <?=MOD_layoutbits::linkWithPicture($member->Username) ?>
            </div>
            <div style="margin-left:80px">
            <strong><?=$member->Username ?></strong><br>
            I joined this group because...
            </div>
            <div style="clear:both; margin:2px"></div>
            </div>
            <?php
        }
        ?></div>
        <?php
    }
    
    protected function getSubmenuActiveItem() {
        return 'members';
    }
    
}


?>