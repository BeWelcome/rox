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
        $this->_model = new TranslateModel();
    }
    
    public function __destruct() {
        unset($this->_model);
    }
    
    public function index()
    {
        $model = $this->_model;
        
        $request = PRequest::get()->request;
        if(!isset($request[1])) {
            $view = new AboutTheidea($model);
        } else switch ($request[1]) {
            case 'thepeople':
                $view = new AboutThepeople($model);
                break;
            case 'getactive':
                $view = new AboutGetactive($model);
                break;
            default:
                $view = new AboutTheidea($model);
        }
        $view->render();
    }
}
?>