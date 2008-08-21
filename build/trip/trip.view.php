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
    	$Trip = new Trip;
		$callbackId = $Trip->createProcess();
    	$editing = false;
    	require 'templates/createform.php';
    }

    public function userbar()
    {
        require 'templates/userbar.php';
    }
    
	public function displayTrips($trips, $trip_data) {
		require 'templates/alltrips.php';
	}
	public function displayMap($trips, $trip_data) {
		require 'templates/map.php';
	}
	public function displaySingleTrip($trip, $trip_data) {
		$User = APP_User::login();
		if (!$User) {
			$isOwnTrip = false;
		} else {
			$isOwnTrip = ($trip->user_id_foreign == $User->getId());
		}
		require 'templates/singletrip.php';
	}
	public function displaySingleTrip_Sidebar($trip, $trip_data) {
		$User = APP_User::login();
		if (!$User) {
			$isOwnTrip = false;
		} else {
			$isOwnTrip = ($trip->user_id_foreign == $User->getId());
		}
		require 'templates/singletrip_sidebar.php';
	}
	public function displaySingleTrip_Map($trip, $trip_data) {
		$User = APP_User::login();
		if (!$User) {
			$isOwnTrip = false;
		} else {
			$isOwnTrip = ($trip->user_id_foreign == $User->getId());
		}
		require 'templates/singletrip_map.php';
	}
    
    public function teaser($trip = false) {
        require 'templates/teaser.php';
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
        $out = '<link rel="stylesheet" href="styles/YAML/screen/custom/trip.css" type="text/css"/>';
		echo $out;
    }
}
?>