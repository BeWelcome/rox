<?php

namespace Rox\CommunityNews\Service;

use Illuminate\Database\Eloquent\Builder;

interface CommunityNewsServiceInterface
{
    /**
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function getAllCommunityNews();
}
