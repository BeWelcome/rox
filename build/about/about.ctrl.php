<?php

/**
 * Aboutus controller
 *
 * @package about
 * @author Andreas (lemon-head)
 * @copyright hmm
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutController extends PAppController
{
    public function __construct() {
        parent::__construct();
        $this->_model = new AboutModel();
    }
    
    public function __destruct() {
        unset($this->_model);
    }
    
    public function index()
    {
        $model = $this->_model;
        
        $request = PRequest::get()->request;
        
        if (!isset($request[0])) {
            // then who activated the about controller?
            $view = new AboutTheidea();
        } else if ($request[0] != 'about') {
            $view = $this->_getViewByKeyword($request[0]);
        } else if (!isset($request[1])) {
            $view = new AboutTheidea();
        } else {
            $view = $this->_getViewByKeyword($request[1]); 
        }
        $view->setModel($model);
        $view->render();
    }
    
    private function _getViewByKeyword($keyword)
    {   
        switch ($keyword) {
            case 'thepeople':
                return new AboutThepeople();
            case 'getactive':
                return new AboutGetactive();
            case 'bod':
            case 'help':
            case 'terms':
            case 'impressum':
            case 'affiliations':
            case 'privacy':
                return new AboutGenericView($keyword);
            case 'stats':
                return new StatsView();
            case 'theidea':
            default:
                return new AboutTheidea();
        }
    }
}

/*
 * the following controllers wouldn't work,
 * because the central router does only route to direct subclasses of PAppController
 * maybe a future version of the framework will allow the trick.
 * Until then, we should use the rox controller or sth else, to route these requests.
 * 
class BodController extends AboutController {}
class HelpController extends AboutController {}
class TermsController extends AboutController {}
class ImpressumController extends AboutController {}
class AffiliationsController extends AboutController {}
class PrivacyController extends AboutController {}
class StatsController extends AboutController {}
*/



?>