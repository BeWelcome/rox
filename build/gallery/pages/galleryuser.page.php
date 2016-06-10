<?php


//------------------------------------------------------------------------------------
/**
 * page showing latest images and albums of a user
 * 
 *
 */

class GalleryUserPage extends GalleryBasePage
{

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function getSubmenuItems()
    {
        $member = $this->member;
        $words = $this->getWords();
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;

        $ViewForumPosts=$words->get("ViewForumPosts",$member->forums_posts_count()) ;
        $tt = array();
            $tt[]= array('overview', 'gallery/show/user/'.$member->Username.'/'.$this->page.'', $ww->GalleryTitleSets);
            $tt[]= array('images', 'gallery/show/user/'.$member->Username.'/images/'.$this->page.'', $ww->GalleryTitleLatest);
        if ($this->myself) {
            $tt[]= array("manage", 'gallery/manage', $ww->GalleryManage, 'manage');
            $tt[]= array("upload", 'gallery/upload', $ww->GalleryUpload, 'upload');
            echo $words->flushBuffer();
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


    protected function breadcrumbs() {
        $words = $this->words;
        return '<h1><a href="gallery">'.$words->get('Gallery').'</a> &raquo; <a href="gallery/show/user/'.$this->member->Username.'">'.ucfirst($this->member->Username).'</a></h1>';
    }

    protected function teaserHeadline() {
        $words = $this->words;
        return '<h3 class="userpage">'.MOD_layoutbits::PIC_50_50($this->member->Username,'',$style='float_left').' <a href="members/'.$this->member->Username.'">'.$this->member->Username.'</a></h3>';
    }
    
    protected function teaser() {
        ?>
        <div id="teaser">
        <div class="breadcrumbs">
        <?=$this->breadcrumbs()?>
        </div>
        <div class="clearfix">
            <?=$this->teaserHeadline()?>
            <div class="gallery_menu">
            <?=$this->submenu()?>
            </div>
        </div>
        </div>
        <?
    }
        
    public function leftSidebar()
    {
        $words = $this->words;
        $galleries = $this->galleries;
        $cnt_pictures = $this->cnt_pictures;
        $username = ($member = $this->loggedInMember) ? $member->Username : '';
        $loggedInMember = $this->loggedInMember;
        require SCRIPT_BASE . 'build/gallery/templates/userinfo.php';
    }

    protected function column_col3() {
        $statement = $this->statement;
        $words = new MOD_words($this->getSession());
        $username = $this->member->Username;
        $galleries = $this->galleries;
        $itemsPerPage = 6;
        require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
    }

}
