<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Trip;
use App\Repository\TripRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class TripModel extends BaseModel
{
    /**
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatest($page, $items)
    {
        /** @var TripRepository $repository */
        $repository = $this->em->getRepository(Trip::class);
        $query = $repository->queryLatest();

        $paginator = new Pagerfanta(new DoctrineORMAdapter($query, false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    public function findInMemberAreaNextThreeMonths(Member $member, $count = 2, $distance = 25)
    {
        $location = new LocationModel($this->em);
        $locationIds = $location->getLocationIdsAroundLocation(
            $member->getLatitude(),
            $member->getLongitude(),
            $distance
        );
        $geonameIds = array_map(function ($item) {
            return $item->getGeonameId();
        }, $locationIds);
        $geoNameIds = implode(',', $geonameIds);
        $sql = "
            select * from trips
            join members ON members.id = trips.created_by
            inner join sub_trips st ON st.trip_id = trips.id 
            where 
              created_by <> {$member->getId()}
              AND st.arrival >= CURDATE() AND st.geonameId IN ({$geoNameIds})
              AND members.status IN ('Active','OutOfremind')
              LIMIT $count
        ";
        $trips = $this->execQuery($sql)->fetchAll(\PDO::FETCH_OBJ);

        return $trips;
    }
}
