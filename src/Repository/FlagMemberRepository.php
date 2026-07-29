<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Flag;
use App\Entity\FlagMember;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends ServiceEntityRepository<FlagMember>
 */
class FlagMemberRepository extends ServiceEntityRepository
{
    public const int PAGE_SIZE = 50;

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

    public function paginateAssignments(
        ?string $username,
        ?int $flagId,
        bool $includeHistory,
        bool $memberFirst,
        int $page,
    ): Pagerfanta {
        $connection = $this->getEntityManager()->getConnection();
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
        $query = $connection->createQueryBuilder()
            ->select(
                'fm.id AS assignment_id',
                'm.id AS member_id',
                'm.Username AS username',
                'm.Status AS status',
                'm.LastActive AS last_active',
                'place.name AS place_name',
                'country.name AS country_name',
                'f.id AS definition_id',
                'f.Name AS definition_name',
                'f.Description AS definition_description',
                'fm.Level AS level',
                'fm.Scope AS scope',
                'fm.Comment AS comment',
                'fm.created AS created_at',
                'fm.updated AS updated_at',
                'CASE WHEN EXISTS (' . $newerAssignment . ') THEN 1 ELSE 0 END AS superseded',
            )
            ->from('flagsmembers', 'fm')
            ->innerJoin('fm', 'flags', 'f', 'f.id = fm.IdFlag')
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
            ->leftJoin('a', 'geo__names', 'place', 'place.geoname_id = a.location')
            ->leftJoin('place', 'geo__names', 'country', 'country.geoname_id = place.country')
            ->where('m.Status IN (:statuses)')
            ->andWhere('f.Relevance > 0')
            ->setParameter('statuses', MemberStatusType::ACTIVE_ALL_ARRAY, ArrayParameterType::STRING)
        ;

        if (null !== $username && '' !== $username) {
            $query->andWhere('m.Username = :username')
                ->setParameter('username', $username);
        }
        if (null !== $flagId) {
            $query->andWhere('f.id = :flagId')
                ->setParameter('flagId', $flagId);
        }
        if (!$includeHistory) {
            $query->andWhere('NOT EXISTS (' . $newerAssignment . ')')
                ->andWhere('fm.Level <> 0');
        }

        if ($memberFirst) {
            $query->orderBy('m.Username', 'ASC')
                ->addOrderBy('f.Relevance', 'DESC')
                ->addOrderBy('f.Name', 'ASC');
        } else {
            $query->orderBy('f.Relevance', 'DESC')
                ->addOrderBy('f.Name', 'ASC')
                ->addOrderBy('m.Username', 'ASC');
        }
        $query->addOrderBy('fm.created', 'DESC')
            ->addOrderBy('fm.id', 'DESC');

        return $this->paginate($query, $page);
    }

    private function paginate(QueryBuilder $query, int $page): Pagerfanta
    {
        $adapter = new QueryAdapter(
            $query,
            static fn (QueryBuilder $countQuery): QueryBuilder => $countQuery
                ->select('COUNT(DISTINCT fm.id)')
                ->resetOrderBy(),
        );
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::PAGE_SIZE);
        $pager->setCurrentPage(min(max(1, $page), max(1, $pager->getNbPages())));

        return $pager;
    }
}
