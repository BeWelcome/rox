<?php

/**
 * Aboutus controller
 *
 * @package about
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutController extends PAppController {
    
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
        if(!isset($request[1])) {
            $view = new AboutTheidea();
        } else switch ($request[1]) {
            case 'thepeople':
                $view = new AboutThepeople();
                break;
            case 'getactive':
                $view = new AboutGetactive();
                break;
            case 'bod':
            case 'help':
            case 'terms':
            case 'impressum':
            case 'affiliations':
            case 'privacy':
                $view = new AboutGenericView($request[1]);
                break;
            case 'theidea':
            default:
                $view = new AboutTheidea();
        }
        $view->setModel($model);
        $view->render();
    }
}


?>