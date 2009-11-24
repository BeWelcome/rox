<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GallerySetPage extends GalleryBasePage
{

    protected function teaserHeadline() {
        $words = $this->words;
        return '<a href="gallery">'.$words->get('Gallery').'</a> > <a href="gallery/show/user/'.$this->member->Username.'">'.ucfirst($this->member->Username).'</a> > <a href="gallery/show/user/'.$this->member->Username.'/sets">'.$words->get("Photosets").'</a>';
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

        $ViewForumPosts=$words->get("ViewForumPosts",$member->forums_posts_count()) ;
        $tt = array();
            $tt[]= array('thumbnails', 'gallery/show/sets/'.$gallery->id.'/'.$this->page.'', $ww->GalleryThumbnails);
            $tt[]= array('details', 'gallery/show/sets/'.$gallery->id.'/details/'.$this->page.'', $ww->GalleryDetails);
        if ($this->myself) {
            $tt[]= array("upload", 'gallery/show/sets/'.$gallery->id.'/upload', $ww->GalleryUpload, 'upload');
            $tt[]= array("delete", 'gallery/show/sets/'.$gallery->id.'/delete', $ww->GalleryDelete, 'delete');
        }
        return($tt) ;
    }
    
    protected function submenu() {
        $active_menu_item = $this->getSubmenuActiveItem();
        $cnt = count($this->getSubmenuItems());
        $ii = 1;
        foreach ($this->getSubmenuItems() as $index => $item) {
            $name = $item[0];
            $url = $item[1];
            $label = $item[2];
            $class = isset($item[3]) ? $item[3] : '';
            if ($name === $active_menu_item) {
                $attributes = ' class="active '.$class.'"';
            } else {
                $attributes = ' class="'.$class.'"';
            }

            ?><a <?=$attributes ?> style="cursor:pointer;" href="<?=$url ?>"><span><?=$label ?></span></a> <?=($ii++ != $cnt) ? '|': '' ?>
            <?php

        }
    }
    
    protected function column_col3() {
        $words = $this->words;
        $cnt_pictures = $this->cnt_pictures;
        $statement = $this->statement;
        $gallery = $this->gallery;
        $uploaderUrl = 'gallery/uploaded_done/?id='.$gallery->id;
        $d = $this->d;
        $num_rows = ($this->num_rows) ? $this->num_rows : 0;
        echo '<h2><a href="gallery/show/sets/'.$gallery->id.'" class="black">'.$gallery->title.'</a></h2>';
        echo '<div class="gallery_menu">';
        echo $this->submenu().'</div>';
        require SCRIPT_BASE . 'build/gallery/templates/galleryset.column_col3.php';
    }
    
    public function leftSidebar() {
    }
    
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
        return $stylesheets;
    }
    
    /*
    *  Custom functions
    *
    */


}

?>
