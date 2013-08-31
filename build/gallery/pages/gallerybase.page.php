<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the gallery system,
 * which don't belong to one specific gallery.
 *
 */

class GalleryBasePage extends PageWithActiveSkin
{
    protected function init()
    {
        $this->page_title = 'Gallery | BeWelcome';
        $this->model = new GalleryModel();
    }
    
    protected function teaser() {
        ?>
        <div id="teaser">
        <div class="breadcrumbs">
        <?=$this->breadcrumbs()?>
        </div>
        <div class="floatbox">
            <?=$this->teaserHeadline()?>
            <div class="gallery_menu">
            <?=$this->submenu()?>
            </div>
        </div>
        </div>
        <?
    }

    protected function breadcrumbs() {
        return '<h1><a href="gallery">'.$this->getWords()->getBuffered('Gallery').'</a>' . $this->getWords()->flushBuffer() . '</h1>'; 
    }

    protected function teaserHeadline() {
        echo $this->getWords()->getBuffered('Gallery') . $this->getWords()->flushBuffer();
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'gallery';
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }
    
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/lightview.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/gallery.css';
        return $stylesheets;
    }
    
    protected function getMessage()
    {
        return $this->message;
    }
    
    /*
    *  Custom functions
    *
    */
    
    protected function getSubmenuItems()
    {
        return array();
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
    
    protected function userLinks()
    {
        $member = $this->loggedInMember;
        $ww = $this->ww;
        if ($member && ($member->Status == 'Active' || $member->Status == 'NeedMore' || $member->Status == 'Pending')) {
            $user_links = '<div id="gallery_userlinks" style="float:right">';
            foreach ($items = $this->getUserLinksItems() as $item)
                $user_links .= '<a href="'.$item[1].'" class="'.$item[3].'"><span>'.$item[2].'</span></a>';
            $user_links .= '</div>';
            return $user_links;
        }
    }
    
    protected function getUserLinksItems()
    {
        if ($member = $this->loggedInMember) {
            $words = $this->words;
            $ww = $this->ww;
            $items = array();
            $items[] = array('user', 'gallery/manage', $ww->GalleryManage, 'bigbuttongrey');
            $items[] = array('user', 'gallery/show/user/'.$member->Username, $ww->GalleryMy, 'bigbuttongrey');
            $items[] = array('upload', 'gallery/upload', $ww->GalleryUpload, 'bigbuttongrey');
            return $items;
        }
    }


}

?>
