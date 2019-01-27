<?php

namespace App\Repository;

use AnthonyMartin\GeoLocation\GeoLocation;
use App\Entity\Location;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
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
     * Get all activities around a given location.
     *
     * @param Location $location
     * @param int      $limit
     * @param int      $distance
     *
     * @throws \Exception
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function findUpcomingAroundLocation(Location $location, $limit = 5, $distance = 20)
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
            ->andWhere($qb->expr()->orX(
                $qb->expr()->lte('a.ends', ':threeMonths'),
                $qb->expr()->gte('a.starts', ':now')
            ))
            ->setParameter('now', new DateTime())
            ->setParameter('threeMonths', (new DateTime())->modify('+3 months'))
            ->setParameter('locations', $locations)
            ->orderBy('a.ends', 'DESC')
            ->setMaxResults($limit);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all activities around a given location.
     *
     * @param Location $location
     * @param int      $distance
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getUpcomingAroundLocationCount(Location $location, $distance = 20)
    {
        // Fetch latitude and longitude of member's location
        $latitude = $location->getLatitude();
        $longitude = $location->getLongitude();

        $edison = GeoLocation::fromDegrees($latitude, $longitude);

        try {
            $coordinates = $edison->boundingCoordinates($distance, 'km');
        } catch (\Exception $e) {
            return 0;
        }

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
            ->select('count(a.id)')
            ->where('a.location IN (:locations)')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->lte('a.ends', ':threeMonths'),
                $qb->expr()->gte('a.starts', ':now')
            ))
            ->setParameter('now', new DateTime())
            ->setParameter('threeMonths', (new DateTime())->modify('+3 months'))
            ->setParameter('locations', $locations)
            ->orderBy('a.ends', 'DESC');

        $unreadCount = 0;
        try {
            $q = $qb->getQuery();
            $unreadCount = $q->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }

        return (int) $unreadCount;
    }
}
