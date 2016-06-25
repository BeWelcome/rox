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
     * @return array CommunityNews
     */
    public function getAll();

    /**
     * @return CommunityNews
     */
    public function getLatest();
}
