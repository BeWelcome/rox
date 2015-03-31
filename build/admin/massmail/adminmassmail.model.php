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
class AdminMassmailModel extends RoxModelBase
{
    /**
     * returns the broadcast messages in the database
     * including information about the current state
     * (number of enqueued, triggered and sent messages)
     *
     * @param bool $specific
     * @param bool $general
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
                    `broadcastmessages`
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
        $ret->id = $id;
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
            if (isset($languagesnames[$mmri->LanguageId])) {
                $mmri->Language = $languagesnames[$mmri->LanguageId];
            } else {
                $mmri->Language = $languagesnames[0];
                $mmri->LanguageId = 0;
            }
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
                created = NOW(),
                majorupdate = NOW(),
                isarchived = 0,
                donottranslate = 'no',
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
                created = NOW(),
                majorupdate = NOW(),
                isarchived = 0,
                donottranslate = 'no',
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
        $type = $vars['Type'];
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

        if ($type == "None") {
            $errors[] = 'AdminMassMailChooseAType';
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
                } elseif (array_key_exists('enqueuereminder', $vars)) {
            $action = 'enqueueReminder';
        } elseif (array_key_exists('enqueuesuggestionsreminder', $vars)) {
            $action = 'enqueueSuggestionsReminder';
        } elseif (array_key_exists('enqueuemailtoconfirmreminder', $vars)) {
            $action = 'enqueueMailToConfirmReminder';
        } elseif (array_key_exists('enqueuetermsofuse', $vars)) {
            $action = 'enqueueTermsOfUse';
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
            case 'enqueueReminder':
                break;
            case 'enqueueSuggestionsReminder':
                break;
            case 'enqueueMailToConfirmReminder':
                break;
            case 'enqueueTermsOfUse':
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
            $where = "WHERE m.Status IN (" . Member::ACTIVE_WITH_MESSAGES . ") AND (mp.Value = 'Yes' OR mp.Value IS NULL)";
        }
        else
        {
            $where = "WHERE m.Status IN (" . Member::ACTIVE_WITH_MESSAGES . ") AND (mp.Value = 'Yes' OR mp.Value IS NULL)
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
                AND (m.Status IN (" . Member::ACTIVE_WITH_MESSAGES . "))";
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
                AND mg.Status = 'In'
                AND (mp.Value = 'Yes' OR mp.Value IS NULL)
                AND (m.Status IN (" . Member::ACTIVE_WITH_MESSAGES . "))";
        $r = $this->dao->query($query);
            if (!$r) {
            return -1;
        }
        $count = $r->affectedRows();
        return $count;
    }

    private function enqueueMassmailReminder($id) {
        $IdEnqueuer = $this->getLoggedInMember()->id;
        // first set all members that didn't login for longer than a year to 'OutOfRemind'
        $query = "
                UPDATE
                    members m
                SET
                    m.status = 'OutOfRemind'
                WHERE
                    DATEDIFF(NOW(), m.LastLogin) > 365
                    AND m.status = 'Active'";
        $r = $this->dao->query($query);
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                members AS m
            WHERE
                m.Status = 'OutOfRemind'";
        $r = $this->dao->query($query);
        $count = $r->affectedRows();
        return $count;
    }

    private function enqueueMassmailSuggestionsReminder($id) {
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
                m.Status = 'Active'
                AND (mp.Value = 'Yes' OR mp.Value IS NULL)
                AND DATEDIFF(NOW(), m.LastLogin) < 180
            ORDER BY
                RAND()
            LIMIT 0," . $this->getSuggestionsReminderCount();
        $r = $this->dao->query($query);
        $count = $r->affectedRows();
        return $count;
    }

    private function enqueueMassmailMailToConfirmReminder($id) {
        $IdEnqueuer = $this->getLoggedInMember()->id;
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                members AS m
            WHERE
                m.Status = 'MailToConfirm'
                AND created BETWEEN '2015-01-01' AND (NOW() - INTERVAL 1 WEEK)";
        $r = $this->dao->query($query);
        $count = $r->affectedRows();
        return $count;
    }

    private function enqueueMassmailTermsOfUse($id) {
        $pref_id = $this->getPreferenceIdForMassmail($id);
        $IdEnqueuer = $this->getLoggedInMember()->id;
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                members AS m
            WHERE
                m.Status IN (" . Member::ACTIVE_ALL . ")
            ";
        $r = $this->dao->query($query);
        $count = $r->affectedRows();
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
            case 'enqueueReminder':
                $count = $this->enqueueMassmailReminder($id);
                break;
            case 'enqueueSuggestionsReminder':
                $count = $this->enqueueMassmailSuggestionsReminder($id);
                break;
            case 'enqueueMailToConfirmReminder':
                $count = $this->enqueueMassmailMailToConfirmReminder($id);
                break;
            case 'enqueueTermsOfUse':
                $count = $this->enqueueMassmailTermsOfUse($id);
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
        // If mass mail is untriggered set status of broadcast entry back to 'Created'
        $query = "
            UPDATE
                broadcast
            SET
                Status = 'Created'
            WHERE
                id = " . $this->dao->escape($id);
        $this->dao->query($query);

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
        // If mass mail is triggered set status of broadcast entry to 'Triggered' as well
        $query = "
            UPDATE
                broadcast
            SET
                Status = 'Triggered'
            WHERE
                id = " . $this->dao->escape($id);
        $this->dao->query($query);

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

    /**
     * Get the count of members to be invited randomly
     *
     * Maximum number of votes ever casted for one suggestion times three
     */
    public function getSuggestionsReminderCount() {
        // fixed number of voters based on the number of members
        // that voted for the decision making process
        $votersCount = 763 * 3;
        $query = "
            SELECT
                count(memberHash) as votersCount
            FROM
                suggestions_votes
            GROUP BY
                suggestionId,
                optionId
            ORDER BY
                votersCount DESC
                 ";
        $r = $this->dao->query($query);
        if (!$r) {
            return $votersCount;
        }
        $row = $r->fetch(PDB::FETCH_OBJ);
        if (!isset($row->votersCount)) {
            return $votersCount;
        }
        return $row->votersCount * 3;
    }

    /**
     * Get the count of members with status MailToConfirm between January, 1st 2015 and now - 1 week
     */
    public function getMailToConfirmCount() {
        $query = "
            SELECT
                count(*) as mailToConfirmCount
            FROM
                members
            WHERE
                status = 'MailToConfirm'
                AND created BETWEEN '2015-01-01' AND (NOW() - INTERVAL 1 WEEK)
                 ";
        $r = $this->dao->query($query);
        if (!$r) {
            return 0;
        }
        $row = $r->fetch(PDB::FETCH_OBJ);
        if (!isset($row->mailToConfirmCount)) {
            return 0;
        }
        return $row->mailToConfirmCount;
    }
}
