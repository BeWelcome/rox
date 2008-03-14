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

class GroupsOverviewPage extends GroupsBasePage
{
    
    protected function teaserContent()
    {
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups">Groups</a></h1>
        </div>
        </div><?php
    }
    
    protected function column_col3()
    {
        ?><div>
        <h3>Search Groups</h3>
        <form>
        <input><input type="submit" value="Find"><br>
        </form>
        <h3>Create new groups</h3>
        <div><span class="button"><a href="groups/new">New group</a></span></div>
        <h3>My Groups</h3>
        <?php
        if (!isset($_SESSION['IdMember'])) {
            // nothing
        } else foreach($this->getModel()->getGroupsForMember($_SESSION['IdMember']) as $group_data) {
            ?><div>
            <a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a>
            </div><?php
        }
        ?>
        </div>
        <div style="float:right"><span class="button"><a href="groups/new">New group</a></span></div>
        <h3>Group List</h3>
        <?php
        foreach($this->getModel()->getGroups() as $group_data) {
            ?><div>
            <a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a>
            </div><?php
        }
        ?>
        </div><?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }
}

class GroupsCreationPage extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups">Groups</a> &raquo; <a href="groups/new">New</a></h1>
        </div>
        </div><?php
    }
    
    protected function column_col3()
    {
        ?>
        <h3>Create a new Group</h3>
        <form>
        Name:<br>
        <input/><br><br>
        Description:<br>
        <textarea cols="50" rows="5""></textarea><br><br>
        Tools:<br>
        <input type="checkbox" checked> Group forum<br>
        <input type="checkbox"> Group blog<br>
        <br>
        <input type="submit" value="Create"><br>
        </form> 
        <?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'new';
    }
}


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

class GroupStartPage extends GroupBasePage
{
    protected function column_col3()
    {
        ?><h3>Group Description</h3>
        <div><pre><?php
        print_r($this->getGroup()->getData());
        ?></pre></div>
        <h3>Group Members</h3>
        <div><?php
        $memberlist_widget = new GroupMemberlistWidget();
        $memberlist_widget->setGroup($this->getGroup());
        $memberlist_widget->render();
        ?></div>
        <h3>Group Forum</h3>
        <div><pre><?php
        $forums_widget = new GroupForumWidget();
        $forums_widget->setGroup($this->getGroup());
        $forums_widget->render();
        ?></pre></div><?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'start';
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