<?php
/*

Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/

    /**
     * @author Felix van Hove <fvanhove@gmx.de>
     * @author Fake51
     */

    /**
     * admin model
     *
     * @package Apps
     * @subpackage Admin
     */
class AdminModel extends RoxModelBase
{

//{{{ accepter stuff
    /**
     * gets an array of members with a given status
     *
     * @param stsring $status
     * @access public
     * @return array
     */
    public function getMembersWithStatus($status, $pager)
    {
        $offset = ($pager->active_page - 1) * $pager->items_per_page;
        return $this->createEntity('Member')->findByWhereMany("Status = '{$this->dao->escape($status)}' ORDER BY id LIMIT {$offset}, {$pager->items_per_page}");
    }

    /**
     * counts members with a given status
     *
     * @param stsring $status
     * @access public
     * @return int
     */
    public function countMembersWithStatus($status)
    {
        return $this->createEntity('Member')->countWhere("Status = '{$this->dao->escape($status)}'");
    }

    public function getStatusOverview()
    {
        $result = array();
        $query = "SELECT status, count(id) AS count FROM members GROUP BY status ORDER BY status";
        if ($results = $this->dao->query($query))
        {
            while ($row = $results->fetch(PDB::FETCH_OBJ))
            {
                $result[$row->status] = $row->count;
            }
        }
        return $result;
    }

    /**
     * updates member statuses according to a post array
     *
     * @param array $post
     *
     * @access public
     * @return array
     */
    public function processMembers(array $post)
    {
        if (empty($post) || empty($post['accept_action']))
        {
            return false;
        }
        $result = array(
            'accepted'  => 0,
            'rejected'  => 0,
            'duplicate' => 0,
            'needmore'  => 0,
            'errors'    => array(),
        );
        foreach ($post['accept_action'] as $id => $action)
        {
            if (!($member = $this->createEntity('Member')->findById($id)))
            {
                continue;
            }
            switch (strtolower($action))
            {
                case 'accept':
                    $member->Status = 'Active';
                    if (!$this->sendAcceptedEmail($member) || !$member->update())
                    {
                        $result['errors'][] = array('id' => $member->id, 'action' => 'accept');
                        $this->logWrite("Accepting of {$member->Username} - {$member->id} failed. Accepter: {$this->getLoggedInMember()->Username}", 'bug');
                    }
                    else
                    {
                        $result['accepted'] += 1;
                        $this->logWrite("{$member->Username} - {$member->id} was accepted. Accepter: {$this->getLoggedInMember()->Username}", 'admin');
                    }
                    break;
                case 'reject':
                    $member->Status = 'Rejected';
                    if (!$this->sendRejectedEmail($member) || !$member->update())
                    {
                        $result['errors'][] = array('id' => $member->id, 'action' => 'reject');
                        $this->logWrite("Rejection of {$member->Username} - {$member->id} failed. Accepter: {$this->getLoggedInMember()->Username}", 'bug');
                    }
                    else
                    {
                        $result['rejected'] += 1;
                        $this->logWrite("{$member->Username} - {$member->id} was rejected. Accepter: {$this->getLoggedInMember()->Username}", 'admin');
                    }
                    break;
                case 'needmore':
                    $member->Status = 'NeedMore';
                    if (!$this->sendNeedmoreEmail($member) || !$member->update())
                    {
                        $result['errors'][] = array('id' => $member->id, 'action' => 'needmore');
                        $this->logWrite("Update of {$member->Username} - {$member->id} to Needmore status failed. Accepter: {$this->getLoggedInMember()->Username}", 'bug');
                    }
                    else
                    {
                        $result['needmore'] += 1;
                        $this->logWrite("{$member->Username} - {$member->id} set as Needmore. Accepter: {$this->getLoggedInMember()->Username}", 'admin');
                    }
                    break;
                case 'duplicated':
                    $member->Status = 'DuplicateSigned';
                    if (!$member->Update())
                    {
                        $result['errors'][] = array('id' => $member->id, 'action' => 'duplicate');
                        $this->logWrite("Setting {$member->Username} - {$member->id} as duplicate failed. Accepter: {$this->getLoggedInMember()->Username}", 'bug');
                    }
                    else
                    {
                        $result['duplicate'] += 1;
                        $this->logWrite("{$member->Username} - {$member->id} set as duplicate. Accepter: {$this->getLoggedInMember()->Username}", 'admin');
                    }
            }
        }
        return $result;
    }

