<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Entity\Wiki;
use App\Repository\WikiRepository;
use App\Wiki\ParsedWikiPage;
use App\Wiki\WikiConflictException;
use App\Wiki\WikiDiff;
use App\Wiki\WikiPageName;
use App\Wiki\WikiParser;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Pages of the wiki and their versions: reading, saving a new version, the history and the changes of a version.
 * Every save adds a version; nothing is overwritten.
 */
class WikiModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WikiParser $parser,
    ) {
    }

    public function getPagename(string $pageTitle): string
    {
        return WikiPageName::fromTitle($pageTitle);
    }

    /**
     * @param int $version 0 for the newest
     */
    public function getPage(string $pageName, int $version = 0): ?Wiki
    {
        return $this->repository()->getPageByName($pageName, $version);
    }

    public function parse(string $markup): ParsedWikiPage
    {
        return $this->parser->parse($markup);
    }

    /**
     * Adds a version to the page (the first one for a new page).
     *
     * @param int $baseVersion the version the edit started from, 0 for a new page
     *
     * @throws WikiConflictException when someone saved a newer version in the meantime
     */
    public function save(string $pageName, string $content, string $author, int $baseVersion): int
    {
        $latest = $this->repository()->getLatestVersion($pageName);
        if ($latest !== $baseVersion) {
            throw new WikiConflictException($latest);
        }

        $now = time();
        try {
            $this->entityManager->getConnection()->insert('ewiki', [
                'pagename' => $pageName,
                'version' => $latest + 1,
                'content' => $content,
                'author' => $author,
                'created' => $now,
                'lastmodified' => $now,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw new WikiConflictException($this->repository()->getLatestVersion($pageName));
        }

        return $latest + 1;
    }

    public function createWikiPage(string $pageTitle, string $wikiMarkup, string $author = 'ewiki'): Wiki
    {
        $pageName = $this->getPagename($pageTitle);
        $this->save($pageName, $wikiMarkup, $author, 0);

        return $this->getPage($pageName);
    }

    /**
     * The versions of a page, newest first, with how many characters each added or removed.
     *
     * @return list<array{version: int, author: string, created: DateTimeImmutable, size: int, delta: ?int}>
     */
    public function getHistory(string $pageName): array
    {
        $versions = $this->repository()->getHistory($pageName);
        $history = [];
        foreach ($versions as $index => $version) {
            $older = $versions[$index + 1] ?? null;
            $history[] = [
                'version' => $version['version'],
                'author' => $version['author'],
                'created' => new DateTimeImmutable('@' . $version['created']),
                'size' => $version['size'],
                'delta' => null === $older ? null : $version['size'] - $older['size'],
            ];
        }

        return $history;
    }

    public function countVersions(string $pageName): int
    {
        return $this->repository()->getLatestVersion($pageName);
    }

    /**
     * What a version changed from the one before.
     *
     * @return array{old: Wiki, new: Wiki, diff: WikiDiff}|null null for the first version or one that doesn't exist
     */
    public function getChanges(string $pageName, int $version): ?array
    {
        $new = $version > 1 ? $this->getPage($pageName, $version) : null;
        $old = null === $new ? null : $this->getPage($pageName, $version - 1);
        if (null === $new || null === $old) {
            return null;
        }

        return ['old' => $old, 'new' => $new, 'diff' => WikiDiff::between($old->getContent(), $new->getContent())];
    }

    /**
     * Which authors are members (old versions have authors who left, or the old wiki's "ewiki"): only they get an
     * avatar and a profile link.
     *
     * @param list<string> $usernames
     *
     * @return array<string, true>
     */
    public function getMembers(array $usernames): array
    {
        $usernames = array_values(array_unique($usernames));
        if ([] === $usernames) {
            return [];
        }

        $found = $this->entityManager->createQueryBuilder()
            ->select('m.username')
            ->from(Member::class, 'm')
            ->where('m.username IN (:names)')
            ->andWhere('m.status IN (:statuses)')
            ->setParameter('names', $usernames)
            ->setParameter('statuses', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->getQuery()
            ->getSingleColumnResult();

        return array_fill_keys($found, true);
    }

    private function repository(): WikiRepository
    {
        /** @var WikiRepository $repository */
        $repository = $this->entityManager->getRepository(Wiki::class);

        return $repository;
    }
}
