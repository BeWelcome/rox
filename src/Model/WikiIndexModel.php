<?php

namespace App\Model;

use App\Entity\Wiki;
use App\Repository\WikiRepository;
use App\Wiki\WikiLinkResolver;
use App\Wiki\WikiPageName;
use App\Wiki\WikiParser;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Views across the wiki's pages: categories, related pages, the search and the recent changes. Categories, related
 * pages and the search cover the main wiki only; group wikis are reached from their group.
 */
class WikiIndexModel
{
    private const string CATEGORY_MARK = '[[Category:';
    private const int EXCERPT_LENGTH = 140;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WikiLinkResolver $linkResolver,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<string, int> the number of pages by category, by name
     */
    public function getCategories(): array
    {
        $counts = [];
        foreach ($this->categorisedPages() as $page) {
            foreach ($page['categories'] as $category) {
                $counts[$category] = ($counts[$category] ?? 0) + 1;
            }
        }
        uksort($counts, 'strnatcasecmp');

        return $counts;
    }

    /**
     * @return list<array{pagename: string, title: string, author: string, created: DateTimeImmutable, excerpt: string}>
     */
    public function getPagesInCategory(string $category): array
    {
        $pages = [];
        foreach ($this->categorisedPages() as $page) {
            if ($this->hasCategory($page['categories'], $category)) {
                $pages[] = [
                    'pagename' => $page['pagename'],
                    'title' => WikiPageName::toTitle($page['pagename']),
                    'author' => $page['author'],
                    'created' => new DateTimeImmutable('@' . $page['created']),
                    'excerpt' => $this->excerpt($page['content']),
                ];
            }
        }

        return $pages;
    }

    /**
     * Pages that share a category with the page, then pages that link to it; each once.
     *
     * @param list<string> $categories the page's
     *
     * @return list<array{pagename: string, title: string, category: ?string}> category null: it links to the page
     */
    public function getRelatedPages(string $pageName, array $categories, int $limit = 4): array
    {
        $related = [];
        if ([] !== $categories) {
            foreach ($this->categorisedPages() as $page) {
                $shared = array_values(array_filter($categories, fn (string $category) => $this->hasCategory($page['categories'], $category)));
                if ([] !== $shared && 0 !== strcasecmp($page['pagename'], $pageName)) {
                    $related[$page['pagename']] ??= ['pagename' => $page['pagename'], 'title' => WikiPageName::toTitle($page['pagename']), 'category' => $shared[0]];
                }
            }
        }

        $title = WikiPageName::toTitle($pageName);
        foreach (array_unique([$title, $pageName]) as $written) {
            foreach ($this->repository()->findLatestContaining('[[' . $written) as $page) {
                if (\count($related) >= $limit) {
                    break 2;
                }
                if (0 !== strcasecmp($page['pagename'], $pageName) && $this->linksTo($page['content'], $pageName)) {
                    $related[$page['pagename']] ??= ['pagename' => $page['pagename'], 'title' => WikiPageName::toTitle($page['pagename']), 'category' => null];
                }
            }
        }

        return \array_slice(array_values($related), 0, $limit);
    }

    /**
     * Pages of the main wiki with the text in their title (first) or text.
     *
     * @return array{results: list<array{pagename: string, title: string, url: string, snippet: string}>, exact: bool}
     */
    public function search(string $text, int $limit = 6): array
    {
        $text = trim($text);
        if ('' === $text) {
            return ['results' => [], 'exact' => false];
        }

        $exact = false;
        $results = [];
        foreach ($this->repository()->search($text, $limit) as $page) {
            $title = WikiPageName::toTitle($page['pagename']);
            $exact = $exact || 0 === strcasecmp($title, $text) || 0 === strcasecmp($page['pagename'], $text);
            $results[] = [
                'pagename' => $page['pagename'],
                'title' => WikiPageName::FRONT_PAGE === $page['pagename'] ? '' : $title,
                'url' => $this->urlGenerator->generate('wiki_page', ['pageTitle' => $page['pagename']]),
                'snippet' => $this->snippet((string) $page['content'], $text, (bool) $page['title_match']),
            ];
        }

        return ['results' => $results, 'exact' => $exact];
    }

