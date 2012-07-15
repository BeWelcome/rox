<?php


class VolunteerPageView extends RoxPageView
{
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }

    protected function teaserContent() {
        require 'templates/teaser_volunteer.php';
    }
    
    protected function getPageTitle() {
        return 'Volunteer Pages - BeWelcome *';
    }
    
    protected function leftSidebar()
    {
        require 'templates/volunteertoolsbar.php';
    }
    
    protected function getSubmenuItems()
    {
        $items = array();
        $items[] = array('dashboard', 'volunteer/dashboard', 'VolunteerDashboard');
        $items[] = array('tools', 'volunteer/tools', 'VolunteerTools');
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
        require 'templates/volunteertoolspage.php';
    }
}

class VolunteerTaskView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'tasks';
    }
    
    protected function column_col3() {
        $currentSubPage = 'tasks';
        require 'templates/volunteertoolspage.php';
    }
}


class VolunteerFeaturesView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'features';
    }
    
    protected function column_col3() {
        $currentSubPage = 'features';
        require 'templates/volunteertoolspage.php';
    }
}


class VolunteerDashboardView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'dashboard';
    }
    
    protected function column_col3() 
    {
        require_once 'simplepie/autoloader.php';
        $isvolunteer = true;
        require 'templates/volunteer.php';
    }
}


?>
