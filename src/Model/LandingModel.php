<?php

namespace App\Model;

use App\Entity\Activity;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Repository\ActivityRepository;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\Query\Expr;
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
     * @param $unread
     * @param int|bool $limit
     *
     * @return array
     */
    public function getMessagesAndRequests(Member $member, $unread, $limit = 5)
    {
        $messageRepository = $this->getManager()->getRepository(Message::class);

        $messagesAndRequests = $messageRepository->getLatestMessagesAndRequests($member, $unread, $limit);

        return $messagesAndRequests;
    }

    /**
     * Generates notifications for display on home page
     * Format: 'title': "Message title #1",
     *   'text': Depending on type of notification,
     *   'link': Depending on type of notification,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',.
     *
     * @param int|bool $limit
     *
     * @return array
     */
    public function getNotifications(Member $member, $limit = 0)
    {
        $queryBuilder = $this->getManager()->createQueryBuilder();
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

        $queryBuilder = $this->getManager()->createQueryBuilder();

        $queryBuilder
            ->select('ft')
            ->from('App:ForumThread', 'ft')
            ->join('App:ForumPost', 'fp', Expr\Join::WITH, 'ft.lastPost = fp.id')
//            ->addSelect('fp.created')
            ->where("ft.deleted = 'NotDeleted'")
            ->andWhere("fp.deleted = 'NotDeleted'")
            ->orderBy('fp.created', 'desc')
        ;

        $groupIds = [];
        if ($groups) {
            $groupIds = array_map(function ($group) {
                return $group->getId();
            }, $member->getGroups());
        }
        $queryBuilder
            ->andWhere('ft.group IN (:groups)')
            ->setParameter('groups', $groupIds);
        if ($forum) {
            $queryBuilder
                ->orWhere('ft.group IS NULL');
        }

        if ($following) {
            // \todo: Add subscriptions?
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
     * @param mixed $online
     *
     * @throws Exception
     *
     * @return array
     */
    public function getUpcomingActivities(Member $member, $online)
    {
        $em = $this->getManager();
        $preferenceRepository = $em->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_ONLINE_ACTIVITIES]);
        $memberPreference = $member->getMemberPreference($preference);
        $value = ($online) ? 'Yes' : 'No';
        $memberPreference->setValue($value);
        $em->persist($memberPreference);
        $em->flush();

        /** @var ActivityRepository $repository */
        $repository = $this->getManager()->getRepository(Activity::class);
        $activities = $repository->findUpcomingAroundLocation($member, $online);

        return $activities;
    }

    public function getMemberDetails()
    {
    }

    public function getDonationCampaignDetails()
    {
    }

    /**
     * @return array
     */
    public function getTravellersInAreaOfMember(Member $member, int $radius)
    {
        $subtripRepository = $this->getManager()->getRepository(Subtrip::class);
        $legs = $subtripRepository->getLegsInAreaMaxGuests($member, 3, $radius);

        return $legs;
    }

    /**
     * @param $accommodation
     *
     * @return Member
     */
    public function updateMemberAccommodation(Member $member, $accommodation)
    {
        $member->setAccommodation($accommodation);
        $em = $this->getManager();
        $em->persist($member);
        $em->flush($member);

        return $member;
    }
}