    /**
     * sends out an email containing a welcoming message
     * to a newly accepted member
     *
     * @param Member $member - member to welcome
     *
     * @access private
     * @return bool
     */
    private function sendAcceptedEmail(Member $member)
    {
        return $this->sendEmailTemplate($member, 'SignupAccepted');
    }

    /**
     * sends out an email containing a nasty message
     * to rejected members
     *
     * @param Member $member - member to welcome
     *
     * @access private
     * @return bool
     */
    private function sendRejectedEmail(Member $member)
    {
        return $this->sendEmailTemplate($member, 'SignupRejected');
    }

    /**
     * sends out an email to new signups stating
     * they forgot to fill in details
     *
     * @param Member $member - member to welcome
     *
     * @access private
     * @return bool
     */
    private function sendNeedmoreEmail(Member $member)
    {
        return $this->sendEmailTemplate($member, 'SignupNeedmore');
    }

    /**
     * boilerplate to instantiate an email template and send it
     *
     * @param Member $member   - member to welcome
     * @param string $template - template to use
     *
     * @access private
     * @return bool
     */
    private function sendEmailTemplate(Member $member, $template)
    {
        $email = new EmailTemplate($template);
        if (!$email->init(array('member' => $member)))
        {
            return false;
        }
        return $email->send();
    }
//}}}

//{{{ volunteer board
    /**
     * returns the text glob object for accepters
     *
     * @access public
     * @return VolunteerBoard
     */
    public function getAccepterBoard()
    {
        return $this->getBoard('Accepters_board');
    }

    /**
     * returns a board given it's name
     *
     * @param string $name
     *
     * @access public
     * @return VolunteerBoard
     */
    public function getBoard($name)
    {
        return $this->createEntity('VolunteerBoard')->findByName($name);
    }

    /**
     * updates a given volunteer board
     *
     * @param string $boardname - name of board to update
     * @param string $text      - text to update with
     *
     * @access public
     * @return bool
     */
    public function updateVolunteerBoard($boardname, $text)
    {
        if (!($board = $this->createEntity('VolunteerBoard')->findByName($boardname)) || !($member = $this->getLoggedInMember()))
        {
            return false;
        }
        return $board->updateText($text, $member);
    }

//}}}

//{{{ comments
    /**
     * returns all comments marked bad
     *
     * @access public
     * @return array
     */
    public function getBadComments()
    {
        return $this->createEntity('Comment')->findByWhereMany("AdminAction NOT IN ('NothingNeeded', 'Checked')");
    }
//}}}

    public function procActivitylogs($vars, $level = 0)
    {

		$where = '';
		$username = $vars["username"];

		$cid = $this->_idMember($username);
		if ($level <= 1) {
			$cid = $_SESSION["IdMember"]; // Member with level 1 can only see his own rights
		}
		if ($cid != 0) {
			$where .= " AND IdMember=" . $cid;
		}

		$R = MOD_right::get();
		$level = $R->hasRight('Logs');


		$limitcount=$vars["limitcount"]; // Number of records per page
		$start_rec=$vars["start_rec"]; // Number of records per page


		$andS1 = $vars["andS1"];
		if ($andS1 != "") {
			$where .= " AND Str LIKE '%" . $andS1 . "%'";
		}

		$andS2 = $vars["andS2"];
		if ($andS2 != "") {
			$where .= " AND Str LIKE '%" . $andS2 . "%'";
		}

		$notAndS1 = $vars["notAndS1"];
		if ($notAndS1 != "") {
			$where .= " AND Str NOT LIKE '%" . $notAndS1 . "%'";
		}

		$notAndS2 = $vars["notAndS2"];
		if ($notAndS2 != "") {
			$where .= " AND Str NOT LIKE '%" . $notAndS2 . "%'";
		}

		$ip = $vars["ip"];
		if ($ip != "") {
			$where .= " AND IpAddress=" . ip2long($ip) . "";
		}

		$type = $vars["type"];
		if ($type != "") {
			$where .= " AND Type='" . $type . "'";
		}

		// If there is a Scope limit logs to the type in this Scope (unless it his own logs)
		if (!$R->hasRight('Logs', "\"All\"")) {
			$scope = RightScope("Logs");
			str_replace($scope, "\"", "'");
			$where .= " AND (Type IN (" . $scope . ") OR IdMember=" . $_SESSION["IdMember"] . ") ";
		}

		$tData = array ();
		$db = "";
		if (!empty($_SYSHCVOL['ARCH_DB'])) {
		    $db = $_SYSHCVOL['ARCH_DB'] . ".";
		}

		// not using: SQL_CALC_FOUND_ROWS and FOUND_ROWS()
		$query = "SELECT logs.*, Username " .
		        "FROM " . $db . ".logs LEFT JOIN members ON members.id=logs.IdMember " .
		        "WHERE 1=1 " . $where . " " .
		        "ORDER BY created DESC LIMIT $start_rec," . $limitcount;
		$resultRecords = $this->dao->query($query);

		$query = "SELECT COUNT(*) AS n " .
		        "FROM " . $db . ".logs LEFT JOIN members ON members.id=logs.IdMember " .
		        "WHERE 1=1 " . $where;
		$result = $this->dao->query($query);
		$altogether = $result->fetch(PDB::FETCH_OBJ);

		return array($altogether->n => $resultRecords);
    }

