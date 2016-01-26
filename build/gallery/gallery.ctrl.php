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
        $this->_model = new GalleryModel();
        $this->_view  = new GalleryView($this->_model);
        $this->loggedInMember = $this->_model->getLoggedInMember();
        $this->username = $this->loggedInMember ? $this->loggedInMember->Username : false;
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index() 
    {
        $subTab = 'browse';
        $name = false;
        $loggedInMember = $this->loggedInMember;
        $membersmodel = $this->membersmodel = new MembersModel();

        $request = PRequest::get()->request;

        if (!isset($request[1])) {
            $this->redirect('main');
        }
        switch ($request[1]) {
            case 'ajax':
                if (!isset($request[2])){
                    PPHP::PExit();
                }
                $this->ajaxImageGallery($request[2]);
                break;
            case 'thumbimg':
                PRequest::ignoreCurrentRequest();
                if (!isset($_GET['id'])) {
                    PPHP::PExit();
                } else {
                    $id = (int) $_GET['id'];
                }
                if ($this->loggedInMember || $this->imageIsPublicById($id)) {
                    $this->thumbImg($id);
                } else {
                    PPHP::PExit();
                }
                break;

            case 'img':
                PRequest::ignoreCurrentRequest();
                if (!isset($_GET['id'])) {
                    PPHP::PExit();
                } else {
                    $id = (int) $_GET['id'];
                }
                if ($this->loggedInMember || $this->imageIsPublicById($id)) {
                    $this->_view->realImg($id);
                } else {
                    PPHP::PExit();
                }
                break;

            case 'images':
                $this->redirect('main');
                
            case 'upload':
                return $this->upload();
            
            case 'uploaded':
                if (!$loggedInMember)
                    return false;
                $this->uploadedProcess();
                break;

            case 'uploaded_done':
                $galleryId = (isset($_GET['id'])) ? $_GET['id'] : false;
                $this->ajaxlatestimages($galleryId, true);
                PPHP::PExit();
                
            case 'avatars': 
                if ($loggedInMember) {
                    $page = new GalleryAvatarsPage();
                    $userId = $loggedInMember->get_userid();
                    $page->statement = $this->_model->getLatestItems($userId);
                    return $page;
                } else $this->redirect('gallery');

            case 'create':
                if (!$loggedInMember)
                    return false;
                if (isset($request[2])) {
                    $vars['gallery'] = $this->_model->updateGalleryProcess();
                }
                $insertId = isset($vars['gallery']) ? $vars['gallery'] : mysql_insert_id();
                $loc_rel = 'gallery/show/sets/'.$insertId;
                header('Location: ' . PVars::getObj('env')->baseuri . $loc_rel);
                PVars::getObj('page')->output_done = true;
                break;              
                
            case 'manage':
                return $this->manage();
                break;

            case 'show':
            default:
                if (!isset($request[2]))
                    $request[2] = '';
                ob_start();
                switch ($request[2]) {
                    case 'image':
                        if (!isset($request[3])) {
                            $this->redirect('gallery');
                        }
                        if (!$image = $this->_model->imageData($request[3])) {
                            return new GalleryImageNotFoundPage();
                        }
                        if (!$loggedInMember &&
                            !$this->imageIsPublic($image)) {
                            $this->redirectToLogin(implode('/', $request));
                        }
                        switch (isset($request[4]) ? $request[4] : '') {
                            case 'delete':
                                return $this->deleteImage($image);
                            case 'edit':
                                $this->_model->editProcess($image);
                            default:
                                return $this->image($image);
                        }
                        break;
                        
                    case 'galleries':
                    case 'sets':
                        if (!isset($request[3]) || !$gallery = $this->_model->getGallery($request[3])) {
                            $this->redirect('main');
                        }
                        if (!$loggedInMember) {
                            // Check if gallery owner's profile is public,
                            // redirect to login if it is not
                            $owner = $this->_model->getMemberWithUserId(
                                $gallery->user_id_foreign);
                            if (!$owner->publicProfile) {
                                $this->redirectToLogin(implode('/', $request));
                            }
                        }
                        if (isset($request[4])) {
                            switch ($request[4]) {
                                case 'delete':
                                    $status = (isset($request[5]) && $request[5] == 'true') ? true : false;
                                    return $this->deleteGallery($gallery,$status);
                                case 'edit':
                                    if (isset($request[5]) && $request[5] == 'images') {
                                        // update/remove the pictures that belong to a gallery
                                        $result = $this->_model->updateGalleryProcess();
                                    } else {
                                        // edit the gallery information
                                        $this->_model->editGalleryProcess();
                                    }
                                    break;
                                case 'remove':
                                    $this->_model->editGalleryProcess();
                                    break;
                                case 'details':
                                    return $this->gallerydetails($gallery);
                                default:
                            }                      
                        } 
                        return $this->gallery($gallery,(isset($request[4]) && $request[4] == 'upload'));

                    case 'user':
                        $subTab = 'user';
                        if (isset($request[3])) {
                            $member = $membersmodel->getMemberWithUsername($request[3]);
                            if ($member) {
                                $userId = $member->get_userid();
                                $this->member = $member;
                                if (!$loggedInMember
                                    && !$member->publicProfile
                                ) {
                                    $this->redirectToLogin(
                                        implode('/', $request)
                                    );
                                }
                                if (isset($request[4])
                                    && (substr(
                                            $request[4], 0, 5
                                        ) != '=page')
                                ) {
                                    switch ($request[4]) {
                                    case 'galleries':
                                    case 'sets':
                                        return $this->user($userId);
                                    case 'pictures':
                                    case 'images':
                                    default:
                                        return $this->userimages($userId);
                                    }
                                }
                                return $this->user($userId);
                            }
                        }
                        
                    default:
                        $this->redirect('main');
                }
        }
    }

    /**
     * handles showing gallery overview page
     *
     * @access public
     * @return object $page
     */
    public function overview()
    {
        $page = new GalleryOverviewPage();
        if ($this->loggedInMember) {
            $page->images = $this->_model->getLatestItems($this->loggedInMember->get_userId());
            $page->galleries = $this->_model->getGalleriesNotEmpty();
            $page->cnt_pictures = $page->images ? $page->images->numRows() : 0;
        } else {
            $page->galleries = $this->_model->getGalleriesNotEmpty(false,
                true);
        }
        $page->loggedInMember = $this->_model->getLoggedInMember();
        $page->statement = $this->_model->getLatestItems();
        return $page;
    }
    
    /**
     * handles showing a page for a single gallery
     *
     * @param Gallery $gallery - gallery to work on
     *
     * @access public
     * @return object $page
     */
    public function gallery(Gallery $gallery,$upload = false)
    {
        $page = new GallerySetPage(); // TODO: Deal with the PageNames. We could easily name this GalleryPage but this reminds of the name of the app itself. How to proceed with this?        
        $page->loggedInMember = $this->loggedInMember;
        $page->myself = ($this->loggedInMember && $this->loggedInMember->get_userId() == $gallery->user_id_foreign) ? $this->loggedInMember->Username : false;
        $page->username = MOD_member::getUserHandle($gallery->user_id_foreign);
        $page->gallery = $gallery;
        $page->statement = $this->_model->getLatestItems('',$gallery->id);
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->upload = ($upload or !$page->cnt_pictures) ? true : false;        
        $page->member = $this->_model->getMemberWithUserId($gallery->user_id_foreign);
        $page->d = $this->_model->getLatestGalleryItem($gallery->id);
        $page->num_rows = $this->_model->getGalleryItems($gallery->id,1);
        return $page;
    }
    
    /**
     * handles the deletion of a gallery
     *
     * @param Gallery $gallery - gallery to delete
     *
     * @access public
     * @return object $page
     */
    public function deleteGallery(Gallery $gallery, $status)
    {
        $page = new GalleryDeletePage();
        $page->member = $this->_model->getMemberWithUserId($gallery->user_id_foreign);
        $page->loggedInMember = $this->loggedInMember;
        $user_id_foreign = $gallery->user_id_foreign;
        $page->myself = ($this->loggedInMember && ($this->loggedInMember->get_userId() == $user_id_foreign)) ? $this->loggedInMember->Username : false;
        $page->gallery = $gallery;
        if ($status) $page->deleted = $this->_model->deleteGalleryProcess($gallery->id);
        return $page;
    }
    
    /**
     * handles showing a page for a single gallery
     *
     * @access public
     * @return object $page
     */
    public function gallerydetails(Gallery $gallery)
    {
        $page = new GallerySetDetailsPage();
        
        //Check if current TB-user-id and Gallery-user-id are the same
        $page->loggedInMember = $this->loggedInMember;
        $page->myself = ($this->loggedInMember && ($this->loggedInMember->get_userId() == $gallery->user_id_foreign)) ? $this->loggedInMember->Username : false;
        $page->gallery = $gallery;
        $page->statement = $this->_model->getLatestItems('',$gallery->id);
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->upload = ((isset($request[4]) && $request[4] == 'upload') or !$page->cnt_pictures) ? true : false;
        $page->member = $this->_model->getMemberWithUserId($gallery->user_id_foreign);
        $page->d = $this->_model->getLatestGalleryItem($gallery->id);
        $page->num_rows = $this->_model->getGalleryItems($gallery->id,1);
        return $page;
    }

    /**
     * handles showing an overview of images and galleries of a user
     *
     * @access public
     * @return object $page
     */
    public function user($userId)
    {
        $words = $this->getWords();
        $page = new GalleryUserPage();
        $page->member = $this->member;
        $page->myself = ($this->loggedInMember && ($this->loggedInMember->get_userId() == $userId)) ? $this->loggedInMember : false;
        $page->infoMessage = $words->get($this->message);
        if ($page->myself) {
            $page->galleries = $this->_model->getUserGalleries($userId);
        } else {
            if ($this->loggedInMember) {
                $page->galleries = $this->_model->getGalleriesNotEmpty(
                    $userId);
            } else {
                $page->galleries = $this->_model->getGalleriesNotEmpty(
                    $userId, true);
            }
        }
        $page->statement = $this->_model->getLatestItems($userId);
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->loggedInMember = $this->loggedInMember;
        return $page;        
    }
    
    /**
     * handles showing a page where the user manages all his pictures
     *
     * @access public
     * @return object $page
     */
    public function manage()
    {
        if (!$this->loggedInMember)
            $this->redirect('login');
        $words = $this->getWords();
        $page = new GalleryManagePage();
        $page->member = $this->member = $this->loggedInMember;
        $page->myself = true;
        $page->infoMessage = $words->get($this->message);
        $page->galleries = $this->_model->getUserGalleries($this->member->get_userId());
        $page->statement = $this->_model->getLatestItems($this->member->get_userId());
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->loggedInMember = $this->loggedInMember;
        return $page;        
    }
    
    /**
     * handles showing a page where the user uploads pictures
     *
     * @access public
     * @return object $page
     */
    public function upload()
    {
        if (!$this->loggedInMember)
            $this->redirect('login');
        $words = $this->getWords();
        $page = new GalleryUploadPage();
        $page->member = $this->member = $this->loggedInMember;
        $page->myself = true;
        $page->infoMessage = $words->get($this->message);
        return $page;
    }
    
    /**
     * handles showing all galleries of a user
     *
     * @access public
     * @return object $page
     */
    public function usergalleries($userId)
    {
        $page = new GalleryUserGalleriesPage();
        $page->member = $this->member;
        $page->galleries = $this->_model->getUserGalleries($userId);
        $page->statement = $this->_model->getLatestItems($userId);
        $page->cnt_pictures = $page->statement? $page->statement->numRows() : 0;
        $page->model = $this->_model;
        $page->loggedInMember = $this->loggedInMember;
        return $page;
    }
    
    /**
     * handles showing all images of a user
     *
     * @access public
     * @return object $page
     */
    public function userimages($userId)
    {
        $page = new GalleryUserImagesPage();
        $page->member = $this->member;
        $page->myself = ($this->loggedInMember && ($this->loggedInMember->get_userId() == $userId)) ? $this->loggedInMember : false;
        $page->galleries = $this->_model->getUserGalleries($userId);
        $page->statement = $this->_model->getLatestItems($userId);
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->loggedInMember = $this->loggedInMember;
        return $page;        
    }
    
    /**
     * handles showing a page for a single image
     *
     * @access public
     * @return object $page
     */
    public function image($image)
    {
        $page = new GalleryImagePage();
        $page->image = $image;
        $page->infoMessage = $this->message;
        $page->previous = $this->_model->getPreviousItems($image->id,$limit=1,$image->user_id_foreign);
        $page->next = $this->_model->getNextItems($image->id,$limit=1,$image->user_id_foreign);
        $gallery_obj = $this->_model->getItemGallery($image->id);
        $galleryid = ($gallery_obj) ? $gallery_obj->fetch(PDB::FETCH_OBJ)->gallery_id_foreign : false;
        $page->gallery = $this->_model->getGallery($galleryid);
        return $page;
    }
    
    /**
     * handles the deletion of a single image
     *
     * @access public
     * @return object $page
     */
    public function deleteImage($image)
    {
        if ($deleted = $this->_model->deleteOneProcess($image)) {
            $this->message = 'Gallery_ImageDeleted';
            $this->member = $this->loggedInMember;
            $userId =$this->loggedInMember->get_userId();
            return $this->user($userId);
        } else {
            $this->message = 'Gallery_ImageNotDeleted';
            $this->member = $this->loggedInMember;
            if (!$this->member) {
                return false;
            }
            $userId =$this->loggedInMember->get_userId();
            return $this->user($userId);
        }
    }
    
    public function uploadedProcess($args, $action, $mem_redirect, $mem_resend)
    {
        // Process the uploaded pictures, display errors
        $userId = $this->_model->getLoggedInMember()->id;
        $username = $this->_model->getLoggedInMember()->Username;
        $vars = $args->post;
        $uploaded = $this->_model->uploadProcess($vars);
        if ($uploaded === false) {
        	$words = $this->getWords();
        	if (!empty($vars['error'])) { // upload failed altogether
        		$this->setFlashError($words->get($vars['error']));
        	} elseif (!empty($vars['fileErrors'])) { // upload of some files failed
        		$errorMessage = '';
        		foreach ($vars['fileErrors'] as $file => $message) {
        			$errorMessage .= $file . ' => ' . $words->get($message) . '<br />' . "\n";
        		}
        		$this->setFlashError($errorMessage);
    		}
        	return 'gallery/upload';
        }
        return 'gallery/show/user/' . $username . '/images';        
    }
    
    /**
     * handles showing all images of a user
     *
     * @access public
     * @return string
     */
    public function ajaxlatestimages($galleryId = false, $nopagination = false)
    {
        $loggedInMember = $this->loggedInMember;
        if ($galleryId) $statement = $this->_model->getLatestItems(false,$galleryId);
        else $statement = $this->_model->getLatestItems($loggedInMember->get_userId());
        $itemsPerPage = 6;
        require_once 'templates/overview.php';
    }

    /**
     * Handles edits to titles and descriptions of galleries and galleryitems
     *
     * that the user makes through Ajaxrequests
     *
     * @access private
     * @param string $type Indicator for Gallery ('set') or Image ('image')
     **/
    private function ajaxImageGallery($type) {
        $words = $this->getWords();
        PRequest::ignoreCurrentRequest();
        if (!$member = $this->loggedInMember)
            return false;
    	// Modifying an IMAGE using an ajax-request
        if( isset($_GET['item']) ) {
            $id = $_GET['item'];
            if ($member->get_userId() == $this->_model->imageGalleryOwner($type,$id)) {
                if( isset($_GET['title']) ) {
                    $str = htmlentities($_GET['title'], ENT_QUOTES, "UTF-8");
                    if ($str === '') {
                        echo $words->get('GalleryCannotBeEmpty');
                    } else {
                        $this->_model->ajaxModImageGallery($type,$id,$str,'');
                        $str = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                        echo $str;
                    }
                }
                if( isset($_GET['text']) ) {
                    $str = htmlentities($_GET['text'], ENT_QUOTES, "UTF-8");
                    $this->_model->ajaxModImageGallery($type, $id,'',$str);
                    $str = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                    if ($str === '') {
                        echo $words->get('GalleryAddDescription');
                    } else {
                        echo $str;
                    }
                }
            PPHP::PExit();
            }
        }
        echo 'Error!';
        PPHP::PExit();
    }
    
    /**
     * createGalleryCallback
     *
     * @param Object $args
     * @param Object $action 
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function createGalleryCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $vars = $args->post;
        $request = $args->request;
        //$errors = $this->model->checkCreateGalleryForm($vars);
        // Not a lot to check at this point:

        $errors = array();
        $desc = (isset($vars['g-description'])) ? $vars['g-description'] : false;
        if (!isset($vars['g-title']) || $vars['g-title'] == "") $errors[] = 'ErrorGalleryNoTitleSet';

        if (count($errors) > 0) {
            // show form again
            $vars['errors'] = $errors;
            $mem_redirect->post = $vars;
            return false;
        }

        if (!$galleryId = $this->_model->createGallery($vars['g-title'], $desc)) return false;
        return 'gallery/show/sets/'.$galleryId;
    }


    // callback processes moved from the model

    /**
     * xxx
     *
     * @access public
     */
    public function editProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling())
        {
            if (!$this->_model->getLoggedInMember())
            {
                return false;
            }

            $vars = &PPostHandler::getVars($callbackId);
            return $this->_model->editProcess($vars);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    /**
     * xxx
     *
     * @access public
     */
    public function editGalleryProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling())
        {
            if (!$this->_model->getLoggedInMember())
            {
                return false;
            }

            $vars = &PPostHandler::getVars($callbackId);
            return $this->_model->editGalleryProcess($vars);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function manageCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (!$this->_model->getLoggedInMember()){
            return false;
        }
        $vars = $args->post;
        $request = $args->request;
        if (array_key_exists('imageId', $vars))
            $mem_redirect->message_gallery = count($vars['imageId']);
        return $this->_model->updateGalleryProcess($vars);
    }

    public function updateGalleryCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (!$this->_model->getLoggedInMember()){
            return false;
        }
        $vars = $args->post;
        $request = $args->request;
        if (array_key_exists('imageId', $vars))
            $mem_redirect->message_gallery = count($vars['imageId']);
        return $this->_model->updateGalleryProcess($vars);
    }

    public function commentProcess($image = false)
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            if (!$this->_model->getLoggedInMember())
            {
                return false;
            }
            $vars =& PPostHandler::getVars();
            return $this->_model->commentProcess($vars, $image);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function uploadProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        $vars = &PPostHandler::getVars($callbackId);
        if (PPostHandler::isHandling())
        {
            $this->_model->uploadProcess($vars);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }
    
    public function thumbImg($id)
    {
        if (!$d = $this->_model->imageData($id))
            PPHP::PExit();
        $tmpDir = new PDataDir('gallery/user'.$d->user_id_foreign);
        if (isset($_GET['t'])) {
            $thumbFile = 'thumb'.(int)$_GET['t'].$d->file;
        } else {
            $thumbFile = 'thumb'.$d->file;
        }
        if (!$tmpDir->fileExists($thumbFile) || $tmpDir->file_Size($thumbFile) == 0) {
            if ($img = new MOD_images_Image($tmpDir->dirName().'/'.$d->file)) {
                if (!$this->_model->createThumbnails($tmpDir,$img) || (isset($_GET['t'])))
                    $thumbFile = $d->file;
            }
        }
        if (!$tmpDir->fileExists($thumbFile))  {
            $tmpDir = new PDataDir('gallery');
            $thumbFile = 'nopic.gif';
            $d->mimetype = 'image/gif';
        }
        header('Content-type: '.$d->mimetype);
        $tmpDir->readFile($thumbFile);
        PPHP::PExit();
    }

    /**
     * Checks if an image is publicly visible. Looks up the image by its ID.
     *
     * @param int $id Database ID of image
     *
     * @return bool True if publicly visible, false if not
     */
    private function imageIsPublicById($id)
    {
        $image = $this->_model->imageData($id);
        if (!$image) {
            return false;
        }
        return $this->imageIsPublic($image);
    }

    /**
     * Checks if an image is publicly visible.
     *
     * Note: Currently this only checks if the image owner's profile is public.
     * If individual image or album rights are implemented, they can be checked
     * here.
     *
     * @param object $image Image as returned by GalleryModel::imageData()
     *
     * @return bool True if publicly visible, false if not
     */
    private function imageIsPublic($image)
    {
        if (isset($this->membersmodel)) {
            $members = $this->membersmodel;
        } else {
            $members = new MembersModel();
        }
        $imageOwner = $members->getMemberWithUsername($image->user_handle);
        if ($imageOwner->publicProfile === false) {
            return false;
        } else {
            return true;
        }
    }

}
