<?php

class GroupBasePage extends RoxPageView
{
    private $_group = false;
    private $_members = false;
    
    public function setGroup($group) {
        $this->_group = $group;
    }
    
    protected function getGroup() {
        return $this->_group;
    }
    
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups">Groups</a> &raquo; <a href="groups/<?=$this->getGroup()->getData()->id ?>"><?=$this->getGroup()->getData()->Name ?></a></h1>
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
        return array(
            array('start', 'groups/'.$group_id, 'Start'),
            array('members', 'groups/'.$group_id.'/members', 'Members'),
        );
    }
    
    protected function leftSidebar()
    {
        ?><h3>Group sidebar</h3><?php
    }
}


class GroupForumWidget  // extends ForumBoardWidget
{
    public function render()
    {
        echo 'group forum';
    }
    
    public function setGroup($group)
    {
        // extract information from the $group object
    }
}

class GroupMemberlistWidget  // extends MemberlistWidget?
{
    private $_group;
    
    public function render()
    {
        $memberships = $this->_group->getMemberships(10);
        foreach ($memberships as $membership) {
            ?><div style="float:left; border:1px solid #fec;">
                <?=MOD_layoutbits::linkWithPicture($membership->Username) ?><br>
                <?=$membership->Username ?>
            </div><?php
        }
        ?><div style="clear:both;"></div><?php
    }
    
    public function setGroup($group)
    {
        // extract memberlist information from the $group object
        $this->_group = $group;
    }
}


?>