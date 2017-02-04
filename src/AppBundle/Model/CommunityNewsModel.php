<?php
/**
 * Created by PhpStorm.
 * User: saeed
 * Date: 12/22/16
 * Time: 12:36 AM
 */
namespace AppBundle\Model;

use AppBundle\Entity\CommunityNews;
use AppBundle\Repository\CommunityNewsRepository;
use Pagerfanta\Pagerfanta;

class CommunityNewsModel extends BaseModel
{
    /**
     * @return Pagerfanta
     */
    public function getLatestPaginator($page, $limit)
    {
        /** @var CommunityNewsRepository $repository */
        $repository = $this->em->getRepository(CommunityNews::class);
        return $repository->findLatest($page, $limit);
    }

    public function getLatest()
    {
        /** @var CommunityNewsRepository $repository */
        $repository = $this->em->getRepository(CommunityNews::class);
        return $repository->getLatest();
    }
}