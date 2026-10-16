<?php


//------------------------------------------------------------------------------------
/**
 * AvatarsPage shows all avatar pictures of a member
 *
 */


class GalleryAvatarsPage extends GalleryBasePage
{

    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    #[\Override]
    protected function teaserHeadline() {
        return '<a href="gallery">'.parent::teaserHeadline() . '</a> &raquo; '. $this->getWords()->getBuffered('GalleryAvatars').'</a>';
    }
}