    /**
     * The latest edits, group wikis included (they lead to the group's Wiki tab).
     *
     * @return list<array{pagename: string, title: string, group: ?string, url: string, changes: ?string, version: int, author: string, created: DateTimeImmutable}>
     */
    public function getRecentChanges(int $limit = 300): array
    {
        return $this->withLinks($this->repository()->getRecentChanges($limit));
    }

    /**
     * The main wiki's pages changed last, each once.
     *
     * @return list<array{pagename: string, title: string, group: ?string, url: string, changes: ?string, version: int, author: string, created: DateTimeImmutable}>
     */
    public function getRecentlyChanged(int $limit = 5): array
    {
        return $this->withLinks($this->repository()->getRecentlyChangedPages($limit));
    }

    /**
     * @param list<array{pagename: string, version: int, author: string, created: int}> $edits
     *
     * @return list<array{pagename: string, title: string, group: ?string, url: string, changes: ?string, version: int, author: string, created: DateTimeImmutable}>
     */
    private function withLinks(array $edits): array
    {
        $groups = $this->linkResolver->groupsByPageName(array_values(array_unique(array_column($edits, 'pagename'))));

        $rows = [];
        foreach ($edits as $edit) {
            $group = $groups[$edit['pagename']] ?? null;
            if (null === $group) {
                $params = ['pageTitle' => $edit['pagename']];
                $url = $this->urlGenerator->generate('wiki_page', $params);
                $changes = $edit['version'] > 1 ? $this->urlGenerator->generate('wiki_page_changes', $params + ['version' => $edit['version']]) : null;
            } else {
                $params = ['id' => $group['id']];
                $url = $this->urlGenerator->generate('group_wiki_page', $params);
                $changes = $edit['version'] > 1 ? $this->urlGenerator->generate('group_wiki_changes', $params + ['version' => $edit['version']]) : null;
            }
            $rows[] = [
                'pagename' => $edit['pagename'],
                'title' => null === $group ? WikiPageName::toTitle($edit['pagename']) : $group['name'],
                'group' => $group['name'] ?? null,
                'url' => $url,
                'changes' => $changes,
                'version' => $edit['version'],
                'author' => $edit['author'],
                'created' => new DateTimeImmutable('@' . $edit['created']),
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{pagename: string, content: string, author: string, created: int, categories: list<string>}>
     */
    private function categorisedPages(): array
    {
        $pages = [];
        foreach ($this->repository()->findLatestContaining(self::CATEGORY_MARK) as $page) {
            $categories = WikiParser::categoriesOf((string) $page['content']);
            if ([] !== $categories) {
                $pages[] = $page + ['categories' => $categories];
            }
        }

        return $pages;
    }

    /**
     * @param list<string> $categories
     */
    private function hasCategory(array $categories, string $category): bool
    {
        foreach ($categories as $name) {
            if (0 === strcasecmp($name, $category)) {
                return true;
            }
        }

        return false;
    }

    private function linksTo(string $content, string $pageName): bool
    {
        preg_match_all('~\[\[([^\[\]|]+)~', $content, $matches);
        foreach ($matches[1] as $target) {
            if (0 === strcasecmp(WikiPageName::fromTitle($target), $pageName)) {
                return true;
            }
        }

        return false;
    }

    private function excerpt(string $content): string
    {
        $text = WikiParser::plainText($content);

        return mb_strlen($text) > self::EXCERPT_LENGTH ? rtrim(mb_substr($text, 0, self::EXCERPT_LENGTH)) . '…' : $text;
    }

    /** a line of the page around the text searched for; the start of the page for a title match */
    private function snippet(string $content, string $text, bool $titleMatch): string
    {
        $plain = WikiParser::plainText($content);
        $position = $titleMatch ? false : mb_stripos($plain, $text);
        if (false === $position || $position < 40) {
            return $this->cut($plain, 0);
        }

        return '…' . $this->cut($plain, $position - 40);
    }

    private function cut(string $text, int $from): string
    {
        $part = mb_substr($text, $from, 120);

        return mb_strlen($text) > $from + 120 ? rtrim($part) . '…' : $part;
    }

    private function repository(): WikiRepository
    {
        /** @var WikiRepository $repository */
        $repository = $this->entityManager->getRepository(Wiki::class);

        return $repository;
    }
}
