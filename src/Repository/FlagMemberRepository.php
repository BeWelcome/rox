<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Flag;
use App\Entity\FlagMember;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FlagMember>
 */
class FlagMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FlagMember::class);
    }

    public function findCurrent(Member $member, Flag $flag): ?FlagMember
    {
        return $this->createQueryBuilder('fm')
            ->where('fm.member = :member')
            ->andWhere('fm.flag = :flag')
            ->setParameter('member', $member)
            ->setParameter('flag', $flag)
            ->orderBy('fm.created', 'DESC')
            ->addOrderBy('fm.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Every assignment of the flag ever made (the flags list): the current ones first (the newest row of a member,
     * not removed), then the others, newest first.
     *
     * @return list<array<string, mixed>> assignment_id, username, place_name, country_name, comment, created_at,
     *                                    updated_at, current (1 or 0)
     */
    public function findAllOfFlag(Flag $flag, ?string $username): array
    {
        $newerAssignment = <<<'SQL'
            SELECT 1
            FROM flagsmembers newer
            WHERE newer.IdMember = fm.IdMember
              AND newer.IdFlag = fm.IdFlag
              AND (
                newer.created > fm.created
                OR (newer.created = fm.created AND newer.id > fm.id)
              )
            SQL;
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select(
                'fm.id AS assignment_id',
                'm.Username AS username',
                'place.name AS place_name',
                'country.name AS country_name',
                'fm.Comment AS comment',
                'fm.created AS created_at',
                'fm.updated AS updated_at',
                'CASE WHEN fm.Level <> 0 AND NOT EXISTS (' . $newerAssignment . ') THEN 1 ELSE 0 END AS current',
            )
            ->from('flagsmembers', 'fm')
            ->innerJoin('fm', 'member', 'm', 'm.id = fm.IdMember')
            ->leftJoin(
                'm',
                'address',
                'a',
                'a.id = ('
                . 'SELECT MIN(active_address.id) FROM address active_address '
                . 'WHERE active_address.member_id = m.id AND active_address.active = 1'
                . ')',
            )
            ->leftJoin('a', 'location', 'place', 'place.geoname_id = a.location')
            ->leftJoin('place', 'location', 'country', 'country.geoname_id = place.country')
            ->where('fm.IdFlag = :flag')
            ->andWhere('m.Status IN (:statuses)')
            ->setParameter('flag', $flag->getId())
            ->setParameter('statuses', MemberStatusType::ACTIVE_ALL_ARRAY, ArrayParameterType::STRING)
            ->orderBy('current', 'DESC')
            ->addOrderBy('fm.created', 'DESC')
            ->addOrderBy('fm.id', 'DESC');
        if (null !== $username && '' !== $username) {
            $query->andWhere('m.Username LIKE :username')
                ->setParameter('username', '%' . addcslashes($username, '%_\\') . '%');
        }

        return $query->executeQuery()->fetchAllAssociative();
    }

    /**
     * How often the member had the flag before (removed assignments).
     */
    public function countRemoved(Member $member, Flag $flag): int
    {
        return (int) $this->createQueryBuilder('fm')
            ->select('COUNT(fm.id)')
            ->where('fm.member = :member')
            ->andWhere('fm.flag = :flag')
            ->andWhere('fm.level = 0')
            ->setParameter('member', $member)
            ->setParameter('flag', $flag)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
