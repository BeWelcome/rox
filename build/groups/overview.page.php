<?php


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
        <?php
            if (APP_user::isBWLoggedIn()) {
                ?>
                <h3>Create new groups</h3>
                <div><span class="button"><a href="groups/new">New group</a></span></div>
                <?php
            }
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
?>