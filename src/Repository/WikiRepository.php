<?php

namespace App\Repository;

use App\Entity\Wiki;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class WikiRepository extends EntityRepository
{
    /**
     * @param $pagename
     *
     * @return Wiki|null
     */
    public function getPageByName($pagename)
    {
        $wikiPage = null;

        try {
            $wikiPage = $this->createQueryBuilder('w')
                ->where('w.pagename = :pagename')
                ->setParameter(':pagename', $pagename)
                ->orderBy('w.version', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
        }

        return $wikiPage;
    }
}
