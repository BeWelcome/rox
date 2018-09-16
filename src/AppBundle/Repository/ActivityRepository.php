<?php

namespace AppBundle\Repository;

use AnthonyMartin\GeoLocation\GeoLocation;
use AppBundle\Entity\Location;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ActivityRepository extends EntityRepository
{
    /**
     * @return Query
     */
    public function queryLatest()
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT a
                FROM AppBundle:Activity a
                ORDER BY a.id DESC
            ');
    }

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

        $locations = $this->getEntityManager()->getRepository('AppBundle:Location')
            ->matching($criteria);

        $queryBuilder = $this->createQueryBuilder('a')
//            ->where('a.starts >= :now')
//            ->andWhere('a.ends <= :now')
            ->andWhere('a.location IN (:locations)')
//            ->setParameter('now', new DateTime())
            ->setParameter('locations', $locations)
            ->orderBy('a.ends', 'DESC')
            ->setMaxResults($limit);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
