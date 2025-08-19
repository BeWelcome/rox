<?php


class MembersMustloginPage extends RoxPageView
{
    #[\Override]
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

    #[\Override]
    protected function getSubmenuItems()
    {

    }

    #[\Override]
    protected function getColumnNames ()
    {
        return ['col3'];
    }

    protected function column_col3()
    {
        $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
        $loginWidget->render();
    }
}
