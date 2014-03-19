<?php
/**
* Country view
*
* @package country
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class TourView extends PAppView {
	private $_model;
	
	public function __construct(Tour $model) {
		$this->_model = $model;
	}   
}
?>
