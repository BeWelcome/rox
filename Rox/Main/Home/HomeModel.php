<?php

namespace Rox\Main\Home;

use Rox\Models\Message;
use Rox\Models\Note;

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
     * Get array of notes
     *
     * @access public
     * @return mixed Returns an array of Note entity objects or false if you're not logged in
     */
    public function getNotes($order = false, $where = false)
    {
        $sql = '
SELECT *
FROM
    notes
';
        if (isset($where) && $where != '') $sql .= $where;
        if (isset($order) && $order != false) $sql .= '
ORDER BY '.$order.' ASC,IdMember ASC
';
        else $sql .= '
ORDER BY created DESC,IdMember ASC
';
        return $this->bulkLookup($sql);

    }

    /**
     * Find all notes I am member of
     *
     * @access public
     * @return mixed Returns an array of Note entity objects or false if you're not logged in
     */
    public function getMyNotes()
    {
        if (!isset($_SESSION['IdMember']))
        {
            return array();
        }
        else
        {
            return $this->getNotesForMember($_SESSION['IdMember']);
        }
    }

    public function getMemberNotes()
    {
        if (!isset($_SESSION['IdMember'])) {
            // not logged in - no messages
            return array();
        } else {
            $member_id = $_SESSION['IdMember'];
            return $this->getNotes(false,'WHERE notes.IdMember = '.$member_id.' AND notes.Checked = 0');
        }
    }

}