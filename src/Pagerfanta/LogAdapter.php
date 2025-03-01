<?php

namespace App\Pagerfanta;

use App\Entity\Member;
use Doctrine\DBAL\Portability\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class LogAdapter implements AdapterInterface
{
    private EntityManagerInterface $entityManager;

    private array $types;

    private Member $member;

    public function __construct(EntityManagerInterface $entityManager, array $types = [], Member $member = null)
    {
        $this->entityManager = $entityManager;
        $this->types = $types;
        $this->member = $member;
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $count = 0;
        try {
            list($sql, $params, $paramTypes) = $this->getSqlAndParameters(true);
            $stmt = $this->entityManager->getConnection()->executeQuery($sql, $params, $paramTypes);
            $count = $stmt->fetchOne();
        } catch (Exception $e) {
            // Return 0
        }

        return $count;
    }

    /**
     * Returns an slice of the results.
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $results = [];
        try {
            list($sql, $params, $paramTypes) = $this->getSqlAndParameters(false);
            $sql .= ' ORDER BY `l`.`created` DESC LIMIT ' . $length . ' OFFSET ' . $offset;
            $stmt = $this->entityManager->getConnection()->executeQuery($sql, $params, $paramTypes);
            $results = $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            // We return an empty array in this case
        }

        return $results;
    }

    private function getSqlAndParameters($count): array
    {
        $params = [];
        $paramTypes = [];
        $sql = 'SELECT ';
        if ($count) {
            $sql .= 'count(*) as count';
        } else {
            $sql .= "`l`.`type` as `type`, `l`.`Str` as logMessage, IFNULL(`m`.`Username`, '') as `Username`," .
                '`l`.`created` as created';
        }
        $sql .= ' FROM logs l LEFT JOIN members m ON l.IdMember = m.id';
        if (!empty($this->types) || $this->member) {
            $sql .= ' WHERE ';
        }
        if (!empty($this->types)) {
            $sql .= ' `type` IN (:types)';
            $params[':types'] = $this->types;
            if (null !== $this->member) {
                $sql .= ' AND ';
            }
        }
        if (null !== $this->member) {
            $sql .= ' `l`.`IdMember` = :memberId';
            $params[':memberId'] = $this->member->getId();
            $paramTypes[':memberId'] = PDO::PARAM_INT;
        }

        return [$sql, $params, $paramTypes];
    }
}
