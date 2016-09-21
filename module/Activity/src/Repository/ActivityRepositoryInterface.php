<?php

namespace Rox\Activity\Repository;

use Rox\Activity\Model\Activity;
use Rox\Core\Exception\NotFoundException;

interface ActivityRepositoryInterface
{
    /**
     * @param $id
     *
     * @return Activity
     *
     * @throws NotFoundException
     */
    public function getById($id);

    /**
     * @param int $page
     * @param int $limit
     * @return array Activity
     */
    public function getAll($page = 1, $limit = 20);

    /**
     * @param $page
     * @param $limit
     * @return int count of CommunityNews
     */
    public function getAllCount($page, $limit);

    /**
     * @return Activity
     */
    public function getLatest();
}
