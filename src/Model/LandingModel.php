<?php

namespace App\Model;

use App\Entity\Activity;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Repository\ActivityRepository;
use App\Repository\MessageRepository;
use App\Repository\SubtripRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Exception;

class LandingModel
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
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
     * The threads with the newest posts in the member's groups (the BeWelcome group's agora included, if the member
     * is in it), for the home page.
     *
     * @return list<ForumThread>
     */
    public function getGroupThreads(Member $member, int $limit): array
    {
        $groupIds = $this->getGroupIds($member);
        if ([] === $groupIds) {
            return [];
        }

        return $this->entityManager->createQueryBuilder()
            ->select('ft')
            ->from(ForumThread::class, 'ft')
            ->join(ForumPost::class, 'fp', Expr\Join::WITH, 'ft.lastPost = fp.id')
            ->where('ft.group IN (:groups)')
            // only threads and posts that aren't deleted
            ->andWhere("ft.deleted = 'NotDeleted'")
            ->andWhere("fp.deleted = 'NotDeleted'")
            ->setParameter('groups', $groupIds)
            ->orderBy('fp.created', 'desc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * How many threads in the member's groups got a new post today.
     */
    public function getGroupThreadsWithPostsTodayCount(Member $member): int
    {
        $groupIds = $this->getGroupIds($member);
        if ([] === $groupIds) {
            return 0;
        }

        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(ft.id)')
            ->from(ForumThread::class, 'ft')
            ->join(ForumPost::class, 'fp', Expr\Join::WITH, 'ft.lastPost = fp.id')
            ->where('fp.created >= :today')
            ->andWhere('ft.group IN (:groups)')
            ->andWhere("ft.deleted = 'NotDeleted'")
            ->andWhere("fp.deleted = 'NotDeleted'")
            ->setParameter('today', new DateTime('today'))
            ->setParameter('groups', $groupIds)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Generates activities (near you) for display on home page.
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

    public function getTravellersInAreaOfMember(Member $member, int $radius): array
    {
        /** @var SubtripRepository $legRepository */
        $legRepository = $this->entityManager->getRepository(Subtrip::class);

        return $legRepository->getLegsInAreaMaxGuests($member, $radius);
    }

    public function updateMemberAccommodation(Member $member, string $accommodation): Member
    {
        $member->setAccommodation($accommodation);
        $em = $this->entityManager;
        $em->persist($member);
        $em->flush();

        return $member;
    }

    /**
     * @return list<int>
     */
    private function getGroupIds(Member $member): array
    {
        $groupIds = [];
        foreach ($member->getGroups() as $group) {
            if (null !== $group) {
                $groupIds[] = $group->getId();
            }
        }

        return $groupIds;
    }
}
