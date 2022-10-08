<?php

use App\Doctrine\MemberStatusType;

/**
 * Class AdminFlagsModel
 */
class AdminFlagsModel extends RoxModelBase {

    public function checkScopeWellFormed($scope) {
        $countQuotes = substr_count($scope, '"');
        $countSemiColons = substr_count($scope, ';');
        if ($countQuotes % 2 == 1) {
            return false;
        }
        if ($countQuotes > 2 && $countSemiColons <> ($countQuotes / 2)) {
            return false;
        }
        return true;
    }

    /**
     * @param $vars
     * @return array
     */
    public function checkAssignVarsOk($vars) {
        $errors = array();
        if (empty($vars['username'])) {
            $errors[] = 'AdminFlagsUsernameEmpty';
        } else {
            // check if user name exists
            $member = new Member();
            $member = $member->findByUsername($vars['username']);
            if (!$member) {
                $errors[] = 'AdminFlagsUsernameNotExisting';
            }
        }
        if ($vars['flagid'] == 0) {
            $errors[] = 'AdminFlagsNoFlagSelected';
        } else {
            // check if flag is already assigned
            if (isset($member)) {
                $flag = new Flag($vars['flagid']);
                $assigned = $flag->getFlagForMember($member);
                if ($assigned) {
                    $errors[] = 'AdminFlagsAlreadyAssigned';
                }
            }
        }
        if ($vars['level'] == 0) {
            $errors[] = 'AdminFlagsNoLevelSelected';
        }
        if (empty($vars['comment'])) {
            $errors[] = 'AdminFlagsCommentEmpty';
        }
        return $errors;
    }

    /**
     * @param $vars
     */
    public function assignFlag($vars) {
        $member = new Member();
        $member = $member->findByUsername($vars['username']);
        $query = "
            INSERT INTO
                flagsmembers
            SET
                IdFlag = '" . $this->dao->escape($vars['flagid']) . "',
                IdMember = '" . $member->id . "',
                Scope = '" . $this->dao->escape($vars['scope']) . "',
                Level = '" . $this->dao->escape($vars['level']) . "',
                Comment = '" . $this->dao->escape($vars['comment']) . "',
                created = NOW()";
        $this->dao->query($query);
    }

    /**
     * get list of members with all assigned flags
     *
     * @access public
     * @return array of members with flags
     */
    public function getMembersWithFlags($member = false, $includeLevelZero = false)
    {
        $query = '
            SELECT
                m.Username,
                m.id as id,
                m.status,
                m.LastLogin,
                g.Name as PlaceName,
                gc.Name as CountryName,
                f.id flagId,
                fm.Level,
                fm.Scope,
                fm.Comment
            FROM
                flags f,
                flagsmembers fm,
                members m,
                geonames g,
                geonamescountries gc
            WHERE
                m.Status in (' . MemberStatusType::ACTIVE_ALL . ')';
        if ($member) {
            $query .= ' AND m.id = ' . $member->id;
        }
        $query .= '
                AND fm.IdMember = m.id
                AND fm.IdFlag = f.id
                AND f.Relevance <> 0
                AND m.IdCity = g.geonameId
                AND g.country = gc.country ';
        if (!$includeLevelZero) {
            $query .= ' AND fm.Level <> 0';
        }
        $query .= '
            ORDER BY
                f.Relevance DESC,
                m.Username,
                f.Name
            ';
        $result = $this->bulkLookup($query);

        $membersWithFlags = array();
        foreach ($result as $mwr) {
            if (!isset($membersWithFlags[$mwr->Username])) {
                $memberDetails = new stdClass();
                $memberDetails->id = $mwr->id;
                $memberDetails->Status = $mwr->status;
                $memberDetails->LastLogin = date('Y-m-d', strtotime($mwr->LastLogin));
                $memberDetails->PlaceName = $mwr->PlaceName;
                $memberDetails->CountryName = $mwr->CountryName;
                $memberDetails->Flags = array();
                $membersWithFlags[$mwr->Username] = $memberDetails;
            }
            $flagDetails = new stdClass();
            $flagDetails->level = $mwr->Level;
            $flagDetails->scope = $mwr->Scope;
            $flagDetails->comment = $mwr->Comment;
            $membersWithFlags[$mwr->Username]->Flags[$mwr->flagId] = $flagDetails;
        }
        return $membersWithFlags;
    }

