<?php

namespace App\Dto\Gallery;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pictures chosen on /gallery/manage, and for a move the album they go to (null: out of any album).
 */
final readonly class PictureSelection
{
    /**
     * @param list<int> $pictures
     */
    public function __construct(
        #[Assert\Count(min: 1, max: 500)]
        #[Assert\All([new Assert\Positive()])]
        public array $pictures = [],
        public ?int $album = null,
    ) {
    }
}
