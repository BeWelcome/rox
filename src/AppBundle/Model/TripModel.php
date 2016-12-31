<?php

namespace AppBundle\Model;


use AppBundle\Entity\Trip;
use AppBundle\Repository\TripRepository;
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
}