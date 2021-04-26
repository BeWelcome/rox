<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Driver\Connection;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class MissingTranslationAdapter implements AdapterInterface
{
    /** @var string */
    private $query;

    /** @var string */
    private $code;

    /** @var string */
    private $locale;

    /** @var Connection */
    private $connection;

    /**
     * SearchAdapter constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(Connection $connection, string $locale, string $code)
    {
        $this->connection = $connection;
        $this->code = $code;
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
        if (!empty($this->code)) {
            $this->query .= " AND code LIKE '%" . $this->code . "%'";
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
        if (!empty($this->code)) {
            $query .= " AND code LIKE '%" . $this->code . "%'";
        }
        $statement = $this->connection->query($query);
        $result = $statement->fetch(PDO::FETCH_OBJ);

        return $result->cnt;
    }

    /**
     * Returns an slice of the results.
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $query = $this->query . ' LIMIT ' . $offset . ', ' . $length;
        $statement = $this->connection->query($query);

        return $statement->fetchAll();
    }
}
