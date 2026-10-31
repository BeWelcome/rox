<?php

namespace App\Dto\Gallery;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * The name of a new or renamed album on /gallery/manage.
 */
final readonly class AlbumTitle
{
    public function __construct(
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Length(max: 60)]
        public string $title = '',
    ) {
    }
}
