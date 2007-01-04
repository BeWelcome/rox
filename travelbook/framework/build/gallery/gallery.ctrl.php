<?php
/**
 * Gallery controller
 *
 * @package gallery
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class GalleryController extends PAppController {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new Gallery();
        $this->_view  = new GalleryView($this->_model);
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index() 
    {
        if ($User = APP_User::login()) {
            ob_start();
            $this->_view->userBar();
            $str = ob_get_contents();
            ob_end_clean();
            $Page = PVars::getObj('page');
            $Page->content .= $str;
        }
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';
        switch ($request[1]) {
        	case 'deleteall':
                if (!PVars::get()->debug)
                    PPHP::PExit();
                $this->_model->deleteAll();
                echo 'deleted.';
                PPHP::PExit();
                break;
                
        	case 'thumbimg':
                PRequest::ignoreCurrentRequest();
                if (!isset($_GET['id']))
                    PPHP::PExit();
                $this->_view->thumbImg((int)$_GET['id']);
                break;
                
            case 'img':
                PRequest::ignoreCurrentRequest();
                if (!isset($_GET['id']))
                    PPHP::PExit();
                $this->_view->realImg((int)$_GET['id']);
                break;
                
            case 'upload':
                ob_start();
                $this->_view->uploadForm();
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;
                break;
            
            case 'uploaded':
                if (!$User = APP_User::login())
                    return false;
                $userId = $User->getId();
                $statement = $this->_model->getLatestItems($userId);
                ob_start();
                $this->_view->userOverview($statement, $User->getHandle());
                $str = ob_get_contents();
                ob_end_clean();
                if (isset($_GET['raw'])) {
                    echo $str;
                    PPHP::PExit();
                }
                $Page = PVars::getObj('page');
                $Page->content .= $str;
                break;
                
            case 'xppubwiz':
                $this->_view->xpPubWiz();
                break;

            case 'show':
        	default:
                if (!isset($request[2]))
                    $request[2] = '';
                ob_start();
                switch ($request[2]) {
                	case 'image':
                        if (!isset($request[3])) {
                            $statement = $this->_model->getLatestItems();
                            $this->_view->latestOverview($statement);
                        	break;
                        }
                        $image = $this->_model->imageData($request[3]);
                        if (!$image) {
                            $statement = $this->_model->getLatestItems();
                            $this->_view->latestOverview($statement);
                            break;
                        }
                        if (isset($request[4])) {
                            switch ($request[4]) {
                                case 'delete':
                                    $this->_view->imageDeleteOne($image);
                                    break;
                            }
                        }
                        $this->_view->image($image);
                        break;
                        
                    case 'galleries':
                        break;
                        
                	case 'user':
                        if (isset($request[3]) && preg_match(User::HANDLE_PREGEXP, $request[3]) && $userId = APP_User::userId($request[3])) {
                            $statement = $this->_model->getLatestItems($userId);
                            $this->_view->userOverview($statement, $request[3]);
                        } else {
                            $statement = $this->_model->getLatestItems();
                            $this->_view->latestOverview($statement);
                        }
                        break;
                        
                    default:
                        $statement = $this->_model->getLatestItems();
                        $this->_view->latestOverview($statement);
                        break;
                }
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;
                break;
        }
    }
}
?>