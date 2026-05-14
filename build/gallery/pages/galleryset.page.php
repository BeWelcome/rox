<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GallerySetPage extends GalleryBasePage
{
    #[\Override]
    protected function init()
    {
        parent::init();
        $this->addLateLoadScriptFile('build/gallery.js');
    }

    #[\Override]
    protected function breadcrumbs() {
        $words = $this->words;
        return '<h3>'.$words->get('Gallery').' &raquo; <a href="gallery/show/user/'.$this->member->Username.'">'.ucfirst((string) $this->member->Username).'</a> &raquo; <a href="gallery/show/user/'.$this->member->Username.'/sets">'.$words->get("Photosets").'</a></h3>';
    }

    #[\Override]
    protected function teaserHeadline() {
        $words = $this->words;
        return '<h2><i class="fa fa-image mr-1"></i>'.htmlspecialchars((string) $this->gallery->title).'</h2>';
    }

    #[\Override]
    protected function teaser() {}

    #[\Override]
    protected function getSubmenuItems()
    {
        return [];
    }

    #[\Override]
    protected function getTopmenuActiveItem()
    {
        return 'gallery';
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
        $num_rows = $this->num_rows ?: 0;
        require SCRIPT_BASE . 'build/gallery/templates/galleryset.column_col3.php';
    }

    public function leftSidebar() {
    }

    #[\Override]
    protected function getColumnNames()
    {
        // we don't need the other columns
        return ['col3'];
    }
}

?>
