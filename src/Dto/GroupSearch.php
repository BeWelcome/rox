<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * The filters and order of the group search (/groups/search), from the query string.
 */
readonly class GroupSearch
{
    public const array ORDERS = [
        'nameasc', 'namedesc', 'membersasc', 'membersdesc', 'actasc', 'actdesc', 'createdasc', 'createddesc',
    ];

    public function __construct(
        /** Words of the group's name; a group matches if its name contains any of them. */
        #[Assert\Length(max: 100)]
        public ?string $name = null,
        public ?int $category = null,
        /** A geonames id: groups there or anywhere below it (a country finds the groups in its regions and places). */
        public ?int $location = null,
        /** The location's name as picked, to show it again. */
        #[Assert\Length(max: 200)]
        public ?string $locationName = null,
        #[Assert\Choice(choices: self::ORDERS)]
        public string $order = 'nameasc',
        #[Assert\Positive]
        public int $page = 1,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getNameParts(): array
    {
        return array_values(array_filter(preg_split('/\s+/', trim((string) $this->name)) ?: [], static fn ($part) => '' !== $part));
    }

    /**
     * The query string for this search with another order or page.
     *
     * @return array<string, string|int>
     */
    public function toQuery(?string $order = null, ?int $page = null): array
    {
        return array_filter([
            'name' => $this->name,
            'category' => $this->category,
            'location' => $this->location,
            'locationName' => $this->locationName,
            'order' => $order ?? $this->order,
            'page' => $page ?? $this->page,
        ], static fn ($value) => null !== $value && '' !== $value);
    }
}
