<?php
/**
* country controller
*
* @package country
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class CountryController extends PAppController {
	private $_model;
	private $_view;
	
	public function __construct() {
		parent::__construct();
		$this->_model = new Country();
		$this->_view =  new CountryView($this->_model);
	}
	
	public function __destruct() {
		unset($this->_model);
		unset($this->_view);
	}
	
	/**
	* index is called when http request = ./country
	*/
	public function index() {
		$request = PRequest::get()->request;
		$User = APP_User::login();

		ob_start();
		
		if (isset($request[1]) && $request[1]) {
			$countryinfo = $this->_model->getCountryInfo($request[1]);
			if (!$countryinfo) {
				$this->_view->countryNotFound();
			} else {
				$members = $this->_model->getMembersOfCountry($request[1]);
				$this->_view->displayCountryInfo($countryinfo, $members);
			}
		} else {
			$countries = $this->_model->getAllCountries();
			$this->_view->displayCountryOverview($countries);
		}
		
		$Page = PVars::getObj('page');
		$Page->content .= ob_get_contents();
		ob_end_clean();
	}
	

}
?>
