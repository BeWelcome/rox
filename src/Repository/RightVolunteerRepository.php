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
    public const int PAGE_SIZE = 50;

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
     * @param string[] $allowedRightNames
     */
    public function paginateAssignments(
        array $allowedRightNames,
        ?string $username,
        ?int $rightId,
        bool $includeHistory,
        bool $memberFirst,
        int $page,
    ): Pagerfanta {
        $connection = $this->getEntityManager()->getConnection();
        $query = $connection->createQueryBuilder()
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
            ->leftJoin('a', 'geo__names', 'place', 'place.geoname_id = a.location')
            ->leftJoin('place', 'geo__names', 'country', 'country.geoname_id = place.country')
            ->where('m.Status IN (:statuses)')
            ->setParameter('statuses', MemberStatusType::ACTIVE_ALL_ARRAY, ArrayParameterType::STRING)
        ;

        if ([] === $allowedRightNames) {
            $query->andWhere('1 = 0');
        } else {
            $query->andWhere('r.Name IN (:rights)')
                ->setParameter('rights', $allowedRightNames, ArrayParameterType::STRING);
        }
        if (null !== $username && '' !== $username) {
            $query->andWhere('m.Username = :username')
                ->setParameter('username', $username);
        }
        if (null !== $rightId) {
            $query->andWhere('r.id = :rightId')
                ->setParameter('rightId', $rightId);
        }
        if (!$includeHistory) {
            $query->andWhere('rv.Level <> 0');
        }

        if ($memberFirst) {
            $query->orderBy('m.Username', 'ASC')
                ->addOrderBy('r.Name', 'ASC');
        } else {
            $query->orderBy('r.Name', 'ASC')
                ->addOrderBy('m.Username', 'ASC');
        }

        return $this->paginate($query, $page);
    }

    private function paginate(QueryBuilder $query, int $page): Pagerfanta
    {
        $adapter = new QueryAdapter(
            $query,
            static fn (QueryBuilder $countQuery): QueryBuilder => $countQuery
                ->select('COUNT(DISTINCT rv.id)')
                ->resetOrderBy(),
        );
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::PAGE_SIZE);
        $pager->setCurrentPage(min(max(1, $page), max(1, $pager->getNbPages())));

        return $pager;
    }
}
