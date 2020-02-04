<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GallerySetPage extends GalleryBasePage
{
    protected function init()
    {
        parent::init();
        $this->addLateLoadScriptFile('build/gallery.js');
    }

    protected function breadcrumbs() {
        $words = $this->words;
        return '<h3>'.$words->get('Gallery').' &raquo; <a href="gallery/show/user/'.$this->member->Username.'">'.ucfirst($this->member->Username).'</a> &raquo; <a href="gallery/show/user/'.$this->member->Username.'/sets">'.$words->get("Photosets").'</a></h3>';
    }

    protected function teaserHeadline() {
        $words = $this->words;
        return '<h2><i class="fa fa-image mr-1"></i>'.htmlspecialchars($this->gallery->title).'</h2>';
    }

    protected function teaser() {
        ?>
        <div>
            <div>
            <?=$this->breadcrumbs()?>
            </div>
            <div class="w-100">
                <?=$this->teaserHeadline()?>
            </div>
        </div>
        <?php
    }

    protected function getTopmenuActiveItem()
    {
        return 'gallery';
    }

    protected function getSubmenuActiveItem()
    {
        if ($this->upload) return 'upload';
        return 'thumbnails';
    }

    protected function getSubmenuItems()
    {
        $username = $this->member->Username;
        $member = $this->member;
        $gallery = $this->gallery;
        $words = $this->getWords();
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;

        $tt = array();
            $tt[]= array('albums', 'gallery/show/user/'. $username .'/sets', $ww->GalleryTitleSets);
            // $tt[]= array('details', 'gallery/show/sets/'.$gallery->id.'/details/'.$this->page.'', $ww->GalleryDetails);
        if ($this->myself) {
            $tt[]= array("manage", 'gallery/manage', $ww->GalleryManage, 'manage');
            $tt[]= array("upload", 'gallery/upload_multiple', $ww->GalleryUpload, 'upload');
            $tt[]= array("delete", 'gallery/show/sets/'.$gallery->id.'/delete', $ww->GalleryDelete, 'delete');
        }
        return($tt) ;
    }

    protected function column_col3() {
        // $mem_redirect = $this->layoutkit->formkit->getMemFromRedirect();
        //         if ($mem_redirect) $this->message = $mem_redirect->message_gallery;
        $words = $this->words;
        $cnt_pictures = $this->cnt_pictures;
        $statement = $this->statement;
        $gallery = $this->gallery;
        $uploaderUrl = 'gallery/uploaded_done/?id='.$gallery->id;
        $d = $this->d;
        $num_rows = ($this->num_rows) ? $this->num_rows : 0;
        require SCRIPT_BASE . 'build/gallery/templates/galleryset.column_col3.php';
    }

    public function leftSidebar() {
    }

    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }
}

?>
