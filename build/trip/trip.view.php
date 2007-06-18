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
    	require TEMPLATE_DIR.'apps/trip/createform.php';
    }

    public function userbar()
    {
    	if (!APP_User::login())
            return false;
        require TEMPLATE_DIR.'apps/trip/userbar.php';
    }
    
	public function displayTrips($trips, $trip_data) {
		require TEMPLATE_DIR.'apps/trip/alltrips.php';
	}
	
	public function displaySingleTrip($trip, $trip_data) {
		$User = APP_User::login();
		if (!$User) {
			$isOwnTrip = false;
		} else {
			$isOwnTrip = ($trip->user_id_foreign == $User->getId());
		}
		require TEMPLATE_DIR.'apps/trip/singletrip.php';
	}
	
	public function editTrip($callbackId) {
		$editing = true;
    	require TEMPLATE_DIR.'apps/trip/createform.php';
		
	}
	
	public function delTrip($callbackId) {
		require TEMPLATE_DIR.'apps/trip/delform.php';
	}
}
?>