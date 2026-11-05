<?php

namespace App\Wiki;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Resolves wiki links with two queries: which pages have a version, and which groups the group pages belong to.
 * A group's page leads to the group's Wiki tab; a page without a version to its create form.
 */
#[AsAlias(WikiLinkResolverInterface::class)]
final readonly class WikiLinkResolver implements WikiLinkResolverInterface
{
    public function __construct(
        private Connection $connection,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function resolve(array $titles): array
    {
        $pageNames = [];
        foreach ($titles as $title) {
            $pageName = WikiPageName::fromTitle(ltrim($title, ':'));
            // no page name can have a slash (the routes can't take it): such a link stays text
            if ('' !== $pageName && !str_contains($pageName, '/')) {
                $pageNames[$title] = $pageName;
            }
        }
        if ([] === $pageNames) {
            return [];
        }

        $existing = $this->existingPages(array_values($pageNames));
        $groups = $this->groupsByPageName(array_values($pageNames));

        $links = [];
        foreach ($pageNames as $title => $pageName) {
            $exists = isset($existing[strtolower($pageName)]);
            $group = $groups[$pageName] ?? null;
            $links[$title] = null !== $group
                ? new WikiLink(WikiLink::GROUP, $pageName, $this->urlGenerator->generate('group_wiki_page', ['id' => $group['id']]), $exists)
                : new WikiLink(
                    WikiLink::PAGE,
                    $pageName,
                    $this->urlGenerator->generate($exists ? 'wiki_page' : 'wiki_page_create', ['pageTitle' => $pageName]),
                    $exists,
                );
        }

        return $links;
    }

    /**
     * The groups of group pages (Group_Lisbon); the agora has no wiki.
     *
     * @param list<string> $pageNames
     *
     * @return array<string, array{id: int, name: string}> by page name
     */
    public function groupsByPageName(array $pageNames): array
    {
        $keys = [];
        foreach ($pageNames as $pageName) {
            if (WikiPageName::isGroupPage($pageName)) {
                $keys[strtolower(WikiPageName::groupKey($pageName))][] = $pageName;
            }
        }
        if ([] === $keys) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT id, name, LOWER(REPLACE(name, ' ', '')) AS page_key FROM `group` WHERE id <> 0 AND LOWER(REPLACE(name, ' ', '')) IN (:keys)",
            ['keys' => array_keys($keys)],
            ['keys' => ArrayParameterType::STRING],
        );

        $groups = [];
        foreach ($rows as $row) {
            foreach ($keys[$row['page_key']] ?? [] as $pageName) {
                $groups[$pageName] ??= ['id' => (int) $row['id'], 'name' => (string) $row['name']];
            }
        }

        return $groups;
    }

    /**
     * @param list<string> $pageNames
     *
     * @return array<string, true> by lower-cased page name
     */
    private function existingPages(array $pageNames): array
    {
        $existing = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT pagename FROM ewiki WHERE pagename IN (:names)',
            ['names' => $pageNames],
            ['names' => ArrayParameterType::STRING],
        );

        return array_fill_keys(array_map('strtolower', $existing), true);
    }
}
