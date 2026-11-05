<?php

namespace App\Form\CustomDataClass;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A new version of a wiki page: its markup and the version the edit started from (0 for a new page).
 */
class WikiPageRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'wiki.content.empty')]
        public ?string $content = '',
        public int $version = 0,
    ) {
    }
}
