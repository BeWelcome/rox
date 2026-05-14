<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GalleryManagePage extends GalleryUserPage
{
    #[\Override]
    protected function init()
    {
        parent::init();
        $this->addLateLoadScriptFile('build/gallery.js');
    }

    #[\Override]
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        return $stylesheets;
    }

    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'manage';
    }

    #[\Override]
    protected function getSubmenuItems()
    {
        return [];
    }

    #[\Override]
    protected function teaser()
    {
    }

    #[\Override]
    public function leftSidebar()
    {
        $galleries = $this->galleries;
        $cnt_pictures = $this->cnt_pictures;
        $username = $this->loggedInMember ? $this->loggedInMember->Username : '';
        // require SCRIPT_BASE . 'build/gallery/templates/userinfo.php';
    }

    #[\Override]
    protected function column_col3() {
        $statement = $this->statement;
        $words = $this->getWords();
        $member = $this->loggedInMember;
        $galleries = $this->galleries;
$backToEditHref = ($this->loggedInMember && $this->loggedInMember->Username)
            ? '/members/' . rawurlencode((string) $this->loggedInMember->Username) . '/edit'
            : '#';
        $mem_redirect = $this->layoutkit->formkit->getMemFromRedirect();
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        $formkit = $this->layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('GalleryController', 'manageCallback');

        $itemsPerPage = 12;
        require SCRIPT_BASE . 'build/gallery/templates/gallerymanage.column_col3.php';
    }

}
