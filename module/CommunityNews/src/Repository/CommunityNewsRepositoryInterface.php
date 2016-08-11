<?php

namespace Rox\CommunityNews\Repository;

use Rox\CommunityNews\Model\CommunityNews;
use Rox\Core\Exception\NotFoundException;

interface CommunityNewsRepositoryInterface
{
    /**
     * @param $id
     *
     * @return CommunityNews
     *
     * @throws NotFoundException
     */
    public function getById($id);

    /**
     * @param int $page
     * @param int $limit
     * @return array CommunityNews
     */
    public function getAll($page = 1, $limit = 20);

    /**
     * @param $page
     * @param $limit
     * @return int count of CommunityNews
     */
    public function getAllCount($page, $limit);

    /**
     * @return CommunityNews
     */
    public function getLatest();
}
