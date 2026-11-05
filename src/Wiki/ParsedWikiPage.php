<?php

namespace App\Wiki;

/**
 * A wiki page's markup as HTML (still to be purified), with what the page around it shows: the table of contents,
 * the categories, the links and whether the visual editor can edit it.
 */
final readonly class ParsedWikiPage
{
    /**
     * @param list<array{level: int, text: string, id: string}> $headings   the h2 and h3 for "On this page"
     * @param list<string>                                      $categories
     * @param list<WikiLink>                                    $links      each target once, in order
     */
    public function __construct(
        public string $html,
        public array $headings,
        public array $categories,
        public array $links,
        public bool $visualEditable,
    ) {
    }
}
