<?php

namespace Rox\CommunityNews\Model;

use Rox\CommunityNews\Repository\CommunityNewsRepositoryInterface;
use Rox\Core\Exception\InvalidArgumentException;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Model\AbstractModel;
use Rox\Member\Model\Member;

/**
 * @property int $id
 * @property-read Member $receiver
 */
class CommunityNews extends AbstractModel implements CommunityNewsRepositoryInterface
{
    /**
     * @var string
     */
    public $table = 'community_news';

    /**
     * @var array
     */
    protected $ormRelationships = [
        'creator',
        'updater',
        'deleter',
        'comments',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function creator()
    {
        return $this->hasOne(Member::class, 'id', 'created_by');
    }

    public function updater()
    {
        return $this->hasOne(Member::class, 'id', 'updated_by');
    }

    public function deleter()
    {
        return $this->hasOne(Member::class, 'id', 'deleted_by');
    }

    public function getById($id)
    {
        $communityNews = $this->newQuery()
            ->with(['creator', 'updater', 'deleter'])
            ->where('Id', $id)->first();

        if (!$communityNews) {
            throw new NotFoundException();
        }

        return $communityNews;
    }

    /**
     * @return array of CommunityModel
     */
    public function getAll()
    {
        return $this->newQuery()
            ->with(['creator', 'updater', 'deleter'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    /**
     * @param int $count Determines how many community news shall be returned
     * @return mixed
     */
    public function getLatest($count = 1)
    {
        if ($count < 1) {
            throw new InvalidArgumentException('Count must be at least 1');
        }

        $communityNews = $this->newQuery()
            ->with(['creator', 'updater', 'deleter'])
            ->limit($count)->orderBy('created_at', 'desc');

        if ($count === 1) {
            return $communityNews->first();
        }

        return $communityNews->get();
    }
}
