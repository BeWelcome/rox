<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;
use Traversable;

class DoNotTranslateTranslationAdapter implements AdapterInterface
{
    /** @var Statement */
    private $statement;

    /** @var string */
    private $locale;

    /** @var Connection */
    private $connection;

    /**
     * SearchAdapter constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(Connection $connection, string $locale)
    {
        $this->connection = $connection;
        $this->locale = $locale;
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults()
    {
        $statement = $this->connection->prepare("
            SELECT
                count(w.id) AS cnt
            FROM
                words w
            WHERE
                w.shortcode = 'en'
                AND (w.donottranslate = 'Yes')
        ");
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_OBJ);

        return $result->cnt;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return array|Traversable the slice
     */
    public function getSlice($offset, $length)
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
                AND (w.donottranslate = 'Yes')
            ORDER BY w.updated DESC
            LIMIT :offset, :limit
        ");
        $statement->bindValue('limit', $length, ParameterType::INTEGER);
        $statement->bindValue('offset', $offset, ParameterType::INTEGER);
        $statement->execute();

        return $statement->fetchAll();
    }
}
