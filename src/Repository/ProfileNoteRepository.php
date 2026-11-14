<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\ProfileNote;
use App\Entity\ProfileNoteCategory;
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

        // notes in one of the categories
        if (!empty($categories)) {
            $qb
                ->andWhere('EXISTS (SELECT 1 FROM ' . ProfileNoteCategory::class . ' c WHERE c.note = n AND c.name IN (:categories))')
                ->setParameter('categories', $categories)
            ;
        }

        switch ($order) {
            case 2:
                // by the first of a note's categories
                $qb->addSelect('(SELECT MIN(oc.name) FROM ' . ProfileNoteCategory::class . ' oc WHERE oc.note = n) AS HIDDEN firstCategory');
                $qb->orderBy('firstCategory', 'ASC');
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

    /**
     * The categories the member gave their notes, by name.
     *
     * @return array<string, string> name => name
     */
    public function getCategories(Member $member): array
    {
        $categories = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT c.name')
            ->from(ProfileNoteCategory::class, 'c')
            ->join('c.note', 'n')
            ->where('n.owner = :member')
            ->setParameter('member', $member)
            ->orderBy('c.name', 'ASC')
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
        $rows = $this->getEntityManager()->createQueryBuilder()
            ->select('c.name AS category, COUNT(DISTINCT n.id) AS total')
            ->from(ProfileNoteCategory::class, 'c')
            ->join('c.note', 'n')
            ->where('n.owner = :member')
            ->setParameter('member', $member)
            ->groupBy('c.name')
            ->orderBy('c.name', 'ASC')
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
