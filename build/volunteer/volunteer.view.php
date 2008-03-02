<?php


class VolunteerPageView extends RoxPageView
{
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }

    protected function teaserContent() {
        $this->showTemplate('apps/rox/teaser_volunteer.php');
    }
    
    protected function getPageTitle() {
        return 'Volunteer Pages - BeWelcome *';
    }
    
    protected function leftSidebar()
    {
        $this->showTemplate('apps/rox/volunteertoolsbar.php');
    }
    
    protected function getSubmenuItems()
    {
        $items = array();
        $items[] = array('dashboard', 'volunteer/dashboard', 'VolunteerDashboard');
        $items[] = array('tools', 'volunteer/tools', 'VolunteerTools');
        $items[] = array('search', 'volunteer/search', 'VolunteerSearch');
        $items[] = array('tasks', 'volunteer/tasks', 'VolunteerTasks');
        $items[] = array('features', 'volunteer/features', 'VolunteerFeatures');
        return $items;
    }
    
    protected function getColumnNames() {
        return array('col1', 'col3');
    }
}



class VolunteerToolsView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'tools';
    }
    
    private $_toolname;
    public function __construct($toolname) {
        $this->_toolname = $toolname;
    }
    protected function column_col3() {
        $currentSubPage = $this->_toolname;
        require TEMPLATE_DIR.'apps/rox/volunteertoolspage.php';
    }
}

class VolunteerSearchView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'search';
    }
    
    protected function column_col3() { 
        require TEMPLATE_DIR.'apps/rox/volunteersearchpage.php';
    }
}


class VolunteerTaskView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'tasks';
    }
    
    protected function column_col3() {
        $currentSubPage = 'tasks';
        require TEMPLATE_DIR.'apps/rox/volunteertoolspage.php';
    }
}


class VolunteerFeaturesView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'features';
    }
    
    protected function column_col3() {
        $currentSubPage = 'features';
        require TEMPLATE_DIR.'apps/rox/volunteertoolspage.php';
    }
}


class VolunteerDashboardView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'dashboard';
    }
    
    protected function column_col3() {
        define('MAGPIE_CACHE_ON',false);
        require_once ("magpierss/rss_fetch.inc");
        $isvolunteer = true;
        require TEMPLATE_DIR.'apps/rox/volunteer.php';
    }
}


?>