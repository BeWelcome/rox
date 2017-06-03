<?php

/**
 * This page allows to create a new group
 *
 */
class GroupsCreationPage2 extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?>
        <div>
        <h1><a href="groups">Groups</a> &raquo; <a href="groups/new">New</a></h1>
        </div>
        <?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'new';
    }
}

?>
