<?php
/**
 * Created by PhpStorm.
 * User: saeed
 * Date: 12/22/16
 * Time: 12:36 AM.
 */

namespace AppBundle\Model;

use AppBundle\Entity\CommunityNews;
use AppBundle\Repository\CommunityNewsRepository;
use Pagerfanta\Pagerfanta;

class CommunityNewsModel extends BaseModel
{
    /**
     * @param mixed $page
     * @param mixed $limit
     *
     * @param bool $publicOnly
     * @return Pagerfanta
     */
    public function getLatestPaginator($page, $limit, $publicOnly = true)
    {
        /** @var CommunityNewsRepository $repository */
        $repository = $this->em->getRepository(CommunityNews::class);

        return $repository->findLatest($page, $limit, $publicOnly);
    }

    public function getLatest()
    {
        /** @var CommunityNewsRepository $repository */
        $repository = $this->em->getRepository(CommunityNews::class);

        return $repository->getLatest();
    }
}
