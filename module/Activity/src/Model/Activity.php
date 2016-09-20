<?php

namespace Rox\Activity\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Rox\Activity\Repository\ActivityRepositoryInterface;
use Rox\Core\Exception\InvalidArgumentException;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Model\AbstractModel;
use Rox\Member\Model\Member;

/**
 * @property int $id
 * @property-read Member $receiver
 */
class Activity extends AbstractModel implements ActivityRepositoryInterface
{
    use SoftDeletes;

    const DELETED_AT = 'deleted_at';

    /**
     * @var string
     */
    public $table = 'activities';

    /**
     * @var array
     */
    protected $ormRelationships = [
        'creator',
        'updater',
        'deleter',
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
        $communityNews = $this
            ->with(['creator', 'updater', 'deleter'])
            ->where('Id', $id)
            ->first();

        if (!$communityNews) {
            throw new NotFoundException();
        }

        return $communityNews;
    }
    /**
     * @param int $page
     * @param int $limit
     * @return array Containing the found community news and the overall count
     */
    public function getAll($page = 1, $limit = 20)
    {
        $communityNews = $this->newQuery();
        $communityNews->with('creator', 'updater', 'deleter');
        $communityNews->orderBy('updated_at', 'desc');
        $communityNews->getQuery()->forPage($page, $limit);

        $count = $communityNews->getQuery()->getCountForPagination();

        return [$communityNews->get()->all(), $count];
    }

    public function getAllCount($page, $limit)
    {
        return $this
            ->getAllQuery($page, $limit)
            ->getCountForPagination();
    }

    /**
     * @return array of CommunityModel
     */
    public function getAllIncludingDeleted()
    {
        return $this
            ->withTrashed()
            ->with(['creator', 'updater', 'deleter'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    /**
     * @param int $count Determines how many community news shall be returned
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getLatest($count = 1)
    {
        if ($count < 1) {
            throw new InvalidArgumentException('Count must be at least 1');
        }

        $activity = $this
            ->with(['creator', 'updater', 'deleter'])
            ->limit($count)
            ->orderBy('created_at', 'desc');

        if ($count === 1) {
            return $activity->first();
        }

        return $activity->get();
    }
}
