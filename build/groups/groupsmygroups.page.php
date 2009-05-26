<?php

    /**
     *
     *
     * @author Fake51
     */


class GroupsMyGroupsPage extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div id="teaser" class="clearfix">
            <div id="teaser_l1"> 
                <h1><a href="groups"><?= $words->get('Groups');?></a> &raquo; <a href="groups/mygroups"><?= $words->get('MyGroups');?></a></h1>
            </div>
        </div>
        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'mygroups';
    }


}
