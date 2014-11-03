<?php


class MembersMustloginPage extends RoxPageView
{
    protected function getPageTitle()
    {
        return "Please login to view these pages - BeWelcome";
    }
    
    protected function teaserHeadline()
    {
        echo 'Please login to view these pages';
    }
    
    protected function leftSidebar()
    {
        
    }

    protected function getSubmenuItems()
    {

    }

    protected function getColumnNames ()
    {
        return array('col3');
    }

    protected function column_col3()
    {
        $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
        $loginWidget->render();
    }
}