    /**
     * returns the broadcast messages in the database
     * including information about the current state
     * (number of enqueued, triggered and sent messages)
     *
     * @return array of broadcast messages
     */
    public function getMassMailings($specific = false, $general = false) {
        $query = "
            SELECT
                b.*,
                IFNULL(enqueued.total, 0) enqueuedCount,
                IFNULL(triggered.total, 0) triggeredCount,
                IFNULL(sent.total, 0) sentCount,
                IFNULL(failed.total, 0) failedCount
            FROM
                broadcast b
            LEFT JOIN (
                SELECT
                    COUNT(*) total, IdBroadcast
                FROM
                    broadcastmessages
                WHERE
                    Status = 'ToApprove'
                GROUP BY
                    IdBroadcast
                ) enqueued
            ON
                (enqueued.IdBroadcast = b.id)
            LEFT JOIN (
                SELECT
                    COUNT(*) total, IdBroadcast
                FROM
                    broadcastmessages
                WHERE
                    Status = 'ToSend'
                GROUP BY
                    IdBroadcast
                ) triggered
            ON
                (triggered.IdBroadcast = b.id)
            LEFT JOIN (
                SELECT
                    COUNT(*) total, IdBroadcast
                FROM
                    broadcastmessages
                WHERE
                    Status = 'Sent'
                GROUP BY
                    IdBroadcast
                ) sent
            ON
                (sent.IdBroadcast = b.id)
            LEFT JOIN (
                SELECT
                    COUNT(*) total, IdBroadcast
                FROM
                    broadcastmessages
                WHERE
                    Status = 'Failed'
                GROUP BY
                    IdBroadcast
                ) failed
            ON
                (failed.IdBroadcast = b.id)";
        if (!($specific && $general)) {
            if ($specific) {
                $query .= " WHERE Type = 'Specific'";
            }
            if ($general) {
                $query .= " WHERE Type = 'Normal'";
            }
        }
        $query .= " ORDER BY Created DESC";
        return $this->BulkLookup($query);
    }

    public function getMassmail($id) {
        $query = "
            SELECT
                Name,
                Type
            FROM
                broadcast
            WHERE
                id = " . $id;
        $broadcast = $this->SingleLookup($query);
        $query = "
            SELECT
                w1.Sentence AS Subject,
                w2.Sentence AS Body,
                w2.Description AS Description
            FROM
                words AS w1,
                words AS w2
            WHERE
                w1.Code = 'BroadCast_Title_" . $this->dao->escape($broadcast->Name) . "'
                AND w1.IdLanguage = 0
                AND w2.Code = 'BroadCast_Body_" . $this->dao->escape($broadcast->Name) . "'
                AND w2.IdLanguage = 0";
        $entry = $this->SingleLookup($query);
        $ret = new StdClass;
        $ret->Name = $broadcast->Name;
        $ret->Type = $broadcast->Type;
        $ret->Subject = $entry->Subject;
        $ret->Body = $entry->Body;
        $ret->Description = $entry->Description;

        // translations
        $query = "
            SELECT
                languages.ShortCode AS ShortCode,
                languages.Name AS Name
            FROM
                languages, words
            WHERE words.code='BroadCast_Body_" . $broadcast->Name . "'
            AND languages.id=words.IdLanguage";
        $ret->Languages = $this->BulkLookup($query);

        // Make sure all counts are set (to avoid isset)
        $ret->ToApprove = $ret->ToSend = $ret->Sent = $ret->Failed = 0;
        $query = "
            SELECT
                `Status`, COUNT(*) AS Count
            FROM
                broadcastmessages
            WHERE
                IdBroadcast = " . $id . "
            GROUP BY
                `Status`";
        $counts = $this->BulkLookup($query);
        foreach($counts as $count) {
            $status = $count->Status;
            $ret->$status = $count->Count;
        }
        return $ret;
    }

