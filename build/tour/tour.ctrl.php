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

class TourController extends PAppController {
	private $_model;
	private $_view;

	public function __construct() {
		parent::__construct();
		$this->_model = new Tour();
		$this->_view =  new TourView($this->_model);
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

        if (!isset($request[1]) || $request[1]== '')
            $step = 'tour';
        else $step = $request[1];

        if (!isset($request[1]))
            $request[1] = '';
        
        // custom styles
            ob_start();
            $this->_view->customStyles1Col();
            $str = ob_get_contents();
            ob_end_clean();
            $Page = PVars::getObj('page');
            $Page->addStyles .= $str;

        // teaser content
            ob_start();
            $this->_view->ShowSimpleTeaser('tour_'.$step,$step);
            $str = ob_get_contents();
            $Page = PVars::getObj('page');
            $Page->teaserBar .= $str;
            ob_end_clean();
        // precontent
            ob_start();
            $this->_view->precontenttour($step);
            $str = ob_get_contents();
            ob_end_clean();
            $Page = PVars::getObj('page');
            $Page->newBar .= $str;
            
        switch($request[1]) {
            
            case 'share':
                ob_start();
                $this->_view->tourpage3();
                $str = ob_get_contents();
                ob_end_clean();
            break;
            
            case 'meet':
                ob_start();
                $this->_view->tourpage4();
                $str = ob_get_contents();
                ob_end_clean();
            break;
            
            case 'trips':
                ob_start();
                $this->_view->tourpage5();
                $str = ob_get_contents();
                ob_end_clean();
            break;
            
            case 'maps':
                ob_start();
                $this->_view->tourpage6();
                $str = ob_get_contents();
                ob_end_clean();
            break;
            
            case 'openness':
                ob_start();
                $this->_view->tourpage2();
                $str = ob_get_contents();
                ob_end_clean();
            break;
            
            default:
                ob_start();
                $this->_view->tourpage();
                $str = ob_get_contents();
                ob_end_clean();
                break;
            }
            $Page = PVars::getObj('page');
            $Page->content .= $str;
    }
	

}
?>
