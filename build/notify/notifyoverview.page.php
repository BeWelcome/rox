<?php

/**
 * This page allows to create a new group
 *
 */
class GroupsCreationPage3 extends GroupsBasePage
{
    #[\Override]
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?>
        <div>
            <h1><a href="groups">Groups</a> &raquo; <a href="new/group">New</a></h1>
        </div>
        <?php
    }

    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'new';
    }
}

?>