    public function getMassmailRecipientsInfo($id, $type, $start, $limit) {
        $status = "Unknown";
        switch($type) {
            case 'enqueued': $status = "ToApprove"; break;
            case 'triggered': $status = "ToSend"; break;
            case 'sent': $status = "Sent"; break;
            case 'failed': $status = "Failed"; break;
        }
        $query = "
            SELECT
                m.Id AS Id, m.Username AS Username, gc.Name AS Country, m.Status AS Status
            FROM
                broadcastmessages AS bm, members AS m, geonames_cache AS g, geonames_countries AS gc
            WHERE
                bm.IdBroadcast = " . $id . "
                AND bm.Status = '" . $status . "'
                AND bm.IdReceiver = m.id
                AND g.geonameid = m.IdCity
                AND gc.iso_alpha2 = g.fk_countrycode
            LIMIT " . $start . "," . $limit;
        $mmris = $this->BulkLookup($query);
        // Now get email address and preferred language of the found members
        foreach($mmris as &$mmri) {
            $m = $this->createEntity('Member', $mmri->Id);
            $mmri->LanguageId = $m->getLanguagePreferenceId();
            $mmri->Email = $m->get_email();
        }

        // get language names from DB
        $languages = array();
        $languagesnames = array();
        foreach($mmris as $mm) {
            $languages[] = $mm->LanguageId;
        }

        $query = "SELECT id, EnglishName FROM languages WHERE id IN ('" . implode("', '", $languages) . "')";
        $r = $this->dao->query($query);
        while ($row = $r->fetch(PDB::FETCH_OBJ)) {
            $languagesnames[$row->id] = $row->EnglishName;
        }

        foreach($mmris as &$mmri) {
            $mmri->Language = $languagesnames[$mmri->LanguageId];
        }

        return $mmris;
    }

    public function getMassmailRecipientsCount($id, $type) {
        $status = "Unknown";
        switch($type) {
            case 'enqueued': $status = "ToApprove"; break;
            case 'triggered': $status = "ToSend"; break;
            case 'sent': $status = "Sent"; break;
            case 'failed': $status = "Failed"; break;
        }
        $query = "
            SELECT
                COUNT(*) AS count
            FROM
                broadcastmessages AS bm
            WHERE
                bm.IdBroadcast = " . $id . "
                AND bm.Status = '" . $status . "'";
        $r = $this->SingleLookup($query);
        if (!$r) {
            return -1;
        }
        return $r->count;
    }

    public function createMassmail($name, $type, $subject, $body, $description) {
        $name = $this->dao->escape($name);
        // first create entry in the broadcast table
        $query = "
            INSERT INTO
                broadcast
            SET 
                Type = '" . $type . "',
                Name = '" . $name . "',
                created = NOW(),
                Status = 'Created',
                IdCreator = ". $_SESSION["IdMember"];
        $this->dao->query($query);

        $query = "
            INSERT INTO
                words
            SET
                code = 'Broadcast_Title_" . $name . "',
                ShortCode = 'en',
                IdLanguage = 0,
                Sentence = '" . $this->dao->escape($subject) . "',
                updated = NOW(),
                IdMember = " . $this->getLoggedInMember()->id . ",
                Description = '" . $this->dao->escape($description) . "'";
        $this->dao->query($query);

        $query = "
            INSERT INTO
                words
            SET
                code = 'Broadcast_Body_" . $name . "',
                ShortCode = 'en',
                IdLanguage = 0,
                Sentence = '" . $this->dao->escape($body) . "',
                updated = NOW(),
                IdMember = " . $this->getLoggedInMember()->id . ",
                Description = '" . $this->dao->escape($description) . "'";
        $this->dao->query($query);
    }

