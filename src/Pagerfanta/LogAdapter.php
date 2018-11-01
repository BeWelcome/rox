<?php

namespace App\Pagerfanta;

use App\Entity\Member;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Portability\Connection;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class LogAdapter implements AdapterInterface
{
    /** @var EntityManager */
    private $em;

    /** @var array */
    private $types;

    /** @var Member */
    private $member;

    public function __construct(EntityManager $em, $types = [], Member $member = null)
    {
        $this->em = $em;
        $this->types = $types;
        $this->member = $member;
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults()
    {
        $count = 0;
        try {
            list($sql, $params, $paramTypes) = $this->getSqlAndParameters(true);
            $stmt = $this->em->getConnection()->executeQuery($sql, $params, $paramTypes);
            $row = $stmt->fetchAll(PDO::FETCH_OBJ);
            $count = ($row[0])->count;
        } catch (DBALException $e) {
            // Return 0
        }

        return $count;
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
        $results = [];
        try {
            list($sql, $params, $paramTypes) = $this->getSqlAndParameters(false);
            $sql .= ' ORDER BY `l`.`created` DESC LIMIT '.$length.' OFFSET '.$offset;
            $stmt = $this->em->getConnection()->executeQuery($sql, $params, $paramTypes);
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (DBALException $e) {
            // We return an empty array in this case
        }

        return $results;
    }

    private function getSqlAndParameters($count)
    {
        $params = [];
        $paramTypes = [];
        $sql = 'SELECT ';
        if ($count) {
            $sql .= 'count(*) as count';
        } else {
            $sql .= "`l`.`type` as `type`, `l`.`Str` as logMessage, IFNULL(`m`.`Username`, '') as `Username`, `l`.`created` as created";
        }
        $sql .= ' FROM logs l LEFT JOIN members m ON l.IdMember = m.id';
        if (!empty($this->types) || $this->member) {
            $sql .= ' WHERE ';
        }
        if (!empty($this->types)) {
            $sql .= ' `type` IN (:types)';
            $params[':types'] = $this->types;
            $paramTypes[':types'] = Connection::PARAM_STR_ARRAY;
            if ($this->member) {
                $sql .= ' AND ';
            }
        }
        if ($this->member) {
            $sql .= ' `l`.`IdMember` = :memberId';
            $params[':memberId'] = $this->member->getId();
            $paramTypes[':memberId'] = PDO::PARAM_INT;
        }

        return [$sql, $params, $paramTypes];
    }
}
