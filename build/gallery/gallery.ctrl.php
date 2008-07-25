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
class GalleryController extends RoxControllerBase {
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
        // that will shrink our code
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $cw = new ViewWrap($this);
        
        $P->addStyles .= $vw->customStylesLightview();
        
        $Page->currentTab = 'gallery';
        $subTab = 'browse';
        $name = false;

        $this->setTitleTranslate("GalleryTitle");

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
                                if ($str) {
                                $this->_model->ajaxModGallery($id,$str,'');
                                $str2 = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                                echo $str2;
                                } else echo 'Can`t be empty! Click to edit!';
                            } elseif( isset($_GET['text']) ) {
                                $str = htmlentities($_GET['text'], ENT_QUOTES, "UTF-8");
                                if (!$str) {
                                $str = ' ';
                                }
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
                $P->content .= $vw->uploadForm();
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
                $this->_view->userOverviewSimple($statement, $User->getHandle());
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
                $P->content .= $vw->latestFlickr();
                break;         

            case 'create':
                if (!$User = APP_User::login())
                    return false;
                $username = $User->getHandle();
                if (!isset($request[2])) {
                    $callbackId = $this->_model->updateGalleryProcess();
                }
                    PPostHandler::clearVars($callbackId);
                    $insertId = mysql_insert_id();
                    $loc_rel = 'gallery/show/galleries/'.$insertId;
                    header('Location: ' . PVars::getObj('env')->baseuri . $loc_rel);
                    PVars::getObj('page')->output_done = true;
                
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
                            $P->content .= $vw->latestOverview($statement);
                            break;
                        }
                        $image = $this->_model->imageData($request[3]);
                        if (!$image) {
                            $statement = $this->_model->getLatestItems();
                            $P->content .= $vw->latestOverview($statement);
                            break;
                        }
                        if (isset($request[4])) {
                            switch ($request[4]) {
                                case 'delete':
                                    $deleted = $this->_model->deleteOneProcess($image);
                                    $P->content .= $vw->imageDeleteOne($image,$deleted);
                                    $statement = $this->_model->getLatestItems();
                                    $P->content .= $vw->latestOverview($statement);
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
                        $P->addStyles .= $vw->customStyles2ColLeft();
                        $Previous = $this->_model->getPreviousItems($image->id,$limit=1,$image->user_id_foreign);
                        $Next = $this->_model->getNextItems($image->id,$limit=1,$image->user_id_foreign);
                        $P->newBar .= $vw->imageInfo($image);
                        $P->newBar .= $vw->imageSurroundItemsSmall($image,$Previous,$Next,1);
                        $P->newBar .= $vw->imageAddInfo($image);
                        $P->content .= $vw->image($image);
                        
                        break;
                        
                    case 'galleries':
                        if (!isset($request[3])) {
                            $galleries = $this->_model->getUserGalleries();
                            $P->content .= $vw->allGalleries($galleries);
                            break;
                        }
                        $gallery = $this->_model->getGallery($request[3]);
                        if (!$gallery) {
                            $galleries = $this->_model->getUserGalleries();
                            $P->content .= $vw->allGalleries($galleries);
                            break;
                        }
                        if (isset($request[4])) {
                            switch ($request[4]) {
                                case 'delete':
                                    $deleted = $this->_model->deleteGalleryProcess($request[3]);
                                    $P->content .= $vw->galleryDeleteOne($gallery,$deleted);
                                    $statement = $this->_model->getUserGalleries();
                                    $P->content .= $vw->allGalleries($statement);
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
                        if (!isset($statement)) {
                            $cnt_pictures = $this->_model->getLatestItems('',$gallery->id,1);
                            $P->newBar .= $vw->galleryInfo($gallery,$cnt_pictures);
                            $P->addStyles .= $vw->customStyles2ColLeft();
                            if (!$cnt_pictures)
                                $P->content .= $vw->uploadForm($gallery->id);
                            $statement = $this->_model->getLatestItems('',$gallery->id);
                            $P->content .= $vw->latestGallery($statement,$gallery->user_id_foreign);
                            $name = $gallery->title;
                        }
                        break;
                        
                    case 'user':
                        if (isset($request[3]) && preg_match(User::HANDLE_PREGEXP, $request[3]) && $userId = APP_User::userId($request[3])) {
                            if (isset($request[4]) && (substr($request[4], 0, 5) != '=page')) {
                                switch ($request[4]) {
                                    case 'pictures':
                                        $statement = $this->_model->getLatestItems($userId);
                                        $P->content .= $vw->userOverviewSimple($statement, $request[3], '');
                                        break;
                                    case 'sets':
                                        $this->_model->updateGalleryProcess();
                                        break;
                                    case 'galleries':
                                        $galleries = $this->_model->getUserGalleries($userId);
                                        $P->content .= $vw->allGalleries($galleries);
                                        $P->content .= $vw->userControls($request[3], 'galleries');
                                            
                                    default: 
                                        $cnt_pictures = $this->_model->getLatestItems($userId,'',1);
                                        $galleries = $this->_model->getUserGalleries($userId);
                                        $P->newBar .= $vw->userInfo($request[3],$galleries,$cnt_pictures);
                                        break;
                                }
                            break;
                            }    
                            $subTab = 'user';
                            $vars = PPostHandler::getVars($this->_model->uploadProcess());
                            if(isset($vars) && array_key_exists('error', $vars)) {
                                $P->content .= $vw->uploadForm();
                            }
                            else {
                                $cnt_pictures = $this->_model->getLatestItems($userId,'',1);
                                $galleries = $this->_model->getUserGalleries($userId);
                                $P->newBar .= $vw->userInfo($request[3],$galleries,$cnt_pictures);
                                $statement = $this->_model->getLatestItems($userId);
                                $P->content .= $vw->userOverview($statement, $request[3], $galleries);
                            }
                        break;
                        }
                        
                    default:
                        if (isset($_SESSION['Username'])) {
                            $userId = $_SESSION['Username'];
                            $cnt_pictures = $this->_model->getLatestItems($userId,'',1);
                            $galleries = $this->_model->getUserGalleries($userId);
                            $P->newBar .= $vw->userInfo($_SESSION['Username'],$galleries,$cnt_pictures);
                        } else {
                            // Doesn't work yet
                            //$loginWidget = $this->layoutkit;
                            //$loginWidget->createWidget('LoginFormWidget');
                            //$loginWidget->render();
                        }
                        $statement = $this->_model->getLatestItems();
                        $P->content .= $vw->latestOverview($statement);
                        break;
                }
        }
        $P->teaserBar .= $vw->teaser($name);
        // submenu
        $P->subMenu .= $vw->showsubmenu($subTab);
    }
    
    public function topMenu($currentTab) {
        $P->subMenu .= $vw->topMenu($currentTab);
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
