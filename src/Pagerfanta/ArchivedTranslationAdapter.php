<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Pagerfanta\Adapter\AdapterInterface;

class ArchivedTranslationAdapter implements AdapterInterface
{
    /**
     * SearchAdapter constructor.
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __construct(private readonly Connection $connection)
    {
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
                w.shortCode = 'en'
                AND (w.isArchived = 1)
        ");
        $result = $statement->executeQuery();
        $count = $result->fetchOne();

        return $count;
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
                shortCode,
                sentence,
                created
            FROM
                words w
            WHERE
                w.shortCode = 'en'
                AND (w.isArchived = 1)
            ORDER BY w.updated DESC
            LIMIT :offset, :limit
        ");
        $statement->bindValue('limit', $length, ParameterType::INTEGER);
        $statement->bindValue('offset', $offset, ParameterType::INTEGER);
        $result = $statement->executeQuery();

        return $result->fetchAllAssociative();
    }
}
