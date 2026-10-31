<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Preference;
use App\Entity\ProfileVisit;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

class ProfileVisitRepository extends EntityRepository
{
    public function getProfileVisitorsMember(Member $member, int $page): Pagerfanta
    {
        // $profileVisitors = $this->findby(['member' => $member], ['updated' => 'DESC']);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
            ->from(ProfileVisit::class, 'p')
            ->join('p.member', 'm', 'WITH', 'm.id = :memberId')
            ->join('p.visitor', 'v', 'WITH', 'v.status IN (:status)')
            ->orderBy('p.updated', 'DESC')
            ->setParameter('memberId', $member->getId())
            ->setParameter('status', ['Active', 'OutOfRemind'])
        ;
        $result = $qb->getQuery()->getResult();

        $paginator = new Pagerfanta(new ArrayAdapter($result));
        $paginator->setMaxPerPage(20);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Notes that a member looked at another one's profile (or brings the last visit's date up to now). Nothing is
     * kept when one of them doesn't want profile visits shown, as the old profile page did.
     */
    public function recordVisit(Member $member, Member $visitor): void
    {
        if ($member === $visitor) {
            return;
        }

        $preference = $this->getEntityManager()->getRepository(Preference::class)
            ->findOneBy(['codename' => Preference::SHOW_PROFILE_VISITORS]);
        if (
            null !== $preference
            && ('Yes' !== $member->getMemberPreferenceValue($preference)
                || 'Yes' !== $visitor->getMemberPreferenceValue($preference))
        ) {
            return;
        }

        $visit = $this->findOneBy(['member' => $member, 'visitor' => $visitor]);
        if (null === $visit) {
            $this->getEntityManager()->persist(new ProfileVisit($member, $visitor));
        } else {
            $visit->setUpdated(new DateTime());
        }
        $this->getEntityManager()->flush();
    }
}
