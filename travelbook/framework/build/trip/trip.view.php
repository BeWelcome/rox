<?php
/**
 * trip view
 *
 * @package trip
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: trip.view.php 150 2006-07-26 12:06:23Z kang $
 */
class TripView extends PAppView {
    private $_model;
    
    public function __construct(Trip $model) {
        $this->_model = $model;
    }

    public function createForm()
    {
    	require TEMPLATE_DIR.'apps/trip/createform.php';
    }

    public function userbar()
    {
    	if (!APP_User::login())
            return false;
        require TEMPLATE_DIR.'apps/trip/userbar.php';
    }
}
?>