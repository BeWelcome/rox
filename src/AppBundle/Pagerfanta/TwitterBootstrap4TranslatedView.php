<?php

namespace AppBundle\Pagerfanta;

use WhiteOctober\PagerfantaBundle\View\TwitterBootstrap3TranslatedView;

/**
* TwitterBootstrap4View.
*
* View that can be used with the pagination module
* from the Twitter Bootstrap4 CSS Toolkit
* http://getbootstrap.com/
*
*/
class TwitterBootstrap4TranslatedView extends TwitterBootstrap3TranslatedView
{
    protected function createDefaultTemplate()
    {
        return new TwitterBootstrap4Template();
    }

    /**
    * {@inheritdoc}
    */
    public function getName()
    {
        return 'twitter_bootstrap4_translated';
    }
}
