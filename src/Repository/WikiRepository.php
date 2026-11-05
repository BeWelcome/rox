<?php

namespace App\Repository;

use App\Entity\Wiki;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * The wiki's versions (ewiki): one row per page and version. "Latest" is the newest version of each page.
 *
 * @extends EntityRepository<Wiki>
 */
class WikiRepository extends EntityRepository
{
    /** the newest version of each page */
    private const string LATEST = 'ewiki w JOIN (SELECT pagename, MAX(version) AS version FROM ewiki GROUP BY pagename) l
        ON l.pagename = w.pagename AND l.version = w.version';

    /** pages of the main wiki: not the group wikis */
    private const string MAIN = "w.pagename NOT LIKE 'Group\\_%'";

    /**
     * @param int $version 0 for the newest
     */
    public function getPageByName(string $pagename, int $version = 0): ?Wiki
    {
        $qb = $this->createQueryBuilder('w')
            ->where('w.pagename = :pagename')
            ->setParameter('pagename', $pagename)
            ->orderBy('w.version', 'DESC')
            ->setMaxResults(1);
        if (0 !== $version) {
            $qb->andWhere('w.version = :version')->setParameter('version', $version);
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function getLatestVersion(string $pagename): int
    {
        return (int) $this->connection()->fetchOne('SELECT MAX(version) FROM ewiki WHERE pagename = ?', [$pagename]);
    }

    /**
     * @return list<array{version: int, author: string, created: int, size: int}> newest first
     */
    public function getHistory(string $pagename): array
    {
        return array_map(
            static fn (array $row) => [
                'version' => (int) $row['version'],
                'author' => (string) $row['author'],
                'created' => (int) $row['created'],
                'size' => (int) $row['size'],
            ],
            $this->connection()->fetchAllAssociative(
                'SELECT version, author, created, CHAR_LENGTH(content) AS size FROM ewiki WHERE pagename = ? ORDER BY version DESC',
                [$pagename],
            ),
        );
    }

    /**
     * The latest edits of all pages, group wikis included.
     *
     * @return list<array{pagename: string, version: int, author: string, created: int}>
     */
    public function getRecentChanges(int $limit = 300): array
    {
        return $this->edits(
            'SELECT pagename, version, author, created FROM ewiki ORDER BY created DESC, version DESC LIMIT ' . $limit,
        );
    }

    /**
     * The pages of the main wiki changed last, each once.
     *
     * @return list<array{pagename: string, version: int, author: string, created: int}>
     */
    public function getRecentlyChangedPages(int $limit): array
    {
        return $this->edits(
            'SELECT w.pagename, w.version, w.author, w.created FROM ' . self::LATEST . ' WHERE ' . self::MAIN
            . ' ORDER BY w.created DESC LIMIT ' . $limit,
        );
    }

    /**
     * The newest versions of the main wiki's pages whose text has the given text (any case).
     *
     * @return list<array{pagename: string, content: string, author: string, created: int}>
     */
    public function findLatestContaining(string $text): array
    {
        return $this->connection()->fetchAllAssociative(
            'SELECT w.pagename, w.content, w.author, w.created FROM ' . self::LATEST . ' WHERE ' . self::MAIN
            . ' AND w.content LIKE ? ORDER BY w.pagename',
            ['%' . addcslashes($text, '%_\\') . '%'],
        );
    }

    /**
     * Pages of the main wiki with the text in their title or text; title matches first.
     *
     * @return list<array{pagename: string, content: string, title_match: int}>
     */
    public function search(string $text, int $limit): array
    {
        $like = '%' . addcslashes($text, '%_\\') . '%';

        return $this->connection()->fetchAllAssociative(
            "SELECT w.pagename, w.content, REPLACE(w.pagename, '_', ' ') LIKE :like AS title_match FROM " . self::LATEST
            . ' WHERE ' . self::MAIN . " AND (REPLACE(w.pagename, '_', ' ') LIKE :like OR w.content LIKE :like)"
            . ' ORDER BY title_match DESC, CHAR_LENGTH(w.pagename), w.pagename LIMIT ' . $limit,
            ['like' => $like],
        );
    }

    /**
     * @return list<array{pagename: string, version: int, author: string, created: int}>
     */
    private function edits(string $sql): array
    {
        return array_map(
            static fn (array $row) => [
                'pagename' => (string) $row['pagename'],
                'version' => (int) $row['version'],
                'author' => (string) $row['author'],
                'created' => (int) $row['created'],
            ],
            $this->connection()->fetchAllAssociative($sql),
        );
    }

    private function connection(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }
}
