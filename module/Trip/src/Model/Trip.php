<?php

namespace Rox\Trip\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rox\Geo\Model\Location;
use Rox\Trip\Repository\TripRepositoryInterface;
use Rox\Core\Exception\InvalidArgumentException;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Model\AbstractModel;
use Rox\Member\Model\Member;

/**
 * @property int $id
 * @property-read Member $receiver
 */
class Trip extends AbstractModel
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $ormRelationships = [
        'createdBy',
        'subtrips',
    ];

    public function createdBy()
    {
        return $this->hasOne(Member::class, 'id', 'created_by');
    }

    /**
     * Get the subtrips of this trip.
     */
    public function subtrips()
    {
        return $this->hasMany('Rox\Trip\Model\SubTrip');
    }

    public function getById($id)
    {
        $communityNews = $this
            ->with(['createdBy'])
            ->where('id', $id)
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
        $communityNews->with('createdBy');
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
            ->with(['createdBy'])
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

        $trip = $this
            ->with(['createdBy'])
            ->limit($count)
            ->orderBy('id', 'desc');

        if ($count === 1) {
            return $trip->first();
        }

        return $trip->get();
    }

    /**
     * @param Member $member
     * @param int $count
     * @param int $distance
     * @return array Trip
     */
    public function findInMemberAreaNextThreeMonths( Member $member, $count = 2, $distance = 25)
    {
        $location = new Location();
        $locationIds = $location->getLocationIdsAroundLocation($member->latitude, $member->longitude, $distance);
        $subtripsFilter = function($q) use($locationIds) {
            $q->where('arrival', '>=', Capsule::raw('CURDATE()'))
//                ->where('arrival', '<=', Capsule::raw('DATE_ADD(CURDATE, INTERVAL 3 MONTH'))
                ->whereIn('geonameId', $locationIds);
        };

        $trips = $this->where('created_by', '<>', $member->id)
            ->join('members', 'members.id', '=', 'trips.created_by')
            ->whereHas('subtrips' , $subtripsFilter )
//            ->with(['createdBy', 'subtrips'])
            ->with(['subtrips' => $subtripsFilter])
            ->whereIn('members.status', ['Active', 'OutOfRemind'])
            ->get()
            ->take($count);

        return $trips;
    }
}
