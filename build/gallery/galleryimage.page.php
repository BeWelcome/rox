<?php


//------------------------------------------------------------------------------------
/**
 * GalleryImagePage shows a single image with the corresponding info
 *
 */


class GalleryImagePage extends GalleryPage
{

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        return $this->getWords()->getBuffered('Gallery');
    }

}

?>
