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
        ob_start();
        $this->_view->teaser();
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->teaserBar .= $str;
        ob_end_clean();
        
        ob_start();
        $this->_view->customStylesLightview();
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->addStyles .= $str;
        ob_end_clean(); 
        
        $Page->currentTab = 'gallery';
        $subTab = 'browse';
        
      //  if ($User = APP_User::login()) {
//            ob_start();
  //          $this->_view->userBar();
    //        $str = ob_get_contents();
    //        ob_end_clean();
    //        $Page = PVars::getObj('page');
    //        $Page->newBar .= $str;
        //    $Page->currentTab = 'gallery';
    //    }
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';
        switch ($request[1]) {
            case 'ajax':
                if (!isset($request[2]))
                    PPHP::PExit();
                switch ($request[2]) {
                    case 'set':
                        PRequest::ignoreCurrentRequest();
                        if (!$User = APP_User::login())
                            return false;
                        if (isset($_GET['item']) ) {
                            $id = $_GET['item'];
                            if( isset($_GET['title']) ) {
                                $str = htmlentities($_GET['title'], ENT_QUOTES, "UTF-8");
                                $this->_model->ajaxModGallery($id,$str,'');
                                $str2 = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                                echo $str2;
                            } elseif( isset($_GET['text']) ) {
                                $str = htmlentities($_GET['text'], ENT_QUOTES, "UTF-8");
                                $this->_model->ajaxModGallery($id,'',$str);
                                echo $str;
                            }
                        }
                        PPHP::PExit();
                        break;
                    case 'image':
                        if( isset($_GET['item']) ) {
                            $id = $_GET['item'];
                            if( isset($_GET['title']) ) {
                                $str = htmlentities($_GET['title'], ENT_QUOTES, "UTF-8");
                                $this->_model->ajaxModImage($id,$str,'');
                                $str = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                                echo $str;
                            }
                            if( isset($_GET['text']) ) {
                                $str = htmlentities($_GET['text'], ENT_QUOTES, "UTF-8");
                                $this->_model->ajaxModImage($id,'',$str);
                                $str = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                                echo $str;
                            }
                        }
                        PPHP::PExit();
                        break;
                }
                break;
        
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
                $subTab = 'upload';
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
                $callbackId = $this->_model->uploadProcess();
                $vars = PPostHandler::getVars($callbackId);
                ob_start();
                if(isset($vars['error'])) {
                $this->_view->errorReport($vars['error'],$callbackId);
                }
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
                
            case 'flickr':
                $subTab = 'upload';
                ob_start();
                $this->_view->latestFlickr();
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;
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
                                    $deleted = $this->_model->deleteOneProcess($image);
                                    $this->_view->imageDeleteOne($image,$deleted);
                                    $statement = $this->_model->getLatestItems();
                                    $this->_view->latestOverview($statement);
                                    break;
                                case 'edit':
                                    $this->_model->editProcess($image);
                                    break;
                                case 'comment':
                                    $this->_model->commentProcess($image);
                                    break;
                                }
                            if ($request[4] == 'delete')
                            break;                            
                        } 

                        $Previous = $this->_model->getPreviousItems($image->id,$limit=1,$image->user_id_foreign);
                        $Next = $this->_model->getNextItems($image->id,$limit=1,$image->user_id_foreign);
                        $this->_view->imageInfo($image);
                        $this->_view->imageSurroundItemsSmall($image,$Previous,$Next,1);
                        $this->_view->imageAddInfo($image);
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;
                        ob_start();
                        $this->_view->image($image);
                        
                        break;
                        
                    case 'galleries':
                        if (!isset($request[3])) {
                            $galleries = $this->_model->getUserGalleries();
                            $this->_view->allGalleries($galleries);
                            break;
                        }
                        $gallery = $this->_model->getGallery($request[3]);
                        if (!$gallery) {
                            $galleries = $this->_model->getUserGalleries();
                            $this->_view->allGalleries($galleries);
                            break;
                        }
                        if (isset($request[4])) {
                            switch ($request[4]) {
                                case 'delete':
                                    $deleted = $this->_model->deleteGalleryProcess($request[3]);
                                    $this->_view->galleryDeleteOne($gallery,$deleted);
                                    $statement = $this->_model->getGallery();
                                    $this->_view->latestGallery($statement);
                                    break;
                                case 'edit':
                                    if (isset($request[5]) && $request[5] == 'images') {
                                        // update/remove the pictures that belong to a gallery
                                        $this->_model->updateGalleryProcess();
                                    } else {
                                        // edit the gallery information
                                        $this->_model->editGalleryProcess();
                                    }
                                    break;
                                case 'remove':
                                    $this->_model->editGalleryProcess();
                                    break;
                                default:
                                }                           
                        } 

                        $cnt_pictures = $this->_model->getLatestItems('',$gallery->id,1);
                        $this->_view->galleryInfo($gallery,$cnt_pictures);
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;
                        
                        ob_start();
                        $this->_view->customStyles2ColLeft();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->addStyles .= $str;
                        ob_start();
                        $statement = $this->_model->getLatestItems('',$request[3]);
                        $this->_view->latestGallery($statement,$gallery->user_id_foreign);
                        
                        break;
                        
                    case 'user':
                        if (isset($request[3]) && preg_match(User::HANDLE_PREGEXP, $request[3]) && $userId = APP_User::userId($request[3])) {
                            if (isset($request[4])) {
                                switch ($request[4]) {
                                    case 'sets':
                                        $this->_model->updateGalleryProcess();
                                        break;
                                    case 'galleries':
                                            $galleries = $this->_model->getUserGalleries($userId);
                                            $this->_view->userControls($request[3], 'galleries');
                                            $this->_view->allGalleries($galleries);
                                            $str = ob_get_contents();
                                            ob_end_clean();
                                            $Page = PVars::getObj('page');
                                            $Page->content .= $str;
                                            ob_start();
                                            break;
                                            
                                    default: break;
                                } break;
                            }    
                            $subTab = 'user';
                            $vars = PPostHandler::getVars($this->_model->uploadProcess());
                            if(isset($vars) && array_key_exists('error', $vars)) {
                                $this->_view->uploadForm();
                            }
                            else {
                                $cnt_pictures = $this->_model->getLatestItems($userId,'',1);
                                $galleries = $this->_model->getUserGalleries($userId);
                                $this->_view->userInfo($request[3],$galleries,$cnt_pictures);
                                $str = ob_get_contents();
                                ob_end_clean();
                                $Page = PVars::getObj('page');
                                $Page->newBar .= $str;
                                ob_start();
                                $statement = $this->_model->getLatestItems($userId);
                                $this->_view->userOverview($statement, $request[3], $galleries);
                            }

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
        }
        // submenu
        ob_start();
        $this->_view->showsubmenu($subTab);
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->subMenu .= $str;
        ob_end_clean();
    }
    
    public function topMenu($currentTab) {
        $this->_view->topMenu($currentTab);
    }
    
    public function LatestGalleryItem($galleryId) {
        $this->_model->getLatestGalleryItem($galleryId);
    }
    public function getItems($userId,$galleryId) {
        $this->_model->getLatestItems($userId,$galleryId);
    }

    private function ajaxImage($items) {
    	// Validate the array
    	foreach ($items as &$item) {
    		$item = (int) $item;
    	}
    	$this->_model->ajaxModImage($items);
    }
    private function ajaxGallery($items) {
    	// Validate the array
    	foreach ($items as &$item) {
    		$item = (int) $item;
    	}
    	$this->_model->ajaxModGallery($items);
    }    
}
?>