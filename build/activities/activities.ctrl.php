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
    
    public function overview() {
        $page = new ActivitiesOverviewPage();
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
            print_r($member);
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
            return false;
        } else {
            if ($args->post['activity-id'] == 0) {
                $this->_model->createActivity($args);
            } else {
                $this->_model->updateActivity($args);
            }
            /* todo: redirect nicely */
            return true;
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
}
