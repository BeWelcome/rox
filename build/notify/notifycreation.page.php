<?php

/**
 * This page allows to create a new group
 *
 */
class GroupsCreationPage extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?>
        <div id="teaser" class="page-teaser clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups">Groups</a> &raquo; <a href="groups/new">New</a></h1>
        </div>
        </div>
        <?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'new';
    }
}

?>
