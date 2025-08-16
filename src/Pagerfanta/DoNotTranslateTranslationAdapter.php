<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Pagerfanta\Adapter\AdapterInterface;

class DoNotTranslateTranslationAdapter implements AdapterInterface
{
    private Connection $connection;

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
                w.shortCode = 'en'
                AND (w.donottranslate = 'Yes')
        ");
        $result = $statement->executeQuery();
        $count = $result->fetchOne();

        return $count;
    }

    /**
     * Returns a slice of the results.
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
                AND (w.donottranslate = 'Yes')
            ORDER BY w.updated DESC
            LIMIT :offset, :limit
        ");
        $statement->bindValue('limit', $length, ParameterType::INTEGER);
        $statement->bindValue('offset', $offset, ParameterType::INTEGER);
        $result = $statement->executeQuery();

        return $result->fetchAllAssociative();
    }
}
