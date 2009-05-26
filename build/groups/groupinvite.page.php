<?php

/**
 * This page allows to create a new group
 *
 */
class GroupInvitePage extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups"><?= $words->get('Groups');?></a> &raquo; <a href=""><?= $words->get('GroupsInviteMembers');?></a></h1>
        </div>
        </div>
        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'members';
    }

    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();

        echo <<<HTML
    <div id="groups">
        <h3>{$words->get('GroupsInviteMembers')}</h3>
HTML;
        if ($search = $this->search_result)
        {
            echo <<<HTML
            <ul>
HTML;
            foreach ($search as $member)
            {
                echo <<<HTML
                <li><a href='groups/{$this->group->getPKValue()}/invitemember/{$member->getPKValue()}' title='{$words->get('GroupsClickToSendInvite')}'>{$words->get('GroupsInviteMember',$member->Username)}</li>
HTML;
            }
        }
        else
        {
            echo $words->get('GroupSearchNoResults');
        }
    echo "  </div>";
    }
}


