<?php

namespace App\Repository;

use App\Entity\Wiki;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class WikiRepository extends EntityRepository
{
    /**
     * @param $pagename
     * @param mixed $version
     *
     * @return Wiki|null
     */
    public function getPageByName($pagename, $version)
    {
        try {
            $qb = $this->createQueryBuilder('w')
                ->where('w.pagename = :pagename')
                ->setParameter(':pagename', $pagename)
                ->orderBy('w.version', 'DESC')
                ->setMaxResults(1);
            if (0 !== $version) {
                $qb
                    ->andWhere('w.version = :version')
                    ->setParameter(':version', $version)
                ;
            }
            $query = $qb->getQuery();
            $wikiPage = $query->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $wikiPage = null;
        }

        return $wikiPage;
    }

    public function getHistory(Wiki $wikiPage)
    {
        $versions = $this->createQueryBuilder('w')
            ->select(['w.version', 'w.author', 'w.created'])
            ->where('w.pagename = :pagename')
            ->setParameter(':pagename', $wikiPage->getPagename())
            ->orderBy('w.version', 'DESC')
            ->getQuery()
            ->getResult();

        return $versions;
    }

    /**
     * Returns the last 300 changes to pages in the wiki.
     *
     * @return array
     */
    public function getRecentChanges()
    {
        $recentChanges = $this->createQueryBuilder('w')
            ->select(['w.pagename', 'w.version', 'w.author', 'w.created'])
            ->orderBy('w.created', 'DESC')
            ->setMaxResults(300)
            ->getQuery()
            ->getResult();

        return $recentChanges;
    }
}
