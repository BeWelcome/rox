<?php

namespace App\Twig;

class MockupExtension
{
    public function image($image)
    {
        // This is only used for the mockups
        // so we replace inline images with the logo
        return '/images/logo_index_top.png';
    }
}