    public function updateMassmail($id, $name, $type, $subject, $body) {
        $query = "
            UPDATE
                broadcast
            SET
                created = NOW(),
                IdCreator = ". $_SESSION["IdMember"] . ",
                Type = '" . $type . "'
            WHERE
                id = " . $id;
        $this->dao->query($query);

        $query = "
            UPDATE
                words
            SET
                Sentence = '" . $this->dao->escape($subject) . "',
                updated = NOW(),
                IdMember = " . $this->getLoggedInMember()->id . "
            WHERE
                code = 'Broadcast_Title_" . $name . "'
                AND ShortCode = 'en'
                AND IdLanguage = 0";
        $this->dao->query($query);

        $query = "
            UPDATE
                words
            SET
                Sentence = '" . $this->dao->escape($body) . "',
                updated = NOW(),
                IdMember = " . $this->getLoggedInMember()->id . "
            WHERE
                code = 'Broadcast_Body_" . $name . "'
                AND ShortCode = 'en'
                AND IdLanguage = 0";
        $this->dao->query($query);
    }

    public function massmailEditCreateVarsOk(&$vars) {
        $id = $vars['Id'];
        $name = $vars['Name'];
        $subject = $vars['Subject'];
        $body = $vars['Body'];
        $description = $vars['Description'];
        $errors = array();
        if (empty($name)) {
            $errors[] = 'AdminMassMailNameEmpty';
        }
        if (empty($subject)) {
            $errors[] = 'AdminMassMailSubjectEmpty';
        }
        if (empty($body)) {
            $errors[] = 'AdminMassMailBodyEmpty';
        }
        if (empty($description)) {
            $errors[] = 'AdminMassMailDescriptionEmpty';
        }

        // if $id = 0 check if a word code for $name already exists
        if ($id == 0) {
            $words = new MOD_words();
            $subject = 'BroadCast_Title_' . $name;
            $body = 'BroadCast_Body_' . $name;
            $subjectCode = $words->getAsIs($subject);
            $bodyCode = $words->getAsIs($body);
            if (!($subject == $subjectCode) || !($body == $bodyCode)) {
                $errors[] = 'AdminMassMailCodeExists';
            }
        }
        return $errors;
    }

    public function getAdminUnits($countrycode) {
        $query = "
            SELECT
                fk_admincode, name
            FROM
                geonames_cache
            WHERE
                fk_countrycode = '" . $countrycode . "'
                AND fcode = 'ADM1'
            ORDER BY
                name";
        return $this->BulkLookup($query);
    }

    public function getPlaces($countrycode, $adminunit) {
        $query = "
            SELECT
                geonameid, name
            FROM
                geonames_cache
            WHERE
                fk_countrycode = '" . $countrycode . "'
                AND fk_admincode = '" . $adminunit . "'
                AND fclass = 'P'
                AND fcode <> 'PPLX'
            ORDER BY
                name";
        return $this->BulkLookup($query);
    }

    public function getEnqueueAction($vars) {
        $action = "";
        if (array_key_exists('enqueuemembers', $vars)) {
            $action = 'enqueueMembers';
        } elseif (array_key_exists('enqueuelocation', $vars)) {
            $action = 'enqueueLocation';
        } elseif (array_key_exists('enqueuegroup', $vars)) {
            $action = 'enqueueGroup';
        } elseif (array_key_exists('enqueuevote', $vars)) {
            $action = 'enqueueVote';
        }
        return $action;
    }

    public function massmailEnqueueVarsOk(&$vars) {
        $errors = array();
        $action = $this->getEnqueueAction($vars);
        switch($action) {
            case 'enqueueMembers':
                if ($vars['members-type'] == 'usernames') {
                    $usernames = $vars['usernames'];
                    if (!empty($usernames)) {
                        // todo: shall we check if all members exist?
                        // $members = explode(";", $usernames);
                    } else {
                        $errors[] = 'AdminMassMailEnqueueUsernamesEmpty';
                    }
                } else {
                    // all members. Check if max-messages is correct
                    $max_messages = $vars['max-messages'];
                    if (!empty($max_messages)) {
                        if (!is_numeric($max_messages)) {
                            $errors[] = 'AdminMassMailEnqueueMembersNoNumber';
                        }
                    }
                }
                break;
            case 'enqueueLocation':
                if (is_numeric($vars['CountryIsoCode'])) {
                    $errors[] = 'AdminMassMailEnqueueNoCountrySelected';
                }
                break;
            case 'enqueueGroup':
                if ($vars['IdGroup'] == 0) {
                    $errors[] = 'AdminMassMailEnqueueNoGroupsSelected';
                }
                break;
            case 'enqueueVote':
                $count = $vars['poster'];
                if (empty($count) || !is_numeric($count)) {
                    $errors[] = 'AdminMassMailEnqueueVoteNoNumber';
                }
                break;
            default:
                $errors[] = 'AdminMassMailEnqueueWrongAction';
                return $errors;
        }
        return $errors;
    }

