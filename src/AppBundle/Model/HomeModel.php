<?php

namespace AppBundle\Model;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class HomeModel extends BaseModel {

    /**
     * Generates messages for display on home page
     *
     * Returns either all messages or only unread ones depending on checkbox state
     *
     * Format: 'title': "Message title #1",
     *   'id': 12345,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',
     *   'read': true
     *
     * @param Member $member
     * @param $all
     * @param $unread
     * @param int|bool $limit
     * @return array
     */
    public function getMessages(Member $member, $unread, $limit = 0)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('m')
            ->from('AppBundle\Entity\Message', 'm')
            ->where('m.receiver = :member')
            ->setParameter('member', $member);
        if ($unread) {
            $queryBuilder
                ->andWhere("whenfirstread = '0000-00-00 00:00.00");
        }

        if($limit <> 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder
            ->orderBy('m.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Generates notifications for display on home page
     * Format: 'title': "Message title #1",
     *   'text': Depending on type of notification,
     *   'link': Depending on type of notification,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',
     *
     * @param Member $member
     * @param int|bool $limit
     *
     * @return array
     */
    public function getNotifications(Member $member, $limit = 0)
    {
        $member;
        $limit;
/*        $query = Note::orderBy('created', 'desc')
            ->with('notifier')
            ->where('IdMember', $member->getId())
            ->where('checked', 0)->get();
        if ($limit) {
            $query=$query->take($limit);
        }
        $notes = $query->all();
        $words = $this->getWords();

        $mappedNotes = array_map(
            function($a) use($words, $member) {
                $result = new \stdClass();
                if ($a->WordCode == '' && ($text_params = unserialize($a->TranslationParams)) !== false) {
                    $text = call_user_func_array(array($words, 'getSilent'), $text_params);
                } else {
                    $text = $words->getSilent($a->WordCode,$a->notifier->Username);
                }
                $result->title = $text;
                $result->id = $a->id;
                $result->link = $a->Link;
                $result->user = $a->notifier->Username;
                $result->time = $a->created;
                return $result;
            }, $notes
        );
        return $mappedNotes;
*/
    }

    /**
     * Generates threads for display on home page
     *
     * Depends on checkboxes shown above the display
     *
     * @param Member   $member
     * @param bool     $groups
     * @param bool     $forum
     * @param bool     $following
     * @param int|bool $limit
     *
     * @return array
     */
    public function getThreads(Member $member, $groups, $forum, $following, $limit = 0)
    {
        if ($groups + $forum + $following == 0) {
            // Member decided not to show anything
            return [];
        }

        $em = $this->em;

        // There seems to be an issue with some threads/posts missing their author.
        // To get around that, this task is split it two parts: first do the search
        // query we want using inner join on all required dependent tables, then second,
        // use the IDs from that result set to do a findMany using the ORM.

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder
            ->select('ft.id')
            ->from('forums_threads', 'ft')
            ->where("ThreadDeleted = 'NotDeleted'")
            ->orderBy('created_at', 'desc')
        ;

        $groupIds = [];
        if ($groups) {
            $groups = $member->getGroups();
            // ->get(['id']);

            $groupIds = $groups->map(
                function($item) {
                    return $item->getId();
                }
            );
        }
        if ($forum) {
            $groupIds = array_merge($groupIds->getValues() , [0]);
        }
        if ($following) {

        }
        if (!empty($groupIds)) {
            $queryBuilder
                ->andWhere('IdGroup IN (:groupIds)')
                ->setParameter('groupIds', $groupIds);
        }

        // Need to use inner join here so it also acts like an eager fetch, ie.
        // no threads will be returned if they have a missing author etc.
        $queryBuilder
            ->join('forums_posts', 'forums_posts.id', '=', 'forums_threads.last_postid')
            ->join('members', 'members.id', '=', 'forums_posts.authorid');

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        $query = $queryBuilder->getQuery();

        $posts = $query->getResult();

        return $posts;
    }

    /**
     * Generates activities (near you) for display on home page
     * Format: 'title': "Activity near you #1",
     *   'id': 12345,
     *   'day': '12',
     *   'month': '02',
     *   'year': 2016,
     *   'place': 'Lourdes',
     *   'country': 'France',
     *   'yes': 12,
     *   'no': 3
     *
     * @param Member $member
     * @param int|bool $limit
     *
     * @return array
     */
    public function getActivities(Member $member, $limit = 0)
    {
        $member; $limit;
/*        // Fetch latitude and longitude of member's location
        $latAndLong = Capsule::table('geonames')->where('geonameid', $member->city->id)->first(['latitude', 'longitude']);

        if ($latAndLong == null) {
            return [];
        }

        $distance = 200; // Fetch from preferences
        $edison = GeoLocation::fromDegrees($latAndLong->latitude, $latAndLong->longitude);
        $coordinates = $edison->boundingCoordinates($distance, 'km');

        $result = new stdClass;
        $result->latne = $coordinates[0]->getLatitudeInDegrees();
        $result->longne = $coordinates[0]->getLongitudeInDegrees();
        $result->latsw = $coordinates[1]->getLatitudeInDegrees();
        $result->longsw = $coordinates[1]->getLongitudeInDegrees();

        $query = Capsule::table('activities')->join('geonames', function($join) use($result) {
            $join->on('activities.locationId', '=', 'geonames.geonameId')
                ->where('latitude', '<=', $result->latsw)
                ->where('latitude', '>=', $result->latne)
                ->where('longitude', '<=', $result->longsw)
                ->where('longitude', '>=', $result->longne);
        })
        ->where(function ($query) {
            $query->where('dateTimeStart', '>=', Capsule::raw('NOW()'))
                ->orWhere('dateTimeEnd', '>=', Capsule::raw('NOW()'));
        })
        ->where('status', 0)
        ->take($limit);

        $activityIds = $query->get(['id']);

        $ids = array_map(function($a) { return $a->id; }, $activityIds);

        $activities = Activity::with(['location', 'attendees'])->whereIn('id', $ids)->get();

        $mappedActivities = [];
        foreach($activities as $activity) {
            $mappedActivity = new stdClass;
            $mappedActivity->id = $activity->id;
            $mappedActivity->title = $activity->title;
            $mappedActivity->start = $activity->dateTimeStart;
            $location = $activity->location;
            $mappedActivity->place = $location->name;
            $mappedActivity->country = $location->Country->name;
            $mappedActivity->yes = $activity->attendees()->where('activitiesattendees.status', 1)->count();
            $mappedActivity->maybe = $activity->attendees()->where('activitiesattendees.status', 2)->count();
            $mappedActivities[] = $mappedActivity;
        }
        return $mappedActivities;
 */   }

    public function getMemberDetails() {
/*        $loggedInMember = $this->getLoggedInMember();
        $location = Capsule::table('geonames')->where('geonameId', $loggedInMember->IdCity)->first(['name']);
        return ['member' =>
            [
                'location' => $location->name,
                'hosting' => $loggedInMember->Accomodation
            ]
        ];
*/    }

    public function getDonationCampaignDetails() {
    }

    /**
     * @param Member $member
     * @return array|bool
     */
    public function getTravellersInAreaOfMember( Member $member)
    {
        $member;
/*        $travellers = false;
        $trip = new Trip();
        $trips = $trip->findInMemberAreaNextThreeMonths( $member );
        if($trips) {
            foreach($trips as $t) {
                $traveller = new \stdClass;
                $traveller->Username = $t->createdBy->Username;
                $traveller->arrives = $t->subtrips[0]->arrival;
                $traveller->leaves = $t->subtrips[0]->departure ? $t->subtrips[0]->departure : $t->subtrips[0]->arrival;
                $traveller->livesIn = $t->createdBy->city;
                $travellers[] = $traveller;
            }
        }
        return $travellers;
*/    }
}
