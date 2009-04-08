<?php


class MembersMembernotfoundPage extends MemberPage
{
    protected function getPageTitle()
    {
        return "Member not Found - BeWelcome";
    }
    
    protected function teaserContent()
    {
        echo '<div id="teaser"><h1>Member not found</h1></div>';
    }
    
    protected function leftSidebar()
    {
        
    }

    protected function getSubmenuItems()
    {

    }
    
    protected function column_col3()
    {
        echo "
        Did not find this member. We are not amused.";
    }
}


?>
