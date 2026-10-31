<?php

namespace App\Dto\Gallery;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * The title and text of a picture, and the album it is in (null: none), as the picture panel on /gallery/manage
 * saves them.
 */
final readonly class PictureDetails
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public string $title = '',
        #[Assert\Length(max: 5000)]
        public string $description = '',
        public ?int $album = null,
    ) {
    }
}
