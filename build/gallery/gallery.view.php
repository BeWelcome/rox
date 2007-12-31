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
    
    /* This displays the custom teaser */
    public function teaser()
    {
        require TEMPLATE_DIR.'apps/gallery/teaser.php';
    }
    
    public function __construct(Gallery $model) 
    {
        $this->_model = $model;
    }

    public function image($image) 
    {
        require TEMPLATE_DIR.'apps/gallery/image.php';
    }

    public function imageDeleteOne($image)
    {
        require TEMPLATE_DIR.'apps/gallery/deleteone.php';
    }

    public function latestOverview($statement) 
    {
        require TEMPLATE_DIR.'apps/gallery/latestoverview.php';
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

    public function userOverview($statement, $userHandle) 
    {
        require TEMPLATE_DIR.'apps/gallery/useroverview.php';
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
        if (!$tmpDir->fileExists($thumbFile))
            PPHP::PExit();
        header('Content-type: '.$d->mimetype);
        $tmpDir->readFile($thumbFile);
        PPHP::PExit();            
    } 

    public function uploadForm() 
    {
        require TEMPLATE_DIR.'apps/gallery/uploadform.php';
    }

    public function userBar()
    {
        require TEMPLATE_DIR.'apps/gallery/userbar.php';
    }

    public function xpPubWiz()
    {
        header('Content-type: text/html;charset="utf-8"');
        require TEMPLATE_DIR.'apps/gallery/xppubwiz.php';
        PPHP::PExit();
    }
    
    public function topMenu($currentTab) {
        require TEMPLATE_DIR.'apps/rox/topmenu.php';
    }
}
?>