<?php

namespace App\Repository;

use AnthonyMartin\GeoLocation\GeoLocation;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Preference;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Pagerfanta\Adapter\DoctrineORMAdapter;
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
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest(), false));
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
                FROM App:Activity a
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
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryProblematicActivities(), false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return Query
     */
    public function queryProblematicActivities()
    {
        return $this->createQueryBuilder('a')
            ->join('App:ActivityAttendee', 'aa', Join::WITH, 'aa.activity = a and aa.organizer = 1')
            ->join('App:Member', 'm', Join::WITH, 'aa.attendee = m')
            ->where("m.status = 'Banned'")
            ->orWhere('DATEDIFF(a.ends, a.starts) > 1')
            ->orderBy('a.id', 'desc')
            ->getQuery();
    }

    /**
     * Get all activities around a given location.
     *
     * @param int   $limit
     * @param int   $distance
     * @param mixed $online
     *
     * @throws Exception
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function findUpcomingAroundLocation(Member $member, $online, $limit = 5)
    {
        $qb = $this->getUpcomingAroundLocationQueryBuilder($member, $online);

        $query = $qb
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * Get all activities around a given location.
     *
     * @param int   $distance
     * @param mixed $online
     *
     * @return int
     */
    public function getUpcomingAroundLocationCount(Member $member, $online)
    {
        $qb = $this->getUpcomingAroundLocationQueryBuilder($member, $online);
        $qb
            ->select('count(a.id) AS cnt')
        ;

        $q = $qb->getQuery();
        $result = $q->getResult();
        if ($result) {
            $unreadCount = $result[0]['cnt'];
        } else {
            $unreadCount = 0;
        }

        return (int) $unreadCount;
    }

    private function getActivitiesRadius(Member $member)
    {
        $preferenceRepository = $this->getEntityManager()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::ACTIVITIES_NEAR_ME_RADIUS]);
        $memberPreference = $member->getMemberPreference($preference);

        $distance = 20;
        if ($preference) {
            $distance = (int) ($memberPreference->getValue());
        }

        return $distance;
    }

    /**
     * @param int   $distance
     * @param mixed $online
     *
     * @throws Exception
     *
     * @return QueryBuilder
     */
    private function getUpcomingAroundLocationQueryBuilder(Member $member, $online)
    {
        $location = $member->getCity();
        $distance = $this->getActivitiesRadius($member);

        // Fetch latitude and longitude of member's location
        $latitude = $location->getLatitude();
        $longitude = $location->getLongitude();

        $edison = GeoLocation::fromDegrees($latitude, $longitude);
        $coordinates = $edison->boundingCoordinates($distance, 'km');

        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where($expr->gte('latitude', $coordinates[0]->getLatitudeInDegrees()))
            ->andWhere($expr->lte('latitude', $coordinates[1]->getLatitudeInDegrees()))
            ->andWhere($expr->gte('longitude', $coordinates[0]->getLongitudeInDegrees()))
            ->andWhere($expr->lte('longitude', $coordinates[1]->getLongitudeInDegrees()));

        $locations = $this->getEntityManager()->getRepository('App:Location')
            ->matching($criteria);

        $qb = $this->createQueryBuilder('a');
        if ($online) {
            $qb
                ->where($qb->expr()->orX(
                    $qb->expr()->eq('a.public', 1),
                    $qb->expr()->in('a.location', ':locations')
                ));
        } else {
            $qb
                ->where($qb->expr()->in('a.location', ':locations'))
            ;
        }
        $qb
            ->andWhere($qb->expr()->andX(
                $qb->expr()->lte('a.ends', ':threeMonths'),
                $qb->expr()->gte('a.starts', ':now')
            ))
            ->setParameter('now', new DateTime())
            ->setParameter('threeMonths', (new DateTime())->modify('+3 months'))
            ->setParameter('locations', $locations)
            ->orderBy('a.ends', 'DESC')
        ;

        return $qb;
    }
}
