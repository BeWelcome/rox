<?php
/**
 * trip controller
 *
 * @package trip
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: trip.ctrl.php 233 2007-02-28 13:37:19Z marco $
 */
class TripsController extends RoxControllerBase {

    const TRIPS_PER_PAGE = 10;

    private $_model;
    private $_view;

    public function __construct() {
        parent::__construct();
        $this->_model = new TripsModel();
    }

    public function __destruct() {
        unset($this->_model);
    }

    private function _notLoggedIn() {
        return new TripsNotLoggedInPage();
    }

    /**
     * Redirects to my trips if a member is logged in otherwise shows Not logged in page
     */
    public function trips() {
        if ($this->_model->getLoggedInMember()) {
            $this->redirectAbsolute($this->router->url('trips_my_trips'));
        } else {
            return $this->_notLoggedIn();
        }
    }

    public function myTrips() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            return $this->_notLoggedIn();
        }
        $pageNumber = 1;
        if (isset($this->route_vars['pageno'])) {
            $pageNumber = $this->route_vars['pageno'];
        }
        $page = new TripsMyTripsPage();
        $page->member = $loggedInMember;
        $count = $this->_model->getTripsForMemberCount($loggedInMember);
        $trips = $this->_model->getTripsForMember($loggedInMember, $pageNumber, self::TRIPS_PER_PAGE);
        $page->trips = $trips;
        $page->initPager('mytrips', $count, $pageNumber);

        $page->allTrips = $this->_model->getAllTripsForMember($loggedInMember);

        return $page;
    }

    public function upcomingTrips() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            return $this->_notLoggedIn();
        }
        $pageNumber = 1;
        if (isset($this->route_vars['pageno'])) {
            $pageNumber = $this->route_vars['pageno'];
        }
        $page = new TripsUpcomingPage();
        $page->member = $loggedInMember;
        $count = $this->_model->getUpcomingTripsCount();
        $trips = $this->_model->getUpcomingTrips($pageNumber, self::TRIPS_PER_PAGE);
        $page->trips = $trips;
        $page->initPager('upcoming', $count, $pageNumber);

        $page->allTrips = $this->_model->getAllUpcomingTrips();

        return $page;
    }

    public function pastTrips() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            return $this->_notLoggedIn();
        }
        $pageNumber = 1;
        if (isset($this->route_vars['pageno'])) {
            $pageNumber = $this->route_vars['pageno'];
        }
        $page = new TripsPastPage();
        $page->member = $loggedInMember;
        $count = $this->_model->getPastTripsCount();
        $trips = $this->_model->getPastTrips($pageNumber, self::TRIPS_PER_PAGE);
        $page->trips = $trips;
        $page->initPager('past', $count, $pageNumber);

        $page->allTrips = $this->_model->getAllPastTrips();

        return $page;
    }

    public function showAllTrips()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }

        $page_no = 1;
        if (isset($this->route_vars['pageno'])) {
            $page_no = $this->route_vars['pageno'];
        }

        $page = new TripsPage();
        $count = $this->_model->getTripsCount();
        $page->trips = $this->_model->getTrips(false, $page_no);
        $page->trip_data = $this->_model->getTripData();
        $page->allTrips = $this->_model->getAllTrips(self::TRIPS_PER_PAGE);
        $page->initPager($count);
        $page->member = $member;
        return $page;
    }

    public function showTripsForUsername() {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }

        $page_no = 1;
        if (isset($this->route_vars['pageno'])) {
            $page_no = $this->route_vars['pageno'];
        }
        $userName = $this->route_vars['username'];
        $page = new TripsPage();
        $count = $this->_model->getTripsCount($userName);
        $page->trips = $this->_model->getTrips($userName, $page_no);
        $page->trip_data = $this->_model->getTripData();
        $page->initPager($count);
        $page->member = $member;
        return $page;
    }

    public function tripsNearMe() {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }

        $pageNumber = 1;
        if (isset($this->route_vars['pageno'])) {
            $pageNumber = $this->route_vars['pageno'];
        }
        $page = new TripsNearMePage();
        $count = 10; // $this->_model->getTripsNearMeCount();
        $page->trips = $this->_model->getTripsNearMe($member, $pageNumber, self::TRIPS_PER_PAGE);
        $page->initPager('past', $count, $pageNumber);
        $page->member = $member;
        return $page;

    }

    /**
     * @param $tripid
     * @return SingleTripPage
     * @throws PException
     */
    public function showSingleTrip()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }
        $tripId = $this->route_vars['tripid'];
        $trip = $this->_model->getTrip($tripId);
        $trip_data = $this->_model->getTripData();
        if (!$trip)
        {
            return false;
        }
        $page = new TripSingleTripPage();
        $page->member = $member;
        $page->trip = $trip;
        $page->trip_data = $trip_data;
        $page->isOwnTrip = ($trip->IdMember == $member->id);

        return $page;
    }

    public function editCreateCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) {
        // Check variables
        $vars = $args->post;
        $mem_redirect->vars = $vars;
        list($errors, $tripInfo) = $this->_model->checkCreateEditVars( $vars );
        if (!empty($errors)) {
            $mem_redirect->errors = $errors;
            $mem_redirect->tripInfo = $tripInfo;
            return false;
        }

        $member = $this->_model->getLoggedInMember();
        if ($vars['trip-id'] == 0) {
            $errors = $this->_model->createTrip($tripInfo, $member);
        } else {
            $errors = $this->_model->editTrip($tripInfo, $member);
        }

        if (!empty($errors)) {
            $mem_redirect->errors = $errors;
            return false;
        }
        return $this->router->url('trip_show', array('username' => $member->Username), false);
    }


    /**
     * returns a template for a new location row (used through an Ajax call)
     */
    public function addLocation() {
        $locationRow = $this->route_vars['number'];
        $locationDetails = $this->_model->getEmptyLocationDetails();
        $errors = array();
        header('Content-type: text/html, charset=utf-8');
        include(SCRIPT_BASE . 'build/trips/templates/locationrow.helper.php');
        include(SCRIPT_BASE . 'build/trips/templates/locationrow.php');
        exit;
    }

    /**
     *
     */
    public function createTrip()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }
        $page = new TripsEditCreatePage(false);
        $page->member = $member;
        $page->vars = array(
            "trip-id" => 0,
            "trip-name" => "",
            "trip-description" => "",
            "trip-count" => "",
            "trip-additional-info" => "",
            "locations" => array(
                0 => $this->_model->getEmptyLocationDetails()
            )
        );

        return $page;
    }

    public function editTrip()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }
        $tripId = $this->route_vars['tripid'];
        $trip = $this->_model->getTrip($tripId);

        if (!$trip) {
            return false;
        }

        if ($trip->handle != $member->Username) {
            return false;
        }

        $page = new TripEditCreatePage(true);
        $page->member = $member;
        $page->vars = array(
            "tripid" => $trip->trip_id,
            "tripname" => $trip->trip_name,
            "tripdescription" => $trip->trip_descr
        );

        return $page;
    }

    public function deleteCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) {
        $errors = array();
        $vars = $args->post;
        $member = $this->_model->getLoggedInMember();
        if (isset($vars['trip-yes'])) {
            $errors = $this->_model->deleteTrip($vars, $member);
        }
        if (!empty($errors)) {
            $mem_redirect->errors = $errors;
            return false;
        }
        return $this->router->url('trip_show', array('username' => $member->Username), false);
    }

    public function deleteTrip()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }
        $tripId = $this->route_vars['tripid'];
        $trip = $this->_model->getTrip($tripId);

        if (!$trip) {
            return false;
        }

        if ($trip->handle != $member->Username) {
            return false;
        }

        $page = new TripDeletePage(true);
        $page->member = $member;
        $page->vars = array(
            "trip-id" => $trip->trip_id,
            "trip-name" => $trip->trip_name,
            "trip-desc" => $trip->trip_descr
        );

        return $page;
    }


    public function searchTripsCallback(StdClass $args, ReadOnlyObject $action,
                                             ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $errors = $this->_model->checkSearchTripsVarsOk($args);
        if (count($errors) > 0) {
            $_SESSION['errors'] = $errors;
            return $this->router->url('trips_search', array(), false);
        } else {
            return $this->router->url('trips_search_results', array( "keyword" => $args->post['activity-keyword']), false);
        }
    }

    public function search() {
        $page = new TripsSearchResultPage();
        $loggedInMember = $this->_model->getLoggedInMember();
        if ($loggedInMember) {
            $page->publicOnly = false;
        } else {
            $page->publicOnly = true;
        }
        $page->member = $loggedInMember;
        $pageNumber = 1;
        if (isset($this->route_vars['pageno'])) {
            $pageNumber = $this->route_vars['pageno'];
        }
        if (isset($this->route_vars['keyword'])) {
            $page->keyword = $this->route_vars['keyword'];
            $count = $this->_model->searchTripsCount($page->publicOnly, $page->keyword);
            $trips = $this->_model->searchTrips($page->publicOnly, $page->keyword, $pageNumber, self::TRIPS_PER_PAGE);
            $page->trips = $trips;
            $page->pager = $this->getPager('search/' . urlencode($page->keyword), $count, $pageNumber);

            $page->allTrips = $this->_model->searchTrips($page->publicOnly, $page->keyword, 0, PVars::getObj('trips')->max_trips_on_map);
        } else {
            $page->keyword = '';
        }
        return $page;
    }
}