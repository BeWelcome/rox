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


    public function __construct(GalleryModel $model) 
    {
        $this->_model = $model;
    }
    public function loginWidget() 
    {
        $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
        $loginWidget->render();
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
    public function userOverviewSimple($statement, $userHandle, $galleries = false) 
    {
        $words = new MOD_words();
        $Gallery = new GalleryController;
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

}
