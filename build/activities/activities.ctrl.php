<?php
/**
 * Activities controller class.
 *
 * @author shevek
 */
class ActivitiesController extends RoxControllerBase
{
    const ACTIVITIES_PER_PAGE = 10;
    const ATTENDEES_PER_PAGE = 10;
    
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
            $this->redirectAbsolute($this->router->url('activities_near_me'));
        } else {
            $this->redirectAbsolute($this->router->url('activities_upcoming_activities'));
        }
    }
    
    public function joinLeaveActivityCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $errors = $this->_model->checkJoinLeaveActivityVarsOk($args);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        }
        $result = $this->_model->joinLeaveActivity($args->post);
        if ($result) {
            $activity = new Activity($args->post['activity-id']);
            $_SESSION['ActivityStatus'] = array('ActivityUpdateStatusSuccess', $activity->title);
            return true;
        } else {
            return false;
        }
    }

    public function cancelUncancelActivityCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $result = $this->_model->cancelUncancelActivity($args->post);
        if (!$result) {
            $errors = array( 'ActivityCancelUncancelError' );
            $mem_redirect->errors = $errors;
            return false;
        }
        $activity = new Activity($args->post['activity-id']);
        if (!$activity->status == 1){
            $_SESSION['ActivityStatus'] = array('ActivityUnCancelSuccess', $activity->title);
        } else {
            return $this->router->url('activities_show', array('id' => $activity->id), false);
        }
        return true;
    }

    protected function getPager($url, $count, $pageno, $itemsPerPage = self::ACTIVITIES_PER_PAGE) {
        $params = new StdClass;
        $params->strategy = new HalfPagePager('right');
        $params->page_url = 'activities/' . $url . '/';
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $params->items = $count;
        $params->active_page = $this->pageno;
        $params->items_per_page = $itemsPerPage;
        $pager = new PagerWidget($params);
        return $pager;
    }
    
    public function show() {
        if (!is_numeric($this->route_vars['id'])) {
            $this->redirectAbsolute($this->router->url('activities_upcoming_activities'));
        }
        $id = intval($this->route_vars['id']);
        $activity = new Activity($id);
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember && !$activity->public) {
            return new ActivitiesNotLoggedInPage();
        }
        $page = new ActivitiesShowPage();
        $page->activity = $activity;
        if ($loggedInMember) {
            $member = $loggedInMember;
            $member->status = 0;
            $member->comment = '';
            if ($loggedInMember && in_array($loggedInMember->id, array_keys($activity->attendees))) {
                $member->status = $activity->attendees[$loggedInMember->id]->status;
                $member->comment = $activity->attendees[$loggedInMember->id]->comment;
                $member->organizer = in_array($loggedInMember->id, array_keys($activity->organizers));
            }
            $page->member = $member;
        }
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $params = new StdClass;
        $params->strategy = new HalfPagePager('right');
        $params->page_url = 'activities/' . $activity->id . '/attendees/';
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $params->items = $activity->attendees;
        $params->active_page = $this->pageno;
        $params->items_per_page = self::ATTENDEES_PER_PAGE;
        $page->attendeesPager = new PagerWidget($params);
        return $page;
    }

    public function editCreateActivityCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $errors = $this->_model->checkEditCreateActivityVarsOk($args);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        } else {
            if ($args->post['activity-id'] == 0) {
                $activity = $this->_model->createActivity($args);
                $_SESSION['ActivityStatus'] = array('ActivityCreateSuccess', $args->post['activity-title']);
            } else {
                $activity = $this->_model->updateActivity($args);
                $_SESSION['ActivityStatus'] = array('ActivityUpdateSuccess', $args->post['activity-title']);
            }
            return $this->router->url('activities_show', array('id' => $activity->id), false);
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
                if (time() > strtotime($activity->dateTimeEnd)) {
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

    public function myActivities() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('activities_upcoming_activities'));
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
        
        $page->allActivities = $this->_model->getMyActivities(0, PVars::getObj('activities')->max_activities_on_map);
        
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
        $page->pager = $this->getPager('upcoming', $count, $pageno);
        
        $page->allActivities = $this->_model->getUpcomingActivities($page->publicOnly, 0, PVars::getObj('activities')->max_activities_on_map);
        
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
        $page->pager = $this->getPager('past', $count, $pageno);
        
        $page->allActivities = $this->_model->getPastActivities($page->publicOnly, 0, PVars::getObj('activities')->max_activities_on_map);
                
        return $page;
    }

    public function setRadiusCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $this->_model->setRadius($args);
        return $this->router->url('activities_near_me', array(), false);
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
        $page->radius = $this->_model->getRadius();
        $distance = 2 * $page->radius;
        $count = $this->_model->getActivitiesNearMeCount($distance);
        $page->activities = $this->_model->getActivitiesNearMe($distance, $pageno, self::ACTIVITIES_PER_PAGE);
        $page->pager = $this->getPager('nearme', $count, $pageno);
        
        $page->allActivities = $this->_model->getActivitiesNearMe($distance, 0, PVars::getObj('activities')->max_activities_on_map);
        
        return $page;
    }

    public function searchActivitiesCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $errors = $this->_model->checkSearchActivitiesVarsOk($args);
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
            
            $page->allActivities = $this->_model->searchActivities($page->publicOnly, $page->keyword, 0, PVars::getObj('activities')->max_activities_on_map);
        } else {
            $page->keyword = '';
        }
        return $page;
    }
}
