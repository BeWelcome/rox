<?php

namespace Rox\CommunityNews\Service;

use Rox\CommunityNews\Model\CommunityNews;

class CommunityNewsService implements CommunityNewsServiceInterface
{
    public function getAllCommunityNews()
    {
        $communityNews = new CommunityNews();

        return $communityNews->getAll();
    }
}
