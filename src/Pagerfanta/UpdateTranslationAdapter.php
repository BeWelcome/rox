<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class UpdateTranslationAdapter implements AdapterInterface
{
    /**
     * SearchAdapter constructor.
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __construct(private readonly Connection $connection, private readonly string $locale)
    {
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $statement = $this->connection->prepare("
            SELECT
                count(w2.id) AS cnt
            FROM
                words w1,
                words w2
            WHERE
                w1.shortCode = 'en'
                AND w2.shortCode = :locale
                AND (w1.isarchived = 0
                OR w1.isArchived IS NULL)
                AND (w1.donottranslate = 'No')
                AND w1.code = w2.code
                AND (w1.majorUpdate > w2.updated
                OR w1.updated > w2.updated)
            ORDER BY w1.updated DESC;");
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
                w2.code,
                w2.domain,
                w2.shortCode,
                w2.sentence,
                w2.created,
                IF(w1.majorUpdate > w2.updated, 1, 0) as majorUpdate
            FROM
                words w1,
                words w2
            WHERE
                w1.shortCode = 'en'
                AND w2.shortCode = :locale
                AND (w1.isarchived = 0
                OR w1.isArchived IS NULL)
                AND (w1.donottranslate = 'No')
                AND w1.code = w2.code
                AND (w1.majorUpdate > w2.updated
                OR w1.updated > w2.updated)
            ORDER BY majorUpdate DESC, w1.updated DESC
            LIMIT :offset, :limit
        ");
        $statement->bindValue('locale', $this->locale, ParameterType::STRING);
        $statement->bindValue('limit', $length, ParameterType::INTEGER);
        $statement->bindValue('offset', $offset, ParameterType::INTEGER);
        $result = $statement->executeQuery();

        return $result->fetchAllAssociative();
    }
}