    /**
     * get list of flags with members with that flag
     *
     * @access public
     * @return list of flags with members
     */
    public function getFlagsWithMembers($flagId = false, $includeLevelZero = false)
    {
        $query = '
            SELECT
                f.id flagId,
                fm.Level,
                fm.Scope,
                fm.Comment,
                m.Username,
                m.id as id,
                m.status,
                m.LastLogin,
                g.Name as PlaceName,
                gc.Name as CountryName
            FROM
                flags f,
                flagsmembers fm,
                members m,
                geonames g,
                geonamescountries gc
            WHERE
                m.Status in (' . MemberStatusType::ACTIVE_ALL . ')
                AND fm.IdMember = m.id
                AND fm.IdFlag = f.id
                AND f.Relevance <> 0';
        if ($flagId) {
            $query .= ' AND f.id = ' . $flagId;
        }
        $query .= '
                AND m.IdCity = g.geonameId
                AND g.country = gc.country
                ';
        if (!$includeLevelZero) {
            $query .= ' AND fm.Level <> 0';
        }
        $query .= '
            ORDER BY
                f.Relevance DESC,
                f.Name,
                m.Username
            ';
        $result = $this->bulkLookup($query);

        $flagsWithMembers = array();
        foreach ($result as $rwm) {
            if (!isset($flagsWithMembers[$rwm->flagId])) {
                $flagDetails = new StdClass();
                $flagDetails->Members = array();
                $flagsWithMembers[$rwm->flagId] = $flagDetails;
            }
            $memberDetails = new StdClass();
            $memberDetails->Status = $rwm->status;
            $memberDetails->LastLogin = date('Y-m-d', strtotime($rwm->LastLogin));
            $memberDetails->Username = $rwm->Username;
            $memberDetails->PlaceName = $rwm->PlaceName;
            $memberDetails->CountryName = $rwm->CountryName;
            $memberDetails->level = $rwm->Level;
            $memberDetails->scope = $rwm->Scope;
            $memberDetails->comment = $rwm->Comment;
            $flagsWithMembers[$rwm->flagId]->Members[$rwm->id] = $memberDetails;
        }
        return $flagsWithMembers;
    }

    /**
     * get all flags defined or flags allowed for member
     *
     * @access public
     * @return array list of flags
     */
    public function getFlags($memberFlagsOnly = false, $member = false) {
        $query = "
            SELECT
                *
            FROM
                flags f
            WHERE
                f.Relevance <> 0";
        if ($memberFlagsOnly) {
        }
        $query .= "
            ORDER BY
                f.Relevance DESC,
                f.Name
            ";
        $memberFlags = array();
        if ($member) {
            $memberFlags = $member->getOldFlags();
        }
        $result = $this->bulkLookup($query, array('id'));

        foreach($memberFlags as $flag) {
            if (isset($result[$flag['id']])) {
                unset($result[$flag['id']]);
            }
        }
        return $result;
    }

    public function checkEditVarsOk($vars) {
        $errors = array();
        if (empty($vars['comment'])) {
            $errors[] = 'AdminFlagsCommentEmpty';
        }
        return $errors;
    }

    public function edit($vars) {
        $temp = new Member();
        $member = $temp->findByUsername($vars['username']);
        $query = "
            UPDATE
                flagsmembers fm
            SET
                fm.Level = '" . $this->dao->escape($vars['level']) . "',
                fm.Scope = '" . $this->dao->escape($vars['scope']) . "',
                fm.Comment = '" . $this->dao->escape($vars['comment']) . "',
                fm.Updated = NOW()
            WHERE
                fm.IdMember = " . $member->id . "
                AND fm.IdFlag = " . $this->dao->escape($vars['flagid']) . "
            ";
        $this->dao->query($query);
        return true;
    }

    /**
     * Removes a flag from a member
     * Keeps the history by setting the level to 0 and updating the comment
     * with a note when the removal happened and by whom
     *
     * @param $vars
     * @return bool
     */
    public function remove($vars) {
        $temp = new Member();
        $member = $temp->findByUsername($vars['username']);
        $loggedInMember = $this->getLoggedInMember();
        $comment = $vars['comment'] . "\n\nRemoved by " .$loggedInMember->Username . " on "
            . date('Y-m-d');
        $query = "
            UPDATE
                flagsmembers fm
            SET
                fm.Level = '0',
                fm.Scope = '" . $this->dao->escape($vars['scope']) . "',
                fm.Comment = '" . $this->dao->escape( $comment ) . "',
                fm.Updated = NOW()
            WHERE
                fm.IdMember = " . $member->id . "
                AND fm.IdFlag = " . $this->dao->escape($vars['flagid']) . "
            ";
        $this->dao->query($query);
        return true;
    }

    public function checkCreateVarsOk($vars) {
        $errors = array();
        if (empty($vars['name'])) {
            $errors[] = 'AdminFlagsNameEmpty';
        } else {
            $query = "
                SELECT
                    *
                FROM
                    flags f
                WHERE
                    f.Name LIKE '" . $this->dao->escape($vars['name']) . "'";
            $name = $this->singleLookup($query);
            if ($name) {
                $errors[] = 'AdminFlagsFlagExists';
            }
        }
        if (empty($vars['description'])) {
            $errors[] = 'AdminFlagsDescriptionEmpty';
        }
        if (empty($vars['relevance'])) {
            $errors[] = 'AdminFlagsRelevance';
        }
        return $errors;
    }

    public function createFlag($vars) {
        $query = "
            INSERT INTO
                flags
            SET
                `Name` = '" . $this->dao->escape($vars['name']) . "',
                `Description` = '" . $this->dao->escape($vars['description']) . "',
                `Relevance` = '100'
             ";
        $this->dao->query($query);

        return true;
    }
}
