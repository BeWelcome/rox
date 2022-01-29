<?php

namespace App\Model;

use App\Entity\Activity;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Repository\ActivityRepository;
use App\Repository\MessageRepository;
use App\Repository\SubtripRepository;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Exception;

class LandingModel
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Generates conversations for display on home page.
     *
     * Returns either all messages or only unread ones depending on checkbox state
     *
     * Format: 'title': "Message title #1",
     *   'id': 12345,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',
     *   'read': true
     */
    public function getConversations(Member $member, bool $unread, $limit = 5): array
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        $conversations = $messageRepository->getConversations($member, $unread, $limit);

        return $conversations;
    }

    /**
     * Generates notifications for display on home page
     * Format: 'title': "Message title #1",
     *   'text': Depending on type of notification,
     *   'link': Depending on type of notification,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',.
     */
    public function getNotifications(Member $member, $limit = 0): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('n')
            ->from('App:Notification', 'n')
            ->where('n.member = :member')
            ->setParameter('member', $member)
            ->andWhere('n.checked = 0')
            ->orderBy('n.created', 'DESC')
            ->setMaxResults($limit);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * Generates threads for display on home page.
     *
     * Depends on checkboxes shown above the display.
     */
    public function getThreads(Member $member, bool $groups, bool $forum, bool $following, int $limit = 0): array
    {
        if (!$groups && !$forum && !$following) {
            // Member decided not to show anything
            return [];
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select('ft')
            ->from('App:ForumThread', 'ft')
            ->join('App:ForumPost', 'fp', Expr\Join::WITH, 'ft.lastPost = fp.id')
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

        // Only show posts that aren't deleted and that aren't in a deleted thread
        $queryBuilder
            ->andwhere("ft.deleted = 'NotDeleted'")
            ->andWhere("fp.deleted = 'NotDeleted'");

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
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
        $em = $this->entityManager;
        $preferenceRepository = $em->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_ONLINE_ACTIVITIES]);
        $memberPreference = $member->getMemberPreference($preference);
        $value = ($online) ? 'Yes' : 'No';
        $memberPreference->setValue($value);
        $em->persist($memberPreference);
        $em->flush();

        /** @var ActivityRepository $repository */
        $repository = $this->entityManager->getRepository(Activity::class);

        return $repository->findUpcomingAroundLocation($member, $online);
    }

    public function getMemberDetails()
    {
    }

    public function getDonationCampaignDetails()
    {
    }

    public function getTravellersInAreaOfMember(Member $member, int $radius): array
    {
        /** @var SubtripRepository $subTripRepository */
        $subTripRepository = $this->entityManager->getRepository(Subtrip::class);

        return $subTripRepository->getLegsInAreaMaxGuests($member, $radius);
    }

    public function updateMemberAccommodation(Member $member, string $accommodation): Member
    {
        $member->setAccommodation($accommodation);
        $em = $this->entityManager;
        $em->persist($member);
        $em->flush();

        return $member;
    }
}
