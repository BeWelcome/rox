<?php

namespace App\Repository;

use AnthonyMartin\GeoLocation\GeoLocation;
use App\Entity\Activity;
use App\Entity\Language;
use App\Entity\Location;
use App\Entity\Member;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
    public function findLatestBannedAdmins($page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatestBannedAdmins(), false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return Query
     */
    public function queryLatestBannedAdmins()
    {
        return $this->createQueryBuilder('a')
            ->join('App:ActivityAttendee', 'aa', Join::WITH, 'aa.activity = a and aa.organizer = 1')
            ->join('App:Member', 'm', Join::WITH, "aa.attendee = m and m.status = 'Banned'")
            ->orderBy(c, 'desc')
            ->getQuery();
    }

    /**
     * @param Location $location
     * @param int $distance
     * @return QueryBuilder
     *
     * @throws Exception
     */
    private function getUpcomingAroundLocationQueryBuilder(Location $location, $distance)
    {
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
        $qb
            ->where('a.location IN (:locations)')
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
    /**
     * Get all activities around a given location.
     *
     * @param Location $location
     * @param int      $limit
     * @param int      $distance
     *
     * @throws Exception
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function findUpcomingAroundLocation(Location $location, $limit = 5, $distance = 20)
    {
        $qb = $this->getUpcomingAroundLocationQueryBuilder($location, $distance);

        $query = $qb
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * Get all activities around a given location.
     *
     * @param Location $location
     * @param int $distance
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getUpcomingAroundLocationCount(Location $location, $distance = 20)
    {
        $qb = $this->getUpcomingAroundLocationQueryBuilder($location, $distance);
        $qb
            ->select('count(a.id)')
        ;

        $unreadCount = 0;
        try {
            $q = $qb->getQuery();
            $unreadCount = $q->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        } catch (NoResultException $e) {
        }

        return (int) $unreadCount;
    }
}
