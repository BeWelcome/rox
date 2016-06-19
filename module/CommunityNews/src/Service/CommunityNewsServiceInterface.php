<?php

namespace Rox\CommunityNews\Service;

use Illuminate\Database\Eloquent\Builder;
use Rox\Member\Model\Member;
use Rox\CommunityNews\Model\CommunityNews;

interface CommunityNewsServiceInterface
{
    /**
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function getAllCommunityNews();
}
