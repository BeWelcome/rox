<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class ProfileVisitRepository extends EntityRepository
{
    public function getProfileVisitorsMember(Member $member, int $page): Pagerfanta
    {
        $profileVisitors = $this->findby(['member' => $member], ['updated' => 'DESC']);

        $paginator = new Pagerfanta(new ArrayAdapter($profileVisitors));
        $paginator->setMaxPerPage(20);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
