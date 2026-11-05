<?php

namespace App\Wiki;

/**
 * Finds where the internal links of a page lead, all at once.
 */
interface WikiLinkResolverInterface
{
    /**
     * @param list<string> $titles link targets as written ([[Hosting tips]], [[Group Lisbon]])
     *
     * @return array<string, WikiLink> by title as written; titles that can't be a page (a slash) have none
     */
    public function resolve(array $titles): array;
}
