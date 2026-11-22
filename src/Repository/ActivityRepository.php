<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Preference;
use App\Form\ActivityRadiusType;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class ActivityRepository extends EntityRepository
{
    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatest($page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new QueryAdapter($this->queryLatest(), false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return Query
     */
    public function queryLatest()
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT a
                FROM Activity a
                WHERE a.status <> 1
                ORDER BY a.id DESC
            ');
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * Only lists activities which do have only banned admins.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findProblematicActivities($page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new QueryAdapter($this->queryProblematicActivities(), false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    public function queryProblematicActivities(): Query
    {
        return $this->createQueryBuilder('a')
            ->join(ActivityAttendee::class, 'aa', Join::WITH, 'aa.activity = a and aa.organizer = 1')
            ->join(Member::class, 'm', Join::WITH, 'aa.attendee = m')
            ->where("m.status = 'Banned'")
            ->orWhere('DATEDIFF(a.ends, a.starts) > 1')
            ->orderBy('a.id', 'desc')
            ->getQuery();
    }

    /**
     * Get all activities around a given location.
     *
     * @param int $limit
     *
     * @throws Exception
     *
     * @return array
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function findUpcomingAroundLocation(Member $member, $online, $limit = 5)
    {
        $address = $member->getActiveAddress();

        if (false === $address) {
            return [];
        }

        $qb = $this->getUpcomingAroundLocationQueryBuilder($member, $online);

        $query = $qb
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * Get all activities around a given location.
     */
    public function getUpcomingAroundLocationCount(Member $member, $online): int
    {
        $address = $member->getActiveAddress();
        if (false === $address) {
            return 0;
        }

        $qb = $this->getUpcomingAroundLocationQueryBuilder($member, $online);
        $qb
            ->select('count(a.id) AS cnt')
        ;

        $unreadCount = $qb->getQuery()->getSingleScalarResult();

        return (int) $unreadCount;
    }

    /**
     * Activities that have not ended yet, soonest first. Cancelled ones are left out.
     */
    public function findUpcoming(bool $onlineOnly, int $page, int $items): Pagerfanta
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.ends >= :now OR a.starts >= :now')
            ->andWhere('a.status = :active')
            ->setParameter('now', new DateTime())
            ->setParameter('active', Activity::STATUS_ACTIVE)
            ->orderBy('a.starts', 'ASC')
            ->addOrderBy('a.id', 'ASC')
        ;
        if ($onlineOnly) {
            $qb->andWhere('a.online = 1');
        }

        return $this->paginate($qb, $page, $items);
    }

    /**
     * Activities that are over, the latest first, cancelled ones included (as the legacy page did).
     */
    public function findPast(int $page, int $items): Pagerfanta
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.ends < :now AND a.starts < :now')
            ->setParameter('now', new DateTime())
            ->orderBy('a.ends', 'DESC')
            ->addOrderBy('a.id', 'DESC')
        ;

        return $this->paginate($qb, $page, $items);
    }

    /**
     * Activities the member organizes or answered (yes, maybe or no), the latest start first.
     */
    public function findForMember(Member $member, int $page, int $items): Pagerfanta
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.attendees', 'aa', Join::WITH, 'aa.attendee = :member')
            ->setParameter('member', $member)
            ->orderBy('a.starts', 'DESC')
            ->addOrderBy('a.id', 'DESC')
        ;

        return $this->paginate($qb, $page, $items);
    }

    /**
     * Upcoming, not cancelled activities within $distance km of the member's place. With a distance of 0,
     * only the activities at the member's place itself. Empty when the member has no active address.
     */
    public function findNearMember(Member $member, int $distance, int $page, int $items): Pagerfanta
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.location', 'l')
            ->where('a.ends >= :now OR a.starts >= :now')
            ->andWhere('a.status = :active')
            ->setParameter('now', new DateTime())
            ->setParameter('active', Activity::STATUS_ACTIVE)
            ->orderBy('a.starts', 'ASC')
            ->addOrderBy('a.id', 'ASC')
        ;

        $address = $member->getActiveAddress();
        if (false === $address || null === $address) {
            $qb->andWhere('1 = 0');
        } elseif (0 === $distance) {
            $qb->andWhere('a.location = :location')->setParameter('location', $address->getLocation());
        } else {
            // the places with activities in the rectangle around the member (see PlacesInRectangle)
            $places = new PlacesInRectangle($this->getEntityManager()->getConnection())
                ->withActivities((float) $address->getLatitude(), (float) $address->getLongitude(), $distance);
            PlacesInRectangle::restrict($qb, 'l.geonameId', $places, 'placesInRectangle');
        }

        return $this->paginate($qb, $page, $items);
    }

    /**
     * Activities whose title, address or description, or whose place or country name contains the keyword,
     * the latest end first.
     */
    public function search(string $keyword, int $page, int $items): Pagerfanta
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.location', 'l')
            ->leftJoin('l.country', 'c')
            ->where('a.title LIKE :keyword OR a.address LIKE :keyword OR a.description LIKE :keyword'
                . ' OR l.name LIKE :keyword OR c.name LIKE :keyword')
            ->setParameter('keyword', '%' . addcslashes($keyword, '%_\\') . '%')
            ->orderBy('a.ends', 'DESC')
            ->addOrderBy('a.id', 'DESC')
        ;

        return $this->paginate($qb, $page, $items);
    }

    /**
     * The members to tell about the activity: active, with their active address within their own radius for
     * activities near them (ActivitiesNearMeRadius) of the activity's place, and with one of the given values for
     * the activity notifications (ActivitiesNotifications, never by default). Not the creator, not who answered
     * already. None for a cancelled, online or past activity, nor for one more than three months ahead.
     *
     * @param list<string> $notificationValues
     *
     * @return list<Member>
     */
    public function getMembersToNotifyAboutActivity(
        Activity $activity,
        array $notificationValues = [Preference::ACTIVITY_NOTIFICATIONS_IMMEDIATELY],
        int $duration = 3,
    ): array {
        $preferenceRepository = $this->getEntityManager()->getRepository(Preference::class);
        $notificationPreference = $preferenceRepository->findOneBy(['codename' => Preference::ACTIVITY_NOTIFICATIONS]);
        $radiusPreference = $preferenceRepository->findOneBy(['codename' => Preference::ACTIVITIES_NEAR_ME_RADIUS]);
        if (null === $notificationPreference || null === $radiusPreference) {
            return [];
        }

        $activeStatuses = [MemberStatusType::ACTIVE, MemberStatusType::OUT_OF_REMIND];
        $now = new DateTime();
        if (
            $activity->isCancelled()
            || $activity->isOnline()
            || $activity->getEnds() < $now
            || $activity->getStarts() > new DateTime()->modify('+' . $duration . ' months')
            || !\in_array($activity->getCreatedBy()->getStatus(), $activeStatuses, true)
        ) {
            return [];
        }

        $location = $activity->getLocation();
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('DISTINCT receiver')
            ->from(Member::class, 'receiver')
            ->join('receiver.addresses', 'a', Join::WITH, 'a.active = true')
            ->join('a.location', 'memberLocation')
            ->leftJoin('receiver.preferences', 'notifications', Join::WITH, 'notifications.preference = :notificationPreference')
            ->leftJoin('receiver.preferences', 'radius', Join::WITH, 'radius.preference = :radiusPreference')
            ->leftJoin(ActivityAttendee::class, 'answered', Join::WITH, 'answered.activity = :activity AND answered.attendee = receiver')
            ->where($qb->expr()->in('receiver.status', ':activeStatuses'))
            ->andWhere('receiver <> :creator')
            ->andWhere('answered.id IS NULL')
            ->andWhere('COALESCE(notifications.value, :defaultNotification) IN (:notificationValues)')
            ->andWhere(
                'ST_Distance_Sphere(memberLocation.coordinates, ST_GeomFromText(:centerPoint, 0)) <= 1000 * COALESCE(radius.value, :defaultRadius)'
            )
            ->setParameter('activeStatuses', $activeStatuses)
            ->setParameter('creator', $activity->getCreatedBy())
            ->setParameter('activity', $activity)
            ->setParameter('notificationPreference', $notificationPreference)
            ->setParameter('radiusPreference', $radiusPreference)
            ->setParameter('defaultNotification', $notificationPreference->getDefaultValue())
            ->setParameter('notificationValues', $notificationValues)
            ->setParameter('defaultRadius', $radiusPreference->getDefaultValue())
            ->setParameter('centerPoint', \sprintf('POINT(%F %F)', $location->getLongitude(), $location->getLatitude()))
        ;
        // the places with members within the largest radius first (see PlacesInRectangle), then each member's own
        $places = new PlacesInRectangle($this->getEntityManager()->getConnection())->withMembers(
            (float) $location->getLatitude(),
            (float) $location->getLongitude(),
            (float) max(ActivityRadiusType::DISTANCES),
        );
        PlacesInRectangle::restrict($qb, 'memberLocation.geonameId', $places, 'placesInRectangle');

        return $qb->getQuery()->getResult();
    }

    /**
     * Both ends included: the dates are compared in whole seconds, and a run's end is the next run's start; the
     * notifications sent (member_activity_notification_sent) keep an activity of that second from being mailed twice.
     *
     * @return list<Activity>
     */
    public function findActivitiesCreatedBetween(DateTimeInterface $since, DateTimeInterface $until): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.created >= :since')
            ->andWhere('a.created <= :until')
            ->setParameter('since', $since)
            ->setParameter('until', $until)
            ->orderBy('a.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function getActivitiesRadius(Member $member)
    {
        $preferenceRepository = $this->getEntityManager()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::ACTIVITIES_NEAR_ME_RADIUS]);
        $memberPreference = $member->getMemberPreference($preference);

        $distance = 20;
        if ($preference) {
            $distance = (int) $memberPreference->getValue();
        }

        return $distance;
    }

    /**
     * @throws Exception
     */
    private function getUpcomingAroundLocationQueryBuilder(Member $member, $online): ?QueryBuilder
    {
        $address = $member->getActiveAddress();
        $distance = $this->getActivitiesRadius($member);

        // Fetch latitude and longitude of member's location
        $latitude = $address->getLatitude();
        $longitude = $address->getLongitude();

        // only the places with activities in the rectangle around the member join (see PlacesInRectangle)
        $places = new PlacesInRectangle($this->getEntityManager()->getConnection())
            ->withActivities((float) $latitude, (float) $longitude, (float) $distance);

        $qb = $this->createQueryBuilder('a');
        $qb
            ->leftJoin(
                Location::class,
                'l',
                Join::WITH,
                'a.location = l.geonameId AND ' . ([] === $places ? '1 = 0' : 'l.geonameId IN (:placesInRectangle)')
            );
        if ([] !== $places) {
            $qb->setParameter('placesInRectangle', $places, ArrayParameterType::INTEGER);
        }
        $qb
            ->where('(a.ends >= :now AND a.ends <= :three_months) OR (a.starts >= :now AND a.starts <= :three_months)')
            ->setParameter('now', new DateTime())
            ->setParameter('three_months', new DateTime()->modify('+3 months'))
            ->andWhere($qb->expr()->neq('a.status', 1)) // Do not show activities that were cancelled
            ->orderBy('a.starts', 'asc')
            ->addOrderBy('a.id', 'asc');

        if ($online) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.online', 1),
                $qb->expr()->isNotNull('l.geonameId')
            ));
        } else {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->isNull('a.online'),
                    $qb->expr()->eq('a.online', 0),
                ))
                ->andWhere($qb->expr()->isNotNull('l.geonameId'))
            ;
        }

        return $qb;
    }

    private function paginate(QueryBuilder $qb, int $page, int $items): Pagerfanta
    {
        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
