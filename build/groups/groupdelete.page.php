<?php

/**
 * This page asks if the user wants to join the group
 *
 */
class GroupDeletePage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        echo <<<HTML
        <h3>{$words->get('GroupsDeleteGroup')}</h3>
        <p>{$words->get('GroupsDeleteConsiderations')}</p>
        <span class="button"><a href="groups/{$this->group->id}/delete/true">{$words->get('GroupsReallyDelete')}</a></span>
        <span class="button"><a href="groups/{$this->group->id}">{$words->get('GroupsDontDelete')}</a></span>
HTML;
    }
    
    protected function getSubmenuActiveItem() {
        return 'admin';
    }
}

?>
