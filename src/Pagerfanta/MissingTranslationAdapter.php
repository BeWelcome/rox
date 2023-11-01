<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Driver\Connection;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class MissingTranslationAdapter implements AdapterInterface
{
    private string $query;

    private string $term;

    private string $locale;

    private Connection $connection;

    public function __construct(Connection $connection, string $locale, string $term)
    {
        $this->connection = $connection;
        $this->term = empty($term) ? $term : $connection->quote('%' . $term . '%');
        $this->locale = $locale;

        $this->query = "
            SELECT
                code,
                domain,
                shortcode,
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
        $statement = $this->connection->query($query);
        $result = $statement->fetch(PDO::FETCH_OBJ);

        return $result->cnt;
    }

    /**
     * Returns a slice of the results.
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $query = $this->query . ' LIMIT ' . $offset . ', ' . $length;
        $statement = $this->connection->query($query);

        return $statement->fetchAll();
    }
}
