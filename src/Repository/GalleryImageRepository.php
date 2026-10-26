<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class GalleryImageRepository extends EntityRepository
{
    public function getImagesByMemberCount(Member $member): int
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->select('count(i.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getImagesForMember(Member $member): array
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->orderBy('i.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getLatestImagesFor(Member $member): array
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->orderBy('i.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * The member's pictures in one of their albums (galleries), newest first.
     */
    public function getImagesForMemberInAlbum(Member $member, int $albumId): array
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->innerJoin('i.galleries', 'g')
            ->andWhere('g.id = :album')
            ->setParameter('album', $albumId)
            ->orderBy('i.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * The member's albums (galleries) that hold at least one of their pictures, by title.
     *
     * @return list<array{id: int, title: string, count: int, cover: int}> (cover: the latest picture, shown on phones)
     */
    public function getAlbumsForMember(Member $member): array
    {
        $rows = $this->getImagesByMemberQueryBuilder($member)
            ->select('g.id AS id, g.title AS title, COUNT(i.id) AS count, MAX(i.id) AS cover')
            ->innerJoin('i.galleries', 'g')
            ->andWhere('g.owner = :member')
            ->groupBy('g.id, g.title')
            ->orderBy('g.title', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'count' => (int) $row['count'],
            'cover' => (int) $row['cover'],
        ], $rows);
    }

    private function getImagesByMemberQueryBuilder(Member $member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->where('i.owner = :member')
            ->setParameter('member', $member)
        ;

        return $qb;
    }
}
