<?php

namespace Rox\Main\Home;

use Rox\Models\Message;
use Rox\Models\Note;
use Rox\Models\Post;
use Rox\Models\Thread;

class HomeModel extends \RoxModelBase {

    /**
     * Generates messages for display on home page
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
    public function getMessages($limit = false)
    {
        $member = $this->getLoggedInMember();
        $query = Message::orderBy('created', 'desc')->with('sender')->where('IdReceiver', $member->id)->get();
        if ($limit) {
            $query=$query->take($limit);
        }
        $messages = $query->all();

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
     * Generates notifications for display on home page
     * Format: 'title': "Message title #1",
     *   'text': Depending on type of notification,
     *   'link': Depending on type of notification,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',
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
        $query = Thread::orderBy('created_at', 'desc')
            ->where('ThreadDeleted', 'NotDeleted');
        if ($groups && $forum) {
            $query = $query->where(function ($query) {
                $query->whereIn('IdGroup', [70])
                    ->orWhere('IdGroup', 0);
            });
        } else {
            if ($groups) {
                $query = $query->whereIn('IdGroup', [70]);
            }
            if ($forum) {
                $query = $query->where('IdGroup', 0);
            }
        }
        if ($following) {
            $query = $query->orWhereIn('id', [1, 2]);
        }
        if ($limit) {
            $query=$query->take($limit);
        }
        $posts = $query->get()->all();

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
}