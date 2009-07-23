<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GalleryPage extends PageWithActiveSkin
{

    protected function teaserHeadline() {
        return $this->getWords()->getBuffered('Gallery');
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
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_3col_75percent.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/lightview.css';
        return $stylesheets;
    }

    protected function getSubmenuItems()
    {

        $words = $this->getWords();
        $items = array();
        $items[] = array('overview', 'gallery', $words->get('GalleryAllPhotos'));
        if (APP_User::IsBWLoggedIn("NeedMore,Pending"))
            $items[] = array('user', 'gallery/show/user/'.APP_User::get()->getHandle(), $words->get('GalleryMy'));
        if (APP_User::IsBWLoggedIn("NeedMore,Pending"))
            $items[] = array('upload', 'gallery/upload', $words->get('GalleryUpload'));
        $items[] = array('flickr', 'gallery/flickr', $words->get('GalleryFlickr'));
        return $items; 
    }
    
    /*
    *  Custom functions
    *
    */
    


}

?>
