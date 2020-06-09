<?php

namespace App\Twig;

class MockupExtension
{
    public function image($image)
    {
        // This is only used for the mockups
        // so we replace inline images with the logo
        if ('logo_index_top' !== $image) {
            $image = 'logo_index_top';
        }

        return '/images/' . $image . '.png';
    }
}
