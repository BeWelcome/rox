<?php

namespace App\Wiki;

/**
 * Where a link of a wiki page leads: a page of the main wiki, a group's wiki, a category listing or another site.
 */
final readonly class WikiLink
{
    public const string PAGE = 'page';
    public const string GROUP = 'group';
    public const string CATEGORY = 'category';
    public const string EXTERNAL = 'external';

    public function __construct(
        public string $kind,
        /** the page name (Hosting_tips, Group_Lisbon), the category or the address */
        public string $target,
        public string $url,
        /** false for a page or group wiki without a version yet */
        public bool $exists = true,
        /** what the link reads; the target when the markup doesn't say */
        public string $label = '',
    ) {
    }

    public function withLabel(string $label): self
    {
        return new self($this->kind, $this->target, $this->url, $this->exists, $label);
    }

    public function isExternal(): bool
    {
        return self::EXTERNAL === $this->kind;
    }

    /** the host of an external link, for "External · domain" */
    public function getDomain(): string
    {
        return (string) parse_url($this->url, \PHP_URL_HOST);
    }
}
