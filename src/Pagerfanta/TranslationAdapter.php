<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Driver\Connection;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class TranslationAdapter implements AdapterInterface
{
    private string $query;

    private string $countQuery;

    private Connection $connection;

    /**
     * SearchAdapter constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(Connection $connection, string $locale, string $term)
    {
        $this->connection = $connection;

        if (!empty($term)) {
            $term = $connection->quote('%' . $term . '%');
        }

        $rawQuery = "
            SELECT *select*
              FROM words AS p
            LEFT OUTER
              JOIN words AS pi_dflt
                ON pi_dflt.code = p.code
                AND pi_dflt.shortcode = 'en'
                AND (pi_dflt.isArchived IS NULL OR pi_dflt.isArchived = 0)
                AND (pi_dflt.donottranslate = 'No')
            LEFT OUTER
              JOIN words AS pi_lang
                ON pi_lang.code = p.code
                AND pi_lang.shortcode = '{$locale}'
                AND (pi_lang.isArchived IS NULL OR pi_lang.isArchived = 0)
                ";
        if (!empty($term)) {
            $rawQuery .= " WHERE (p.code LIKE {$term} OR p.Sentence LIKE {$term})";
        }

        $this->query = str_replace('*select*', 'distinct p.code
                 , COALESCE(pi_lang.shortcode,pi_dflt.shortcode) AS shortcode
                 , COALESCE(pi_lang.domain,pi_dflt.domain) AS domain
                 , COALESCE(pi_lang.Sentence,pi_dflt.Sentence) AS sentence
                 , COALESCE(pi_lang.created,pi_dflt.created) AS created', $rawQuery);

        $this->query .= "             ORDER BY created desc";
        $this->countQuery = str_replace('*select*', 'COUNT(distinct p.code) AS cnt', $rawQuery);
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $statement = $this->connection->query($this->countQuery);
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
