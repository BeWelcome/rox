<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\MemberFlag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends ServiceEntityRepository<MemberFlag>
 */
class MemberFlagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberFlag::class);
    }

    public function isShadowBanned(Member $member): bool
    {
        return (bool) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.member = :member')
            ->andWhere('f.released IS NULL')
            ->setParameter('member', $member)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * The flags still open (released: the released ones), the newest first.
     *
     * @return Pagerfanta<MemberFlag>
     */
    public function findFlags(bool $released, int $page, int $limit): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->join('f.member', 'm')
            ->addSelect('m')
            ->leftJoin('f.pattern', 'p')
            ->addSelect('p')
            ->where($released ? 'f.released IS NOT NULL' : 'f.released IS NULL')
            ->orderBy($released ? 'f.released' : 'f.created', 'DESC')
            ->addOrderBy('f.id', 'DESC');

        return Pagerfanta::createForCurrentPageWithMaxPerPage(new QueryAdapter($queryBuilder), $page, $limit);
    }

    public function countOpen(): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.released IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<int, int> the number of members each pattern flagged, by the pattern's id
     */
    public function countByPattern(): array
    {
        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.pattern) AS pattern', 'COUNT(f.id) AS flagged')
            ->where('f.pattern IS NOT NULL')
            ->groupBy('f.pattern')
            ->getQuery()
            ->getArrayResult();
        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['pattern']] = (int) $row['flagged'];
        }

        return $counts;
    }

    /**
     * @param list<int> $memberIds
     *
     * @return array<int, bool> by member id, for the flagged ones: true while shadow banned, false once released
     */
    public function statesFor(array $memberIds): array
    {
        if ([] === $memberIds) {
            return [];
        }
        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.member) AS member', 'f.released')
            ->where('f.member IN (:members)')
            ->setParameter('members', $memberIds)
            ->getQuery()
            ->getArrayResult();
        $states = [];
        foreach ($rows as $row) {
            $states[(int) $row['member']] = null === $row['released'];
        }

        return $states;
    }

    public function isFlagged(Member $member): bool
    {
        return null !== $this->findOneBy(['member' => $member]);
    }
}
