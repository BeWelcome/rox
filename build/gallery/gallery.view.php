<?php
/**
 * Gallery view
 *
 * @package gallery
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class GalleryView extends PAppView {
    private $_model;


    public function __construct(Gallery $model) 
    {
        $this->_model = $model;
    }


    /* This displays the custom teaser */
    public function teaser($name)
    {
        require 'templates/teaser.php';
    }

    /* This displays the optional precontent */
    public function precontent($gallery = false)
    {
        require 'templates/precontent_gallery.php';
    }
    
    public function customStyles2ColLeft()
	{		
	// calls a 1column layout 
		 echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/bw_basemod_2colleft300.css\" type=\"text/css\"/>";
	}    
    public function customStyles2ColRight()
	{		
	// calls a 1column layout 
		 echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/bw_basemod_2colright.css\" type=\"text/css\"/>";
	}    
    public function customStylesLightview()
	{		
	// calls a 1column layout 
		echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/bw_basemod_2col_wide.css\" type=\"text/css\"/>";
        echo "<link rel=\"stylesheet\" href=\"styles/lightview.css\" type=\"text/css\"/>";
	}        
    public function image($image) 
    {
        require 'templates/image.php';
    }
    public function imageInfo($image) 
    {
        require 'templates/imageinfo.php';
    }
    public function galleryInfo($gallery,$cnt_pictures) 
    {
        require 'templates/galleryinfo.php';
    }
    public function galleryDeleteOne($gallery,$deleted)
    {
        require 'templates/gallery_deleteone.php';
    }
    public function imageAddInfo($image) 
    {
        require 'templates/imageaddinfo.php';
    }
    public function userInfo($username,$galleries,$cnt_pictures) 
    {
        require 'templates/userinfo.php';
    }
    public function commentForm($image,$callbackId)
    {
        require 'templates/deleteone.php';
    }
    public function showsubmenu($subTab)
    {
        require 'templates/submenu.php';
    }    
    public function imageDeleteOne($image,$deleted)
    {
        require 'templates/deleteone.php';
    }
    public function loginWidget() 
    {
        $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
        $loginWidget->render();
    }
    public function imageSurroundItems($Previous = false, $Next = false)
    {
        require 'templates/surrounditems.php';
    }
    public function imageSurroundItemsSmall($image,$Previous = false, $Next = false, $UserId = false, $SetId = false)
    {
        require 'templates/surrounditems_small.php';
    }

    public function latestOverview($statement) 
    {
        require 'templates/latestoverview.php';
    }
    public function latestFlickr($statement = false) 
    {
        require 'templates/latestflickr.php';
    }
    public function latestGallery($statement, $userHandle = false, $type = 'gallery') 
    {
        $request = PRequest::get()->request;
        require 'templates/latestgallery.php';
        $shoutsCtrl = new ShoutsController;
        $shoutsCtrl->shoutsList('trip', $request[3]);
    }
    public function allGalleries($galleries) 
    {
        echo '<h3>Latest Photosets</h3>';
        require 'templates/galleries_overview.php';
    }
    public function errorReport($vars,$callbackId) 
    {
        $words = new MOD_words();
        echo '<p class="error">'.$words->getFormatted($vars).'</p>';  
        PPostHandler::clearVars($callbackId);
    }
    
    public function realImg($id)
    {
        if (!$d = $this->_model->imageData($id))
            PPHP::PExit();
        $tmpDir = new PDataDir('gallery/user'.$d->user_id_foreign);
        if (!$tmpDir->fileExists($d->file))
            PPHP::PExit();
        header('Content-type: '.$d->mimetype);
        if (isset($_GET['s'])) {
        	header('Content-Disposition: attachment; filename='.$d->original);
        }
        $tmpDir->readFile($d->file);
        PPHP::PExit();            
    } 

    public function userOverview($statement, $userHandle, $galleries = false) 
    {
        $itemsPerPage = 6;
        require 'templates/user_galleryoverview.php';
    }
    
    public function userOverviewSimple($statement, $userHandle, $galleries = false) 
    {
        $words = new MOD_words();
        $Gallery = new Gallery;
        $callbackId = $Gallery->updateGalleryProcess();
        $vars =& PPostHandler::getVars($callbackId);
        if (!isset($vars['errors']))
            $vars['errors'] = array();
        $type = 'images';
        $galleries = $this->_model->getUserGalleries();
        echo '
        <form method="post" action="gallery/show/user/'.$userHandle.'/pictures" name="mod-images" class="def-form">
        <input type="hidden" name="'.$callbackId.'" value="1"/>
        ';
        if (in_array('gallery', $vars['errors'])) {
            echo '<span class="error">'.$words->get('GalleryErrorsPhotosets').'</span>';
        }
        if (in_array('images', $vars['errors'])) {
            echo '<span class="error">'.$words->get('GalleryErrorsImages').'</span>';
        }
        require 'templates/overview.php';
        require 'templates/user_controls.php';
        echo '</form>';
    }
    
    public function userControls($userHandle, $type = 'all') 
    {
        require 'templates/user_controls.php';
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
        if (!$tmpDir->fileExists($thumbFile))
            $thumbFile = $d->file;
        if (!$tmpDir->fileExists($thumbFile) || ($tmpDir->file_Size($thumbFile) == 0)) {
            $tmpDir = new PDataDir('gallery');
            $thumbFile = 'nopic.gif';
            $d->mimetype = 'image/gif';
        }
        header('Content-type: '.$d->mimetype);
        $tmpDir->readFile($thumbFile);
        PPHP::PExit();            
    } 

    public function uploadForm($galleryId = false, $hide = false) 
    {
        require 'templates/uploadform.php';
    }

    public function userBar()
    {
        require 'templates/userbar.php';
    }

    public function xpPubWiz()
    {
        header('Content-type: text/html;charset="utf-8"');
        require 'templates/xppubwiz.php';
        PPHP::PExit();
    }
    
    public function topMenu($currentTab) {
        require TEMPLATE_DIR.'shared/roxpage/topmenu.php';
    }
}
?>