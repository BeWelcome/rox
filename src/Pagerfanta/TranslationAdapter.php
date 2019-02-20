<?php

namespace App\Pagerfanta;

use Doctrine\DBAL\Driver\Connection;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class TranslationAdapter implements AdapterInterface
{
    /** @var string */
    private $query;

    /** @var string */
    private $code;

    /** @var Connection */
    private $connection;

    /**
     * SearchAdapter constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param Connection $connection
     * @param string $locale
     * @param string $code
     */
    public function __construct(Connection $connection, string $locale, string $code)
    {
        $this->connection = $connection;
        $this->code = $code;

        $this->query = "
            SELECT distinct p.code
                 , COALESCE(pi_lang.shortcode,pi_dflt.shortcode) AS shortcode
                 , COALESCE(pi_lang.Sentence,pi_dflt.Sentence) AS Sentence
                 , COALESCE(pi_lang.created,pi_dflt.created) AS created
              FROM words AS p
            LEFT OUTER 
              JOIN words AS pi_dflt 
                ON pi_dflt.code = p.code
                AND pi_dflt.shortcode = 'en'
            LEFT OUTER 
              JOIN words AS pi_lang 
                ON pi_lang.code = p.code
                AND pi_lang.shortcode = '{$locale}'";
        if (!empty($code)) {
            $this->query .= " WHERE (pi_lang.code LIKE '%" . $code . "%' OR pi_dflt.code LIKE '%" . $code . "%')";
        }
        $this->query .= "
            ORDER BY created desc";
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults()
    {
        $query = "SELECT count(*) as cnt FROM words WHERE shortcode = 'en'";
        if (!empty($this->code)) {
            $query .= " AND code LIKE '%" . $this->code . "%'";
        }

        $statement = $this->connection->query($query);
        $result = $statement->fetch(PDO::FETCH_OBJ);

        return $result->cnt;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return array|\Traversable the slice
     */
    public function getSlice($offset, $length)
    {
        $query = $this->query.' LIMIT '.$offset.', '.$length;
        $statement = $this->connection->query($query);

        return $statement->fetchAll();
    }
}
