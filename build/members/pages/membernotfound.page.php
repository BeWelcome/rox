<?php


class MembersMembernotfoundPage extends PageWithActiveSkin
{
    protected function getPageTitle()
    {
        $words = new MOD_words;
        return $words->getSilent('MemberNotFound') . " - BeWelcome";
    }

    protected function teaserContent()
    {
        $words = new MOD_words;
        echo "<div id='teaser' class='row'><h1>{$words->get('MemberNotFound')}</h1></div>";
    }

    protected function leftSidebar()
    {

    }

    protected function getSubmenuItems()
    {

    }

    protected function column_col3()
    {
        $words = new MOD_words;
        echo "<p class='note'>{$words->get('MemberNotFoundDescription')}</p>";
    }
}
