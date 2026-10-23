<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\ProfileNote;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class ProfileNoteRepository extends EntityRepository
{
    public function getProfileNotesCount(Member $member): int
    {
        $q = $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->where('n.owner = :member')
            ->setParameter('member', $member)
            ->getQuery();

        return (int) $q->getSingleScalarResult();
    }

    public function getProfileNotes(
        Member $member,
        array $categories = [],
        int $order = 1,
        int $page = 1,
        int $itemsPerPage = 20,
        ?string $search = null,
    ): Pagerfanta {
        $qb = $this->createQueryBuilder('n')
            ->where('n.owner = :member')
            ->setParameter('member', $member)
        ;

        // A word to find in the member's username or in the note
        $search = trim((string) $search);
        if ('' !== $search) {
            $qb
                ->join('n.member', 'noted')
                ->andWhere('noted.username LIKE :search OR n.comment LIKE :search')
                ->setParameter('search', '%' . addcslashes($search, '%_') . '%')
            ;
        }

        if (!empty($categories)) {
            $qb
                ->andWhere('n.category IN (:categories)')
                ->setParameter('categories', $categories)
            ;
        }

        switch ($order) {
            case 2:
                $qb->orderBy('n.category', 'ASC');
                $qb->addOrderBy('n.updated', 'DESC');
                break;
            case 1:
            default:
                $qb->orderBy('n.updated', 'DESC');
                break;
        }
        $q = $qb->getQuery();

        $notes = new Pagerfanta(new QueryAdapter($q));
        $notes->setMaxPerPage($itemsPerPage);
        $notes->setCurrentPage($page);

        return $notes;
    }

    public function getCategories(Member $member): array
    {
        $categories = $this->createQueryBuilder('n')
            ->select('DISTINCT n.category')
            ->where('n.owner = :member')
            ->setParameter('member', $member)
            ->orderBy('n.category', 'ASC')
            ->getQuery()
            ->getSingleColumnResult()
        ;

        return array_combine($categories, $categories);
    }

    /**
     * The categories of the member's notes with the number of notes in each, by name.
     *
     * @return array<string, int>
     */
    public function getCategoryCounts(Member $member): array
    {
        $rows = $this->createQueryBuilder('n')
            ->select('n.category AS category, COUNT(n.id) AS total')
            ->where('n.owner = :member')
            ->andWhere("n.category IS NOT NULL AND n.category <> ''")
            ->setParameter('member', $member)
            ->groupBy('n.category')
            ->orderBy('n.category', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['category']] = (int) $row['total'];
        }

        return $counts;
    }

    public function getNoteForMemberPair(Member $loggedInMember, Member $member): ?ProfileNote
    {
        $note = $this->findOneBy(['member' => $member, 'owner' => $loggedInMember]);

        return $note;
    }
}
