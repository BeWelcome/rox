<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\ProfileNote;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
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

    public function getProfileNotes(Member $member): array
    {
        $q = $this->createQueryBuilder('n')
            ->where('n.owner = :member')
            ->setParameter('member', $member)
            ->getQuery();

        return $q->getResult();
    }

    public function getCategories(Member $member): array
    {
        $rawCategories = $this->createQueryBuilder('n')
            ->select('DISTINCT n.category')
            ->where('n.owner = :member')
            ->setParameter('member', $member)
            ->getQuery()
            ->getArrayResult()
        ;

        $categories = [];
        foreach ($rawCategories as $rawCategory) {
            $categories[] = [
                'value' => $rawCategory['category'],
                'text' => $rawCategory['category'],
            ];
        }

        return $categories;
    }

    public function getNoteForMemberPair(Member $loggedInMember, Member $member): ?ProfileNote
    {
        $note = $this->findOneBy(['member' => $member, 'owner' => $loggedInMember]);

        return $note;
    }
}
