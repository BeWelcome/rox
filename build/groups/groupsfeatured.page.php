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
        // &gt; or &raquo; ?
        ?>
        <div id="teaser" class="clearfix">
            <div id="teaser_l1"> 
                <h1><a href="groups">Groups</a> &raquo; <a href="groups/featured">Featured Groups</a></h1>
            </div>
        </div>
        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'featured';
    }

    public function column_col3()
    {
        echo <<<HTML
    <div>
        <h3>{$this->words->get('GroupsMyGroups')}</h3>
HTML;

        if ($this->_search_result)
        {
            foreach($this->_search_result as $group_data)
            {
                echo <<<HTML
            <div>
            <a href="groups/{$group_data->id}">{$group_data->Name}</a>
            </div>
HTML;
            }
        }
        else
        {
            echo <<<HTML
            <div>
            {$words->get('GroupSearchNoResults')}
            </div>
HTML;
        }

        echo "</div>";
    }

}
