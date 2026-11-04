<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * What the category suggestions of the group forms look at: the name and description typed so far, and the
 * categories already picked (not suggested again).
 */
final readonly class GroupCategorySuggestion
{
    /**
     * @param list<int> $selected
     */
    public function __construct(
        #[Assert\Length(max: 20000)]
        public string $text = '',
        #[Assert\All([new Assert\Type('integer')])]
        public array $selected = [],
    ) {
    }
}
