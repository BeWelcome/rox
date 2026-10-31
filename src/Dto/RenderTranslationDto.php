<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Translation mode: a text the page shows, to be rendered again once it was saved in the side panel, once for each
 * set of parameters it was shown with.
 */
final readonly class RenderTranslationDto
{
    /**
     * @param list<array<string, scalar|null>> $parameterSets
     */
    public function __construct(
        #[Assert\NotBlank]
        public string $code,
        #[Assert\NotBlank]
        public string $locale,
        #[Assert\All([new Assert\Type('array')])]
        #[Assert\Count(max: 50)]
        public array $parameterSets = [[]],
    ) {
    }
}
