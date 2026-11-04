<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Entity\Right;
use App\Entity\RightVolunteer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends ServiceEntityRepository<RightVolunteer>
 */
class RightVolunteerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RightVolunteer::class);
    }

    public function findAssignment(Member $member, Right $right): ?RightVolunteer
    {
        return $this->findOneBy([
            'member' => $member,
            'right' => $right,
        ]);
    }

    public function findActiveForMemberAndName(Member $member, string $rightName): ?RightVolunteer
    {
        return $this->createQueryBuilder('rv')
            ->innerJoin('rv.right', 'r')
            ->where('rv.member = :member')
            ->andWhere('rv.level <> 0')
            ->andWhere('LOWER(r.name) = LOWER(:rightName)')
            ->setParameter('member', $member)
            ->setParameter('rightName', $rightName)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * The assignments of the rights tool's By right view: by right, then username; with the removed ones (level 0)
     * when $includeHistory.
     *
     * @param string[] $allowedRightNames the rights the volunteer manages
     */
    public function paginateAssignments(
        array $allowedRightNames,
        ?string $username,
        ?int $rightId,
        bool $includeHistory,
        int $page,
        int $itemsPerPage,
    ): Pagerfanta {
        $query = $this->assignmentsQuery($allowedRightNames)
            ->orderBy('r.Name', 'ASC')
            ->addOrderBy('m.Username', 'ASC');
        if (null !== $username && '' !== $username) {
            $query->andWhere('m.Username LIKE :username')
                ->setParameter('username', '%' . addcslashes($username, '%_\\') . '%');
        }
        if (null !== $rightId) {
            $query->andWhere('r.id = :rightId')
                ->setParameter('rightId', $rightId);
        }
        if (!$includeHistory) {
            $query->andWhere('rv.Level <> 0');
        }

        return $this->paginate($query, 'COUNT(DISTINCT rv.id)', $page, $itemsPerPage);
    }

    /**
     * The members of the rights tool's By member view: everyone with an assignment of a right the volunteer manages
     * (also removed ones), by username. With a right, only its current holders.
     *
     * @param string[] $allowedRightNames
     *
     * @return Pagerfanta<array<string, mixed>> member_id, username, status, last_active, place_name, country_name
     */
    public function paginateMembers(
        array $allowedRightNames,
        ?string $username,
        ?int $rightId,
        int $page,
        int $itemsPerPage,
    ): Pagerfanta {
        $query = $this->assignmentsQuery($allowedRightNames)
            ->select(
                'm.id AS member_id',
                'm.Username AS username',
                'm.Status AS status',
                'm.LastActive AS last_active',
                'place.name AS place_name',
                'country.name AS country_name',
            )
            ->groupBy('m.id', 'm.Username', 'm.Status', 'm.LastActive', 'place.name', 'country.name')
            ->orderBy('m.Username', 'ASC');
        if (null !== $username && '' !== $username) {
            $query->andWhere('m.Username LIKE :username')
                ->setParameter('username', '%' . addcslashes($username, '%_\\') . '%');
        }
        if (null !== $rightId) {
            $query->andWhere('r.id = :rightId AND rv.Level <> 0')
                ->setParameter('rightId', $rightId);
        }

        return $this->paginate($query, 'COUNT(DISTINCT m.id)', $page, $itemsPerPage);
    }

    /**
     * Every assignment of the given members, of the rights the volunteer manages: by member, current ones first,
     * then by right.
     *
     * @param string[] $allowedRightNames
     * @param int[]    $memberIds
     *
     * @return list<array<string, mixed>> the columns of assignmentsQuery()
     */
    public function findAssignmentsOfMembers(array $allowedRightNames, array $memberIds): array
    {
        if ([] === $memberIds) {
            return [];
        }

        return $this->assignmentsQuery($allowedRightNames)
            ->andWhere('m.id IN (:members)')
            ->setParameter('members', $memberIds, ArrayParameterType::INTEGER)
            ->orderBy('m.Username', 'ASC')
            ->addOrderBy('rv.Level = 0', 'ASC')
            ->addOrderBy('r.Name', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @param string[] $allowedRightNames
     *
     * @return array<int, int> the current holders (active members) per right id
     */
    public function countHoldersByRight(array $allowedRightNames): array
    {
        $rows = $this->assignmentsQuery($allowedRightNames)
            ->select('r.id AS right_id', 'COUNT(DISTINCT m.id) AS holders')
            ->andWhere('rv.Level <> 0')
            ->groupBy('r.id')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_combine(
            array_map(static fn (array $row): int => (int) $row['right_id'], $rows),
            array_map(static fn (array $row): int => (int) $row['holders'], $rows),
        );
    }

    /**
     * The assignments with their member (and the member's place) and right, of active members and of the rights the
     * volunteer manages.
     *
     * @param string[] $allowedRightNames
     */
    private function assignmentsQuery(array $allowedRightNames): QueryBuilder
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select(
                'rv.id AS assignment_id',
                'm.id AS member_id',
                'm.Username AS username',
                'm.Status AS status',
                'm.LastActive AS last_active',
                'place.name AS place_name',
                'country.name AS country_name',
                'r.id AS definition_id',
                'r.Name AS definition_name',
                'r.Description AS definition_description',
                'rv.Level AS level',
                'rv.Scope AS scope',
                'rv.Comment AS comment',
                'rv.created AS created_at',
                'rv.updated AS updated_at',
            )
            ->from('rightsvolunteers', 'rv')
            ->innerJoin('rv', 'rights', 'r', 'r.id = rv.IdRight')
            ->innerJoin('rv', 'member', 'm', 'm.id = rv.IdMember')
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
            ->where('m.Status IN (:statuses)')
            ->setParameter('statuses', MemberStatusType::ACTIVE_ALL_ARRAY, ArrayParameterType::STRING)
        ;

        if ([] === $allowedRightNames) {
            $query->andWhere('1 = 0');
        } else {
            $query->andWhere('r.Name IN (:rights)')
                ->setParameter('rights', $allowedRightNames, ArrayParameterType::STRING);
        }

        return $query;
    }

    private function paginate(QueryBuilder $query, string $count, int $page, int $itemsPerPage): Pagerfanta
    {
        $adapter = new QueryAdapter(
            $query,
            static fn (QueryBuilder $countQuery): QueryBuilder => $countQuery
                ->select($count)
                ->resetGroupBy()
                ->resetOrderBy(),
        );
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($itemsPerPage);
        $pager->setCurrentPage(min(max(1, $page), max(1, $pager->getNbPages())));

        return $pager;
    }
}
