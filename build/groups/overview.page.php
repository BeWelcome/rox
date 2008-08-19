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
    
    
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }
}
?>