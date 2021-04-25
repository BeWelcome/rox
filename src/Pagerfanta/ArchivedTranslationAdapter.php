<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\ParameterType;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;
use Traversable;

class ArchivedTranslationAdapter implements AdapterInterface
{
    private Connection $connection;

    /**
     * SearchAdapter constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $statement = $this->connection->prepare("
            SELECT
                count(w.id) AS cnt
            FROM
                words w
            WHERE
                w.shortcode = 'en'
                AND (w.isArchived = 1)
        ");
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_OBJ);

        return $result->cnt;
    }

    /**
     * Returns an slice of the results.
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $statement = $this->connection->prepare("
            SELECT
                code,
                domain,
                shortcode,
                sentence,
                created
            FROM
                words w
            WHERE
                w.shortcode = 'en'
                AND (w.isArchived = 1)
            ORDER BY w.updated DESC
            LIMIT :offset, :limit
        ");
        $statement->bindValue('limit', $length, ParameterType::INTEGER);
        $statement->bindValue('offset', $offset, ParameterType::INTEGER);
        $statement->execute();

        return $statement->fetchAll();
    }
}
