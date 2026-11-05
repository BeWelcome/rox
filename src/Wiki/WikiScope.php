<?php

namespace App\Wiki;

use App\Entity\Group;

/**
 * Where a wiki page lives: the main wiki (/wiki/Page) or a group's Wiki tab (/group/1/wiki). The pages of both
 * share their templates; the routes and their parameters tell them apart.
 */
final readonly class WikiScope
{
    private const array MAIN_ROUTES = [
        'read' => 'wiki_page',
        'version' => 'wiki_page',
        'edit' => 'wiki_page_edit',
        'create' => 'wiki_page_create',
        'history' => 'wiki_page_history',
        'changes' => 'wiki_page_changes',
    ];

    private const array GROUP_ROUTES = [
        'read' => 'group_wiki_page',
        'version' => 'group_wiki_page_version',
        'edit' => 'group_wiki_page_edit',
        'create' => 'group_wiki_page_create',
        'history' => 'group_wiki_history',
        'changes' => 'group_wiki_changes',
    ];

    /**
     * @param array<string, string>     $routes by action
     * @param array<string, int|string> $params of every route
     */
    private function __construct(
        public string $pageName,
        public array $routes,
        public array $params,
        public bool $canEdit,
        public ?Group $group = null,
    ) {
    }

    public static function main(string $pageName): self
    {
        return new self($pageName, self::MAIN_ROUTES, ['pageTitle' => $pageName], true);
    }

    public static function group(Group $group, bool $canEdit): self
    {
        return new self(WikiPageName::forGroup($group->getName()), self::GROUP_ROUTES, ['id' => $group->getId()], $canEdit, $group);
    }

    public function isGroup(): bool
    {
        return null !== $this->group;
    }

    public function isFrontPage(): bool
    {
        return WikiPageName::FRONT_PAGE === $this->pageName;
    }

    /** the page's title: Hosting tips; a group page has the group's name */
    public function getTitle(): string
    {
        return $this->group?->getName() ?? WikiPageName::toTitle($this->pageName);
    }
}
