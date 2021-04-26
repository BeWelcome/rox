<?php

namespace App\Pagerfanta;

use Pagerfanta\View\Template\Template;
use Pagerfanta\View\Template\TwitterBootstrap4Template;
use Pagerfanta\View\TwitterBootstrapView;

/**
 * TwitterBootstrap4View.
 *
 * View that can be used with the pagination module
 * from the Twitter Bootstrap4 CSS Toolkit
 * http://getbootstrap.com/
 */
class TwitterBootstrap4TranslatedView extends TwitterBootstrapView
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'twitter_bootstrap4';
    }

    protected function createDefaultTemplate(): Template
    {
        return new TwitterBootstrap4Template();
    }
}
