<?php

namespace App\Model;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class SafetyModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return Member[]
     */
    public function getSafetyTeamMembers(): array
    {
        $entityManager = $this->entityManager;

        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata(Member::class, 'm');

        $query = $this->entityManager->createNativeQuery("
            SELECT
                m.*
            FROM
                members m, rights, rightsvolunteers
            WHERE
                m.Status = 'Active'
                AND m.Username <> 'SafetyTeam'
                AND m.id = rightsvolunteers.IdMember
                AND rights.`Name` = 'SafetyTeam'
                AND rightsvolunteers.IdRight = rights.id
                AND rightsvolunteers.Level > 0
            ORDER BY
                username
                ", $rsm);

        return $query->getResult();
    }
}