    private function getPreferenceIdForMassmail($id) {
        $massmail = $this->getMassmail($id);
        if ($massmail->Type == 'Specific') {
            $pref = 'PreferenceLocalEvent';
        } else {
            $pref = 'PreferenceAcceptNewsByMail';
        }
        $query = "SELECT id FROM preferences WHERE codeName = '" . $pref . "'";
        $r = $this->SingleLookup($query);
        if (!$r) {
            return -1;
        }
        return $r->id;
    }

    private function enqueueMassmailMembers($id, $usernames, $maxmessages) {
        $pref_id = $this->getPreferenceIdForMassmail($id);
        $IdEnqueuer = $this->getLoggedInMember()->id;
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                members AS m
            LEFT JOIN
                memberspreferences AS mp
                ON (m.id = mp.IdMember AND mp.IdPreference = " . $pref_id . ")";
        if (empty($usernames)) {
            // get count of members that would receive the newsletter
            $where = "WHERE m.Status IN ('Active', 'ActiveHidden') AND (mp.Value = 'Yes' OR mp.Value IS NULL)";
        }
        else
        {
            $where = "WHERE m.Status IN ('Active', 'ActiveHidden') AND (mp.Value = 'Yes' OR mp.Value IS NULL)
                    AND m.Username IN ('" . implode("', '", $usernames) . "')";
        }
        $limit = "";
        if ($maxmessages <> 0) {
            $limit = "LIMIT 0, " . $maxmessages;
        }
        $r = $this->dao->query($query . " " . $where . " " . $limit);
        $count = $r->affectedRows();
        return $count;
    }

    private function enqueueMassmailLocation($id, $countrycode, $adminunit, $place) {
        $pref_id = $this->getPreferenceIdForMassmail($id);
        $IdEnqueuer = $this->getLoggedInMember()->id;
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                geonames_cache AS g, members AS m
            LEFT JOIN
                memberspreferences AS mp
                ON (m.id = mp.IdMember AND mp.IdPreference = " . $pref_id . ")
            WHERE
                (m.IdCity = g.geonameId)
                AND g.fk_countrycode = '" . $this->dao->escape($countrycode) . "'
                AND (mp.Value = 'Yes' OR mp.Value IS NULL)
                AND (m.Status IN ('Active', 'ActiveHidden'))";
        if ($adminunit) {
            $query .= " AND g.fk_admincode = '". $adminunit . "'";
        }
        if ($place) {
            $query .= " AND g.geonameid = ". $place;
        }
        $r = $this->dao->query($query);
        if (!$r) {
            return -1;
        }
        $count = $r->affectedRows();
        return $count;
    }
    
    private function enqueueMassmailGroup($id, $groupId) {
        $pref_id = $this->getPreferenceIdForMassmail($id);
        $IdEnqueuer = $this->getLoggedInMember()->id;
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT 
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM 
                membersgroups as mg, members AS m
            LEFT JOIN 
                memberspreferences AS mp 
                ON (m.id = mp.IdMember AND mp.IdPreference = " . $pref_id . ")
            WHERE
                m.id = mg.IdMember 
                AND mg.IdGroup = " . $groupId . "
                AND (mp.Value = 'Yes' OR mp.Value IS NULL)
                AND (m.Status IN ('Active', 'ActiveHidden'))";
        $r = $this->dao->query($query);
            if (!$r) {
            return -1;
        }
        $count = $r->affectedRows();
        return $count;
    }
    
