<?php

    /**
     *
     *
     * @author Fake51
     */


class GroupsFeaturedPage extends GroupsBasePage
{
    protected function teaserContent()
    {
        $words = $this->getWords();
        // &gt; or &raquo; ?
        ?>
        <div id="teaser" class="clearfix">
            <div id="teaser_l1"> 
                <h1><a href="groups">Groups</a> &raquo; <a href="groups/featured"><?= $words->get('GroupsFeaturedTeaser'); ?></a></h1>
            </div>
        </div>
        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'featured';
    }
}
