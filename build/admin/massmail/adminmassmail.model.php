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

use App\Doctrine\MemberStatusType;

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
                word AS w1,
                word AS w2
            WHERE
                w1.code = 'broadcast_title_" . $this->dao->escape($broadcast->Name) . "'
                AND w1.ShortCode = 'en'
                AND w2.code = 'broadcast_body_" . $this->dao->escape($broadcast->Name) . "'
                AND w2.ShortCode = 'en'";
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
                language.ShortCode AS ShortCode,
                language.Name AS Name
            FROM
                language, word
            WHERE word.code='broadcast_body_" . $this->dao->escape($broadcast->Name) . "'
            AND language.ShortCode=word.ShortCode";
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
        // The country of the member's active address; the language is the member's locale (English if unknown)
        $query = "
            SELECT
                m.id AS Id, m.Username AS Username, m.Email AS Email, c.name AS Country, m.Status AS Status,
                COALESCE(l.ShortCode, 'en') AS LanguageId, COALESCE(l.Name, 'English') AS Language
            FROM
                broadcastmessages AS bm
            JOIN
                member AS m ON m.id = bm.IdReceiver
            LEFT JOIN
                address AS a ON a.member_id = m.id AND a.active = 1
            LEFT JOIN
                location AS g ON g.geoname_id = a.location
            LEFT JOIN
                location AS c ON c.geoname_id = g.country
            LEFT JOIN
                language AS l ON l.ShortCode = m.Locale
            WHERE
                bm.IdBroadcast = " . (int) $id . "
                AND bm.Status = '" . $status . "'
            LIMIT " . (int) $start . "," . (int) $limit;

        return $this->BulkLookup($query);
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
        $name = $this->dao->escape(strtolower((string) $name));
        // first create entry in the broadcast table
        $query = "
            INSERT INTO
                broadcast
            SET
                Type = '" . $type . "',
                Name = '" . $name . "',
                created = NOW(),
                Status = 'Created',
                IdCreator = ". $this->session->get("IdMember");
        $this->dao->query($query);

        $query = "
            INSERT INTO
                word
            SET
                code = 'broadcast_title_" . $name . "',
                ShortCode = 'en',
                `domain` = 'messages+intl-icu',
                Sentence = '" . $this->dao->escape($subject) . "',
                updated = NOW(),
                created = NOW(),
                majorupdate = NOW(),
                isarchived = 0,
                donottranslate = 'no',
                TranslationPriority = 5,
                author_id = " . $this->getLoggedInMember()->id . ",
                Description = '" . $this->dao->escape($description) . "'";
        $this->dao->query($query);

        $query = "
            INSERT INTO
                word
            SET
                code = 'broadcast_body_" . $name . "',
                ShortCode = 'en',
                `domain` = 'messages+intl-icu',
                Sentence = '" . $this->dao->escape($body) . "',
                updated = NOW(),
                created = NOW(),
                majorupdate = NOW(),
                isarchived = 0,
                donottranslate = 'no',
                TranslationPriority = 5,
                author_id = " . $this->getLoggedInMember()->id . ",
                Description = '" . $this->dao->escape($description) . "'";
        $this->dao->query($query);
    }

    public function updateMassmail($id, $name, $type, $subject, $body) {
        $query = "
            UPDATE
                broadcast
            SET
                created = NOW(),
                IdCreator = ". $this->session->get("IdMember") . ",
                Type = '" . $type . "'
            WHERE
                id = " . $id;
        $this->dao->query($query);

        $query = "
            UPDATE
                word
            SET
                Sentence = '" . $this->dao->escape($subject) . "',
                updated = NOW(),
                author_id = " . $this->getLoggedInMember()->id . "
            WHERE
                code = 'broadcast_title_" . $this->dao->escape($name) . "'
                AND ShortCode = 'en'";
        $this->dao->query($query);

        $query = "
            UPDATE
                word
            SET
                Sentence = '" . $this->dao->escape($body) . "',
                updated = NOW(),
                author_id = " . $this->getLoggedInMember()->id . "
            WHERE
                code = 'broadcast_body_" . $this->dao->escape($name) . "'
                AND ShortCode = 'en'";
        $this->dao->query($query);
    }

    public function massmailEditCreateVarsOk(&$vars) {
        $id = $vars['Id'];
        $name = strtolower((string) $vars['Name']);
        $subject = $vars['Subject'];
        $body = $vars['Body'];
        $description = $vars['Description'];
        $type = $vars['Type'];
        $errors = [];
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
            $subject = 'broadcast_title_' . $name;
            $body = 'broadcast_body_' . $name;
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
                admin_1_id AS admin1, name
            FROM
                location
            WHERE
                country_id = '" . $this->dao->escape($countrycode) . "'
                AND feature_code = 'ADM1'
            ORDER BY
                name";
        return $this->BulkLookup($query);
    }

    public function getPlaces($countrycode, $adminunit) {
        $query = "
            SELECT
                geoname_id AS geonameId, name
            FROM
                location
            WHERE
                country_id = '" . $this->dao->escape($countrycode) . "'
                AND admin_1_id = '" . $this->dao->escape($adminunit) . "'
                AND feature_class = 'P'
                AND feature_code <> 'PPLX'
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
        } elseif (array_key_exists('enqueuemailtoconfirmreminder', $vars)) {
            $action = 'enqueueMailToConfirmReminder';
        } elseif (array_key_exists('enqueuecorrectbday', $vars)) {
            $action = 'enqueueCorrectBirthDate';
        } elseif (array_key_exists('enqueuetermsofuse', $vars)) {
            $action = 'enqueueTermsOfUse';
        } elseif (array_key_exists('enqueuesuspension', $vars)) {
            $action = 'enqueueSuspensionNotification';
        }
        return $action;
    }

    public function massmailEnqueueVarsOk(&$vars) {
        $errors = [];
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
            case 'enqueueMailToConfirmReminder':
            case 'enqueueTermsOfUse':
            case 'enqueueCorrectBirthDate':
            case 'enqueueSuspensionNotification':
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
                member AS m
            LEFT JOIN
                memberspreferences AS mp
                ON (m.id = mp.IdMember AND mp.IdPreference = " . $pref_id . ")";
        if (empty($usernames)) {
            // get count of members that would receive the newsletter
            $where = "WHERE m.Status IN (" . MemberStatusType::ACTIVE_WITH_MESSAGES . ") AND (mp.Value = 'Yes' OR mp.Value IS NULL)";
        }
        else
        {
            $where = "WHERE m.Status IN (" . MemberStatusType::ACTIVE_WITH_MESSAGES . ") AND (mp.Value = 'Yes' OR mp.Value IS NULL)
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
                member AS m
            JOIN
                address AS a ON a.member_id = m.id AND a.active = 1
            JOIN
                location AS g ON g.geoname_id = a.location
            LEFT JOIN
                memberspreferences AS mp
                ON (m.id = mp.IdMember AND mp.IdPreference = " . $pref_id . ")
            WHERE
                g.country_id = '" . $this->dao->escape($countrycode) . "'
                AND (mp.Value = 'Yes' OR mp.Value IS NULL)
                AND (m.Status IN (" . MemberStatusType::ACTIVE_WITH_MESSAGES . "))";
        if ($adminunit) {
            $query .= " AND g.admin_1_id = '" . $this->dao->escape($adminunit) . "'";
        }
        if ($place) {
            $query .= " AND g.geoname_id = " . (int) $place;
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
                membersgroups as mg, member AS m
            LEFT JOIN
                memberspreferences AS mp
                ON (m.id = mp.IdMember AND mp.IdPreference = " . $pref_id . ")
            WHERE
                m.id = mg.IdMember
                AND mg.IdGroup = " . $groupId . "
                AND mg.Status = 'In'
                AND (mp.Value = 'Yes' OR mp.Value IS NULL)
                AND (m.Status IN (" . MemberStatusType::ACTIVE_WITH_MESSAGES . "))";
        $r = $this->dao->query($query);
            if (!$r) {
            return -1;
        }
        $count = $r->affectedRows();
        return $count;
    }

    private function enqueueMassmailLoginReminder($id) {
        $IdEnqueuer = $this->getLoggedInMember()->id;
        // first set all members that didn't login for longer than a year to 'OutOfRemind'
        $query = "
                UPDATE
                    member m
                SET
                    m.status = 'OutOfRemind'
                WHERE
                    (DATEDIFF(NOW(), m.LastActive) > 365 OR m.LastActive IS NULL)
                    AND m.status = 'Active'";
        $r = $this->dao->query($query);

        // then increase the counter for reminders without login for all members that are OutOfRemind
        $query = "
                UPDATE
                    member m
                SET
                    m.Reminders = m.Reminders + 1
                WHERE
                    m.status = 'OutOfRemind'";
        $r = $this->dao->query($query);

        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                member AS m
            WHERE
                m.Status = 'OutOfRemind'";
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
                member AS m
            WHERE
                m.Status = 'MailToConfirm'
                AND created BETWEEN '2015-01-01' AND (NOW() - INTERVAL 1 WEEK)";
        $r = $this->dao->query($query);
        $count = $r->affectedRows();
        return $count;
    }

    private function enqueueMassmailCorrectBirthDate($id) {
        $IdEnqueuer = $this->getLoggedInMember()->id;
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                member AS m
            WHERE
                m.Status IN ('Active', 'OutOfRemind')
                AND BirthDate < '1922-06-22'";
        $r = $this->dao->query($query);
        $count = $r->affectedRows();
        return $count;
    }

    private function enqueueSuspensionNotification($id) {
        $IdEnqueuer = $this->getLoggedInMember()->id;
        $query = "
            REPLACE
                broadcastmessages (IdBroadcast, IdReceiver, IdEnqueuer, Status, updated)
            SELECT
                " . $id . ", m.id, " . $IdEnqueuer . ", 'ToApprove', NOW()
            FROM
                member AS m
            WHERE
                m.Status IN ('OutOfRemind')
                AND DATEDIFF(NOW(), m.LastActive) > 365 * 5";
        $r = $this->dao->query($query);

        // Now set all those members to suspended
        $query = "
                UPDATE
                    member m
                SET
                    m.status = 'SuspendedBeta', m.Reminders = 100
                WHERE
                    m.status = 'OutOfRemind' AND DATEDIFF(NOW(), m.LastActive) > 365 * 5"
                ;
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
                member AS m
            WHERE
                m.Status IN (" . MemberStatusType::ACTIVE_ALL . ")
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
                $usernames = [];
                if ($vars['members-type'] == 'usernames') {
                    $usernames = explode(";", (string) $vars['usernames']);
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
                $count = $this->enqueueMassmailLoginReminder($id);
                break;
            case 'enqueueMailToConfirmReminder':
                $count = $this->enqueueMassmailMailToConfirmReminder($id);
                break;
            case 'enqueueCorrectBirthDate':
                $count = $this->enqueueMassmailCorrectBirthDate($id);
                break;
            case 'enqueueSuspensionNotification':
                $count = $this->enqueueSuspensionNotification($id);
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
     * Get the count of members with status MailToConfirm between January, 1st 2015 and now - 1 week
     */
    public function getMailToConfirmCount() {
        $query = "
            SELECT
                count(*) as mailToConfirmCount
            FROM
                member
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

    /**
     * Get the count of members with obviously wrong birth dates
     */
    public function getIncorrectBirthDateCount() {
        $query = "
            SELECT
                count(*) as incorrectBirthDateCount
            FROM
                member
            WHERE
                status IN ('Active', 'OutOfRemind')
                AND BirthDate < '1922-06-22'
                 ";
        $r = $this->dao->query($query);
        if (!$r) {
            return 0;
        }
        $row = $r->fetch(PDB::FETCH_OBJ);
        if (!isset($row->incorrectBirthDateCount)) {
            return 0;
        }
        return $row->incorrectBirthDateCount;
    }

    /**
     * Get the count of members who will be suspended as they didn't react on 5 login reminders
     */
    public function getSuspensionNotificationCount() {
        $query = "
            SELECT
                count(*) as suspensionNotificationCount
            FROM
                member m
            WHERE
                m.status = 'OutOfRemind' AND DATEDIFF(NOW(), m.LastActive) > 365 * 5
                 ";
        $r = $this->dao->query($query);
        if (!$r) {
            return 0;
        }
        $row = $r->fetch(PDB::FETCH_OBJ);
        if (!isset($row->suspensionNotificationCount)) {
            return 0;
        }
        return $row->suspensionNotificationCount;
    }
}