    private function enqueueMassmailVoters($id, $voters) {
        $pref_id = $this->getPreferenceIdForMassmail($id);
        $IdEnqueuer = $this->getLoggedInMember()->id;
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                members AS m
            LEFT JOIN
                memberspreferences AS mp 
                ON (m.id = mp.IdMember AND mp.IdPreference = " . $pref_id . ")
            WHERE
                m.Status IN ('Active', 'ActiveHidden')
                AND DATEDIFF(NOW(), m.LastLogin) < 183
            ORDER BY RAND()
            LIMIT 0, " . $voters;
        $r = $this->dao->query($query);
        $count = $r->affectedRows();
        if ($voters <> $count) {
            return -1;
        }
        return $count;
    }

    public function enqueueMassmail($vars) {
        $count = 0;
        $id = $vars['id'];
        $action = $this->getEnqueueAction($vars);
        switch($action) {
            case 'enqueueMembers':
                $usernames = array();
                if ($vars['members-type'] == 'usernames') {
                    $usernames = explode(";", $vars['usernames']);
                }
                if (empty($vars['max-messages'])) {
                    $maxmessages = 0;
                } else {
                    $maxmessages = $vars['max-messages'];
                }
                $count = $this->enqueueMassmailMembers($id, $usernames, $maxmessages);
                break;
            case 'enqueueLocation':
                $place = false;
                $admincode = false;
                $countrycode = $vars['CountryIsoCode'];
                if (isset($vars['AdminUnits'])) {
                    $admincode = $vars['AdminUnits'];
                }
                if (isset($vars['Places'])) {
                    $place = $vars['Places'];
                }
                $count = $this->enqueueMassmailLocation($id, $countrycode, $admincode, $place);
                break;
            case 'enqueueGroup':
                $groupId = $vars['IdGroup'];
                $count = $this->enqueueMassmailGroup($id, $groupId);
                break;
            case 'enqueueVote':
                $voters = $vars['poster'] * 3;
                $count = $this->enqueueMassmailVoters($id, $voters);
                break;
        }
        return $count;
    }

    public function unqueueMassMail($id) {
        $query = "
            DELETE FROM
                broadcastmessages
            WHERE
                Status = 'ToApprove'
                AND IdBroadcast = " . $id;
        $r = $this->dao->query($query);
        return $r->affectedRows();
    }

    public function untriggerMassMail($id) {
        $query = "
            DELETE FROM
                broadcastmessages
            WHERE
                Status = 'ToSend'
                AND IdBroadcast = " . $id;
        $r = $this->dao->query($query);
        return $r->affectedRows();
    }

    public function triggerMassMail($id) {
        $query = "
            UPDATE
                broadcastmessages
            SET
                Status = 'ToSend',
                updated = NOW()
            WHERE
                broadcastmessages.IdBroadcast = " . $id;
        $r = $this->dao->query($query);
        return $r->affectedRows();
    }

    public function treasurerEditCreateDonationVarsOk(&$vars) {
        $errors = array();
        if (empty($vars['donate-username'])) {
            $errors[] = 'AdminTreasurerDonorEmpty';
        } else {
            if ($vars['donate-username'] == "-empty-") {
                $vars['IdMember'] = 0;
            } else {
                $donor = $this->createEntity('Member')->findByUsername($vars['donate-username']);
                if (!$donor) {
                    $errors[] = 'AdminTreasurerUnknownDonor';
                } else {
                    $vars['IdMember'] = $donor->id;
                }
            }
        }
        if (!is_numeric($vars['donate-amount'])) {
            $errors[] = 'AdminTreasurerDonatedAmountInvalid';
        }
        if (empty($vars['donate-date'])) {
            $errors[] = 'AdminTreasurerDonatedOnEmpty';
        } else {
            $date = $vars['donate-date'];
            if ((strlen($date) < 8) || (strlen($date) > 10)) {
                 $errors[] = 'AdminTreasurerDonatedOnInvalid';
            } else {
                list($day, $month, $year) = preg_split('/[\/.-]/', $date);
                if (substr($month,0,1) == '0') $month = substr($month,1,2);
                if (substr($day,0,1) == '0') $day = substr($day,1,2);
                $start = mktime(0, 0, 0, (int)$month, (int)$day, (int)$year);
                $vars['DonatedOn'] = date('YmdHis', $start);
            }
        }
        if (is_numeric($vars['donate-country'])) {
            $errors[] = 'AdminTreasurerNoCountry';
        }
        return $errors;
    }

