<?php

namespace App\Repository;

use AnthonyMartin\GeoLocation\GeoLocation;
use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Preference;
use DateTime;
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
        ;

        $address = $member->getActiveAddress();
        if (false === $address || null === $address) {
            $qb->andWhere('1 = 0');
        } elseif (0 === $distance) {
            $qb->andWhere('a.location = :location')->setParameter('location', $address->getLocation());
        } else {
            $center = GeoLocation::fromDegrees((float) $address->getLatitude(), (float) $address->getLongitude());
            $box = $center->boundingCoordinates($distance, 'km');
            $qb
                ->andWhere('MBRContains(ST_Envelope(ST_GeomFromText(:box, 0)), l.coordinates) = 1')
                ->setParameter('box', \sprintf(
                    'LINESTRING(%F %F, %F %F)',
                    $box[0]->getLongitudeInDegrees(),
                    $box[0]->getLatitudeInDegrees(),
                    $box[1]->getLongitudeInDegrees(),
                    $box[1]->getLatitudeInDegrees()
                ))
            ;
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
        ;

        return $this->paginate($qb, $page, $items);
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

        $edison = GeoLocation::fromDegrees($latitude, $longitude);
        $coordinates = $edison->boundingCoordinates($distance, 'km');

        $qb = $this->createQueryBuilder('a');
        $qb
            ->leftJoin(
                Location::class,
                'l',
                Join::WITH,
                'a.location = l.geonameId '
                . 'AND MBRContains(ST_Envelope(ST_GeomFromText(:boundingBox, 0)), l.coordinates) = 1'
            )
            ->setParameter('boundingBox', \sprintf(
                'LINESTRING(%F %F, %F %F)',
                $coordinates[0]->getLongitudeInDegrees(),
                $coordinates[0]->getLatitudeInDegrees(),
                $coordinates[1]->getLongitudeInDegrees(),
                $coordinates[1]->getLatitudeInDegrees()
            ))
            ->where('(a.ends >= :now AND a.ends <= :three_months) OR (a.starts >= :now AND a.starts <= :three_months)')
            ->setParameter('now', new DateTime())
            ->setParameter('three_months', new DateTime()->modify('+3 months'))
            ->andWhere($qb->expr()->neq('a.status', 1)) // Do not show activities that were cancelled
            ->orderBy('a.starts', 'asc');

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
