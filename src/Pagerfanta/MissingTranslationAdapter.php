<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Connection;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class MissingTranslationAdapter implements AdapterInterface
{
    private string $query;

    private readonly string $term;

    public function __construct(private readonly Connection $connection, private readonly string $locale, string $term)
    {
        $this->term = empty($term) ? $term : $this->connection->quote('%' . $term . '%');

        $this->query = "
            SELECT
                code,
                domain,
                shortCode,
                sentence,
                created
            FROM
                words
            WHERE
                shortCode = 'en'
                AND (isArchived IS NULL OR isArchived = 0)
                AND (donottranslate = 'No')
                AND code NOT IN (SELECT code FROM words WHERE shortCode = '{$this->locale}')";
        if (!empty($this->term)) {
            $this->query .= " AND code LIKE {$this->term}";
        }
        $this->query .= '
            ORDER BY created desc';
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $query = "
            SELECT
                count(*) as cnt
            FROM
                words
            WHERE
                shortCode = 'en'
                AND (isArchived IS NULL OR isArchived = 0)
                AND (donottranslate = 'No')
                AND code NOT IN (SELECT code FROM words WHERE shortCode = '{$this->locale}')";
        if (!empty($this->term)) {
            $query .= " AND code LIKE {$this->term}";
        }
        $statement = $this->connection->executeQuery($query);
        $count = $statement->fetchOne();

        return $count;
    }

    /**
     * Returns a slice of the results.
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $query = $this->query . ' LIMIT ' . $offset . ', ' . $length;
        $statement = $this->connection->executeQuery($query);

        return $statement->fetchAllAssociative();
    }
}