    public function getGeonameIdForCountryCode($countrycode) {
        $query = "
            SELECT
                geonameid
            FROM
                geonames_cache AS g
            WHERE
                g.fcode LIKE 'PCL%'
                AND g.fk_countrycode = '" . $countrycode . "'";
        $cc = $this->singleLookup($query);
        if ($cc) {
            return $cc->geonameid;
        }
        return false;
    }
    
    public function getCountryCodeForGeonameId($geonameid) {
        $query = "
            SELECT
                fk_countrycode
            FROM
                geonames_cache AS g
            WHERE
                g.geonameid = " . $geonameid;
        $cc = $this->singleLookup($query);
        if ($cc) {
            return $cc->fk_countrycode;
        }
        return false;
    }
    
    public function createDonation($memberid, $donatedon, $amount, $comment, $countryid) {
        $query = "
            INSERT INTO
                donations
            SET
                IdMember = " . $memberid . ",
                Email = '',
                StatusPrivate = 'showamountonly',
                created = '" .  $donatedon . "',
                Amount = " . $amount . ",
                Money = '',
                IdCountry = " . $countryid . ",
                namegiven = '',
                referencepaypal = '',
                membercomment = '',
                SystemComment = '" . $this->dao->escape($comment) . "'";
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        if ($sql->affectedRows() != 1) {
            return false;
        }
        return true;
    }

    public function updateDonation($id, $memberid, $donatedon, $amount, $comment, $countryid) {
        $query = "
            UPDATE
                donations
            SET
                IdMember = " . $memberid . ",
                Email = '',
                StatusPrivate = 'showamountonly',
                created = '" .  $donatedon . "',
                Amount = " . $amount . ",
                Money = '',
                IdCountry = " . $countryid . ",
                namegiven = '',
                referencepaypal = '',
                membercomment = '',
                SystemComment = '" . $this->dao->escape($comment) . "'
            WHERE
                id = " . $id;
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        return true;
    }

    public function getRecentDonations() {
        $donateModel = new DonateModel();
        return $donateModel->getDonations(true);
    }
    
    public function getStatForDonations() {
        $donateModel = new DonateModel();
        return $donateModel->getStatForDonations();
    }

    public function getDonationCampaignValues() {
        $donateModel = new DonateModel();
        return $donateModel->getCampaignValues();
    }

    public function getDonation($id) {
        $query = "
            SELECT
                *
            FROM
                donations
            WHERE
                id = " . $id;
        return $this->singleLookup($query);
    }
    
    public function getDonationCampaignStatus() {
        $query = "
            SELECT
                ToggleDonateBar
            FROM
                params";
        $r = $this->singleLookup($query);
        if (isset($r)) {
            return $r->ToggleDonateBar;
        }
        return false;
    }
 
    public function treasurerStartDonationCampaignVarsOk(&$vars) {
        $errors = array();
        if (!is_numeric($vars['donate-needed-per-year'])) {
            $errors[] = 'AdminTreasurerNeededAmountInvalid';
        }
        if (empty($vars['donate-start-date'])) {
            $errors[] = 'AdminTreasurerStartDateEmpty';
        } else {
            $date = $vars['donate-start-date'];
            if ((strlen($date) < 8) || (strlen($date) > 10)) {
                 $errors[] = 'AdminTreasurerStartDateInvalid';
            } else {
                list($day, $month, $year) = preg_split('/[\/.-]/', $date);
                if (substr($month,0,1) == '0') $month = substr($month,1,2);
                if (substr($day,0,1) == '0') $day = substr($day,1,2);
                $start = mktime(0, 0, 0, (int)$month, (int)$day, (int)$year);
                $vars['StartDate'] = date('Y-m-d', $start);
            }
        }
        return $errors;
    }

    public function startDonationCampaign($vars) {
        $donateModel = new DonateModel();
        $success = $donateModel->setCampaignValues($vars['donate-needed-per-year'], $vars['StartDate']);
        if (!$success) {
            return false;
        }
        $query = "
            UPDATE
                params
            SET
                ToggleDonateBar = 1";
        $r = $this->dao->query($query);
        if ($r->affectedRows() != 1) {
            return false;
        };
        return true;
    }

    public function stopDonationCampaign() {
        $query = "
            UPDATE
                params
            SET
                ToggleDonateBar = 0";
        $r = $this->dao->query($query);
        if ($r->affectedRows() != 1) {
            return false;
        };
        return true;
    }
}
