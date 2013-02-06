<?php
/**
 * trip view
 *
 * @package trip
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: trip.view.php 233 2007-02-28 13:37:19Z marco $
 */
class TripView extends PAppView {
    private $_model;
    
    public function __construct(Trip $model) {
        $this->_model = $model;
    }

    public function createForm()
    {
    	$Trip = new TripController;
		$callbackId = $Trip->createProcess();
    	$editing = false;
    	require 'templates/createform.php';
    }

    public function userbar()
    {
        require 'templates/userbar.php';
    }
    
	public function displayTrips($trips, $trip_data, $page = 1) {
        $pages       = PFunctions::paginate($trips, $page);
        $trips       = $pages[0];
        $maxPage     = $pages[2];
        $pages       = $pages[1];
        $currentPage = $page;
		require 'templates/alltrips.php';
        $request = PRequest::get()->request;
        $requestStr = implode('/', $request);
        $requestStr = str_replace('/page'.$page,'',$requestStr);
        $this->pages($pages, $currentPage, $maxPage, $requestStr.'/page%d');
    }
    
    public function pages($pages, $currentPage, $maxPage, $request) 
    {
        require 'templates/pages.php';
    }
    
	public function displayMap($trips, $trip_data) {
	?>
        <?php
		require 'templates/map.php';
		?>
        <?php
	}
	public function displaySingleTrip($trip, $trip_data)
    {
        $member = $this->_model->getLoggedInMember();
		if (!$member)
        {
			$isOwnTrip = false;
		}
        else
        {
			$isOwnTrip = ($trip->IdMember == $member->id);
		}
		require 'templates/singletrip.php';
        
        $shoutsCtrl = new ShoutsController;
        $shoutsCtrl->shoutsList('trip', $trip->trip_id);
	}
    
    public function teaser($trip = false) {
        require 'templates/teaser.php';
    }
	
    public function teaser_singleTrip($trip = false) {
        require 'templates/singletrip_teaser.php';
    }
	
	public function editTrip($callbackId) {
		$editing = true;
    	require 'templates/createform.php';
		
	}
	
	public function delTrip($callbackId) {
		require 'templates/delform.php';
	}
    
    public function searchPage($trips = false, $trip_data = false) {
        require 'templates/searchpage.php';
    }
    
	/* This adds other custom styles to the page*/
	public function customStyles() {
        $out = '<link rel="stylesheet" href="styles/css/minimal/screen/custom/trip.css?2" type="text/css"/>';
		echo $out;
    }
}
