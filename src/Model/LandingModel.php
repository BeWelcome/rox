<?php

namespace App\Model;

use App\Entity\Activity;
use App\Entity\Member;
use App\Repository\ActivityRepository;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

class LandingModel
{
    use ManagerTrait;

    /**
     * Generates messages for display on home page.
     *
     * Returns either all messages or only unread ones depending on checkbox state
     *
     * Format: 'title': "Message title #1",
     *   'id': 12345,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',
     *   'read': true
     *
     * @param Member $member
     * @param $unread
     * @param int|bool $limit
     *
     * @return array
     */
    public function getMessages(Member $member, $unread, $limit = 0)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('m')
            ->from('App:Message', 'm')
            ->where('m.receiver = :member')
            ->setParameter('member', $member);
        if ($unread) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('m.whenfirstread', "'0000-00-00 00:00.00'"),
                        $qb->expr()->isNull('m.whenfirstread')
                    )
                );
        }

        if (0 !== $limit) {
            $qb->setMaxResults($limit);
        }

        // throw new Exception($qb->getDQL());

        return $qb
            ->orderBy('m.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Generates notifications for display on home page
     * Format: 'title': "Message title #1",
     *   'text': Depending on type of notification,
     *   'link': Depending on type of notification,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',.
     *
     * @param Member   $member
     * @param int|bool $limit
     *
     * @return array
     */
    public function getNotifications(Member $member, $limit = 0)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('n')
            ->from('App:Notification', 'n')
            ->where('n.member = :member')
            ->setParameter('member', $member)
            ->andWhere('n.checked = 0')
            ->setMaxResults($limit);

        return $queryBuilder
            ->orderBy('n.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Generates threads for display on home page.
     *
     * Depends on checkboxes shown above the display
     *
     * @param Member   $member
     * @param bool     $groups
     * @param bool     $forum
     * @param bool     $following
     * @param int|bool $limit
     *
     * @return array
     */
    public function getThreads(Member $member, $groups, $forum, $following, $limit = 0)
    {
        if (0 === $groups + $forum + $following) {
            // Member decided not to show anything
            return [];
        }

        $em = $this->em;

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder
            ->select('ft')
            ->from('App:ForumThread', 'ft')
            ->where("ft.threadDeleted = 'NotDeleted'")
            ->orderBy('ft.lastPostid', 'desc');

        $groupIds = [];
        if ($groups) {
            $groupIds = array_map(function ($group) {
                return $group->getId();
            }, $member->getGroups());
        }
        if ($forum) {
            // The forum is identified by a group set to 0
            array_push($groupIds, 0);
        }
        $queryBuilder
            ->andWhere('ft.group IN (:groups)')
            ->setParameter('groups', $groupIds);

        if ($following) {
        }

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        $query = $queryBuilder->getQuery();

        $posts = $query->getResult();

        return $posts;
    }

    /**
     * Generates activities (near you) for display on home page.
     *
     * @param Member $member
     *
     * @throws Exception
     *
     * @return array
     */
    public function getActivities(Member $member)
    {
        /** @var ActivityRepository $repository */
        $repository = $this->em->getRepository(Activity::class);
        $activities = $repository->findUpcomingAroundLocation($member->getCity());

        return $activities;
    }

    public function getMemberDetails()
    {
    }

    public function getDonationCampaignDetails()
    {
    }

    /**
     * @param Member $member
     *
     * @return array
     */
    public function getTravellersInAreaOfMember(Member $member)
    {
        return [$member];
    }

    /**
     * @param Member $member
     * @param $accommodation
     *
     * @return Member
     */
    public function updateMemberAccommodation(Member $member, $accommodation)
    {
        try {
            $member->setAccommodation($accommodation);
            $this->em->persist($member);
            $this->em->flush($member);
        } catch (OptimisticLockException $e) {
        } catch (ORMException $e) {
        }

        return $member;
    }
}
