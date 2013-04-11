<?php
/**
 * Events controller class.
 *
 * @author shevek
 */
class ActivitiesController extends RoxControllerBase
{
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

    public function find() {
        return new ActivitiesFindPage();
    }
    
    public function joinLeaveCancelActivityCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        $this->_model->joinLeaveCancelActivity($args->post);
        /* todo: redirect nicely */
        return true;
    }
    
    public function show() {
        $id = intval($this->route_vars['id']);
        $page = new ActivitiesShowPage();
        $activity = new Activity($id);
        $page->activity = $activity;
        $page->loggedInMember = $this->_model->getLoggedInMember();
        $params = new StdClass;
        $params->strategy = new HalfPagePager('right');
        $params->page_url = 'activities/show/' . $id . '/attendees/';
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $params->items = count($activity->attendees);
        $params->items_per_page = 18;
        $pager = new PagerWidget($params);
        $member = new StdClass;
        $member->status = 0;
        $member->comment = '';
        $loggedInMember = $this->_model->getLoggedInMember();
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
            } else {
                $activity = new Activity();
                $activity->id = 0;
                $activity->locationId = $loggedInMember->IdCity;
                $entityFactory = new RoxEntityFactory();
                $activity->location = $entityFactory->create('Geo', $activity->locationId);
            }
            $page = new ActivitiesEditCreatePage();
            $page->activity = $activity;
            return $page;
        } else {
            return new ActivitiesNotLoggedInPage();
        }
    }

    public function myActivities() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('activities_upcoming'));
        }
        $page = new ActivitiesMyActivitiesPage();
        $page->member = $loggedInMember;
        $activities = $this->_model->getMyActivities();
        $page->activities = $activities;
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
        $page->activities = $this->_model->getActivities($page->publicOnly);
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
        $page->activities = $this->_model->getPastActivities($page->publicOnly);
        return $page;
    }

    public function searchActivitiesCallback(StdClass $args, ReadOnlyObject $action, 
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) 
    {
        error_log("search");
        $errors = $this->_model->checkSearchActivitiesVarsOk($args);
        error_log(print_r($errors, true));
        if (count($errors) > 0) {
            error_log("error");
            $_SESSION['errors'] = $errors;
        } else {
            error_log("no error");
                $loggedInMember = $this->_model->getLoggedInMember();
            if ($loggedInMember) {
                $publicOnly = false;
            } else {
                $publicOnly = true;
            }
            $activities = $this->_model->getPastActivities($publicOnly);
            $_SESSION['activities'] = $activities;
            $_SESSION['vars'] = $args->post;
        }
        return $this->router->url('activities_search_results', array(), false);
    }

    /**
     * normally search will be reached by a redirect and the information from the originating
     * page will be stored in a $_SESSION variable.
     * 
     * if none of the expected variables is set we just an empty page with a search field.  
     */
    public function search() {
        error_log("hallo search");
        $page = new ActivitiesSearchResultPage();
        $loggedInMember = $this->_model->getLoggedInMember();
        if ($loggedInMember) {
            $page->publicOnly = false;
        } else {
            $page->publicOnly = true;
        }
        $page->member = $loggedInMember;
        return $page;
    }
}
