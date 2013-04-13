<?php
/**
 * Events controller class.
 *
 * @author shevek
 */
class ActivitiesController extends RoxControllerBase
{
    const ACTIVITIES_PER_PAGE = 20;
    const ATTENDEES_PER_PAGE = 18;
    
    /**
     * Declaring private variables.
     */
    private $_model;
    private $_view;
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new ActivitiesModel();
        $this->_view  = new ActivitiesView($this->_model);
    }

    /**
     * Redirects to my activities if a member is logged in otherwise shows upcoming activities
     */
    public function activities() {
        if ($this->_model->getLoggedInMember()) {
        error_log('myact');
            $this->redirectAbsolute($this->router->url('activities_my_activities'));
        } else {
        error_log('upact');
            $this->redirectAbsolute($this->router->url('activities_upcoming_activities'));
        }
    }
    
    public function joinLeaveCancelActivityCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $result = $this->_model->joinLeaveCancelActivity($args->post);
        if ($result) {
            $_SESSION['ActivityStatus'] = array('ActivityUpdateStatusSuccess', $args->post['activity-title']);
            return $this->router->url('activities_my_activities', array(), false);
        } else {
            return false;
        }
    }
    
    public function show() {
        $id = intval($this->route_vars['id']);
        $activity = new Activity($id);
        
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember && !isset($activity->public)) {
            return new ActivitiesNotLoggedInPage();
        }
        $page = new ActivitiesShowPage();
        $page->activity = $activity;
        $page->loggedInMember = $this->_model->getLoggedInMember();
        $params = new StdClass;
        $params->strategy = new HalfPagePager('right');
        $params->page_url = 'activities/show/' . $id . '/attendees/';
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $params->items = count($activity->attendees);
        $params->items_per_page = self::ATTENDEES_PER_PAGE;
        $pager = new PagerWidget($params);
        $member = new StdClass;
        $member->status = 0;
        $member->comment = '';
        if ($loggedInMember && in_array($loggedInMember->id, array_keys($activity->attendees))) {
            $member->status = $activity->attendees[$loggedInMember->id]->status;
            $member->comment = $activity->attendees[$loggedInMember->id]->comment;
            $member->organizer = in_array($loggedInMember->id, array_keys($activity->organizers));
        }
        $page->member = $member;
        $page->attendeesPager = $pager;
        return $page;
    }

    public function editCreateActivityCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $errors = $this->_model->checkEditCreateActivityVarsOk($args);
        if (count($errors) > 0) {
            error_log("error");
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        } else {
            if ($args->post['activity-id'] == 0) {
                $this->_model->createActivity($args);
                $_SESSION['ActivityStatus'] = array('ActivityCreateSuccess', $args->post['activity-title']);
            } else {
                $this->_model->updateActivity($args);
                $_SESSION['ActivityStatus'] = array('ActivityUpdateSuccess', $args->post['activity-title']);
            }
            return $this->router->url('activities_my_activities', array(), false);
        }
    }
    
    public function editcreate() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if ($loggedInMember) {
            $id = 0;
            if (isset($this->route_vars['id'])) {
                $id = $this->route_vars['id'];
                $activity = new Activity($id);
                if (!in_array($loggedInMember->id, array_keys($activity->organizers))) {
                    $this->redirectAbsolute($this->router->url('activities_my_activities'));
                }
            } else {
                $activity = new Activity();
                $activity->id = 0;
                $activity->locationId = $loggedInMember->IdCity;
                $entityFactory = new RoxEntityFactory();
                $activity->location = $entityFactory->create('Geo', $activity->locationId);
            }
            $page = new ActivitiesEditCreatePage();
            $page->member = $loggedInMember;
            $page->activity = $activity;
            return $page;
        } else {
            return new ActivitiesNotLoggedInPage();
        }
    }

    protected function getPager($url, $count, $pageno) {
        $params = new StdClass;
        $params->strategy = new HalfPagePager('right');
        $params->page_url = 'activities/' . $url . '/';
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $params->items = $count;
        $params->active_page = $this->pageno;
        $params->items_per_page = self::ACTIVITIES_PER_PAGE;
        $pager = new PagerWidget($params);
        return $pager;
    }
    
    public function myActivities() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('activities_upcoming'));
        }
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new ActivitiesMyActivitiesPage();
        $page->member = $loggedInMember;
        $count = $this->_model->getMyActivitiesCount();
        $activities = $this->_model->getMyActivities($pageno, self::ACTIVITIES_PER_PAGE);
        $page->activities = $activities;
        $page->pager = $this->getPager('myactivities', $count, $pageno);
        return $page;
    }

    public function upcomingActivities() {
        $page = new ActivitiesUpcomingActivitiesPage();
        $loggedInMember = $this->_model->getLoggedInMember();
        if ($loggedInMember) {
            $page->publicOnly = false;
        } else {
            $page->publicOnly = true;
        }
        $page->member = $loggedInMember;
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $count = $this->_model->getUpcomingActivitiesCount($page->publicOnly);
        $page->activities = $this->_model->getUpcomingActivities($page->publicOnly, $pageno, self::ACTIVITIES_PER_PAGE);
        $page->pager = $this->getPager('upcomingactivities', $count, $pageno);
        return $page;
    }

    public function pastActivities() {
        $page = new ActivitiesPastActivitiesPage();
        $loggedInMember = $this->_model->getLoggedInMember();
        if ($loggedInMember) {
            $page->publicOnly = false;
        } else {
            $page->publicOnly = true;
        }
        $page->member = $loggedInMember;
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $count = $this->_model->getPastActivitiesCount($page->publicOnly);
        $page->activities = $this->_model->getPastActivities($page->publicOnly, $pageno, self::ACTIVITIES_PER_PAGE);
        $page->pager = $this->getPager('pastactivities', $count, $pageno);
        return $page;
    }

    public function activitiesNearMe() {
        $page = new ActivitiesActivitiesNearMePage();
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('activities_upcoming'));
        }
        $page->member = $loggedInMember;
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $distance = 50;
        $count = $this->_model->getActivitiesNearMeCount($distance);
        error_log($count);
        $page->activities = $this->_model->getActivitiesNearMe($distance, $pageno, self::ACTIVITIES_PER_PAGE);
        $page->pager = $this->getPager('nearme', $count, $pageno);
        return $page;
    }

    public function searchActivitiesCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $errors = $this->_model->checkSearchActivitiesVarsOk($args);
        error_log(print_r($errors, true));
        if (count($errors) > 0) {
            $_SESSION['errors'] = $errors;
            return $this->router->url('activities_search', array(), false);
        } else {
            return $this->router->url('activities_search_results', array( "keyword" => $args->post['activity-keyword']), false);
        }
    }

    public function search() {
        $page = new ActivitiesSearchResultPage();
        $loggedInMember = $this->_model->getLoggedInMember();
        if ($loggedInMember) {
            $page->publicOnly = false;
        } else {
            $page->publicOnly = true;
        }
        $page->member = $loggedInMember;
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        if (isset($this->route_vars['keyword'])) {
            $page->keyword = $this->route_vars['keyword'];
            $count = $this->_model->searchActivitiesCount($page->publicOnly, $page->keyword);
            $activities = $this->_model->searchActivities($page->publicOnly, $page->keyword, $pageno, self::ACTIVITIES_PER_PAGE);
            $page->activities = $activities;
            $page->pager = $this->getPager('search/' . urlencode($page->keyword), $count, $pageno);
        } else {
            $page->keyword = '';
        }
        return $page;
    }
}
