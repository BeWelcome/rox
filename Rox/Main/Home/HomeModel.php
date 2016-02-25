<?php

namespace Rox\Main\Home;

use AnthonyMartin\GeoLocation\GeoLocation;
use Rox\Models\Activity;
use Rox\Models\Member;
use Rox\Models\Message;
use Rox\Models\Note;
use Rox\Models\Post;
use Rox\Models\Thread;
use Illuminate\Database\Capsule\Manager as Capsule;
use stdClass;

class HomeModel extends \RoxModelBase {

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
     * @param int|bool $limit
     *
     * @return array
     */
    public function getMessages($all, $unread, $limit = false)
    {
        $member = $this->getLoggedInMember();
        $query = Message::orderBy('created', 'desc')->with('sender')->where('IdReceiver', $member->id);
        if ($unread) {
            $query= $query->where('WhenFirstRead', '0000-00-00 00:00:00');
        }
        if ($limit) {
            $query=$query->take($limit);
        }
        $messages = $query->get()->all();

        $mappedMessages = array_map(
            function($a) {
                $result = new \stdClass();
                $result->title = strip_tags($a->Message);
                $result->id = $a->id;
                $result->user = $a->sender->Username;
                $result->time = $a->created;
                $result->read = ($a->WhenFirstRead != '0000-00-00 00:00:00');
                return $result;
            }, $messages
        );
        return $mappedMessages;
    }

    /**
     * Generates notifications for display on home page
     * Format: 'title': "Message title #1",
     *   'text': Depending on type of notification,
     *   'link': Depending on type of notification,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',
     *
     * @param int|bool $limit
     *
     * @return array
     */
    public function getNotifications($limit = false)
    {
        $member = $this->getLoggedInMember();
        $query = Note::orderBy('created', 'desc')
            ->with('notifier')
            ->where('IdMember', $member->id)
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
    }

    /**
     * Generates threads for display on home page
     * 
     * Depends on checkboxes shown above the display
     *
     * @param bool     $groups
     * @param bool     $forum
     * @param bool     $following
     * @param int|bool $limit
     *
     * @return array
     */
    public function getThreads($groups, $forum, $following, $limit = false)
    {
        if ($groups + $forum + $following == 0) {
            // Member decided not to show anything
            return [];
        }
        Capsule::enableQueryLog();
        $query = Thread::orderBy('created_at', 'desc')
            ->where('ThreadDeleted', 'NotDeleted');
        $groupIds = [];
        if ($groups) {
            $loggedInMember = $this->getLoggedInMember();
            $member = Member::where('id', $loggedInMember->id)->first();;
            $groups = $member->groups()->get(['groups.id']);
            $groupIds = $groups->map(
                function($item, $key) {
                    return $item->id;
                }
            )->all();
        }
        if ($forum) {
            $groupIds = array_merge($groupIds , [0]);
        }
        if ($following) {

        }
        if (!empty($groupIds)) {
            $query = $query->whereIn('IdGroup', $groupIds);
            if ($following) {
                $query = $query->orWhereIn('id', [1, 2]);
            }
        } else {
            $query = $query->whereIn('id', [1, 2]);
        }
        if ($limit) {
            $query=$query->take($limit);
        }
        $posts = $query->get()->all();

        error_log(print_r(Capsule::getQueryLog(), true));
        $mappedPosts = array_map(
            function($a) {
                $result = new \stdClass();
                $result->title = $a->title;
                $result->id = $a->id;
                $result->lastuser = $a->lastPost->author->Username;
                $result->time = $a->created_at;
                return $result;
            }, $posts
        );
        return $mappedPosts;
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
     * @param int|bool $limit
     *
     * @return array
     */
    public function getActivities($limit = false)
    {
        $loggedInMember = $this->getLoggedInMember();
        // Fetch latitude and longitude of member's location
        $latAndLong = Capsule::table('geonames')->where('geonameid', $loggedInMember->IdCity)->first(['latitude', 'longitude']);

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
        ->where('status', 0);

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
    }

    public function getMemberDetails() {
        $loggedInMember = $this->getLoggedInMember();
        $location = Capsule::table('geonames')->where('geonameId', $loggedInMember->IdCity)->first(['name']);
        return ['member' =>
            [
                'location' => $location->name,
                'hosting' => $loggedInMember->Accomodation
            ]
        ];
    }
}