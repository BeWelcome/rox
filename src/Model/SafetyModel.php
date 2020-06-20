<?php

namespace App\Model;

use App\Entity\Language;
use App\Entity\Member;
use App\Pagerfanta\LogAdapter;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Pagerfanta\Pagerfanta;

class SafetyModel
{
    use ManagerTrait;

    /**
     * @return Member[]
     */
    public function getSafetyTeamMembers(): array
    {
        $entityManager = $this->getManager();

        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata(Member::class, 'm');

        $query = $this->getManager()->createNativeQuery("
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
