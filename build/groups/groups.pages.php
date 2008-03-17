<?php


class GroupsAppBasePage extends RoxPageView
{
    protected function leftSidebar()
    {
        ?><h3>Last visited groups</h3>
        <ul>
        <?php
        $last_visited = $this->getModel()->getLastVisited();
        foreach ($last_visited as $group) {
            if ($group) {
                ?><li><a href="groups/<?=$group->getData()->id ?>"><?=$group->getData()->Name ?></a></li>
                <?php
            }
        }
        ?></ul><?php
        ?><h3>My groups</h3>
        <ul>
        <?php
        $my_groups = $this->getModel()->getMyGroups();
        foreach ($my_groups as $group_data) {
            if ($group_data) {
                ?><li><a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a></li>
                <?php
            }
        }
        ?></ul><?php
    }
}


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */
class GroupsBasePage extends GroupsAppBasePage
{
    protected function getSubmenuItems()
    {
        return array(
            array('overview', 'groups', 'Overview'),
            array('new', 'groups/new', 'Create'),
        );
    }
    
}


//------------------------------------------------------------------------------------
/**
 * This page shows an overview of the groups in bw,
 * with search, my groups, etc
 *
 */
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
        <?php
        $my_groups = $this->getModel()->getMyGroups();
        if (!empty($my_groups)) {
            ?><h3>My Groups</h3><?php
            foreach($this->getModel()->getMyGroups() as $group_data) {
                ?><div>
                <a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a>
                </div><?php
            }
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


//------------------------------------------------------------------------------------
/**
 * This page allows to create a new group
 *
 */
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
        <?php /* ?>
        <h3>Group options</h3>
        Tools:<br>
        <input type="checkbox" checked> Group forum<br>
        <input type="checkbox"> Group blog<br>
        <br>
        <?php */ ?>
        <h3>Who can join</h3>
        <input type="radio" checked> Any BeWelcome member<br>
        <input type="radio"> Any BeWelcome member, approved by moderators<br>
        <input type="radio"> Only invited BeWelcome members<br>
        <input type="radio"> Noone can join (it's not really a group)<br>
        <br>
        <h3>Create it now!</h3>
        <input type="submit" value="Create"><br>
        </form> 
        <?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'new';
    }
}





?>