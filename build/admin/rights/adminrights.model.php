<?php

/**
 * Class AdminRightsModel
 */
class AdminRightsModel extends RoxModelBase {

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
            $errors[] = 'AdminRightsUsernameEmpty';
        } else {
            // check if user name exists
            $member = new Member();
            $member = $member->findByUsername($vars['username']);
            if (!$member) {
                $errors[] = 'AdminRightsUsernameNotExisting';
            }
        }
        if ($vars['right'] == 0) {
            $errors[] = 'AdminRightsNoRightSelected';
        } else {
            // check if right is already assigned
            if (isset($member)) {
                $right = new Right($vars['right']);
                $assigned = $right->getRightForMember($member);
                if ($assigned) {
                    $errors[] = 'AdminRightsAlreadyAssigned';
                }
            }
        }
        if ($vars['level'] == 0) {
            $errors[] = 'AdminRightsNoLevelSelected';
        }
        if (empty($vars['scope'])) {
            $errors[] = 'AdminRightsScopeEmpty';
        } else {
            // check if scope is well formed
            if (!$this->checkScopeWellFormed($vars['scope'])) {
                $errors[] = 'AdminRightsScopeNotWellFormed';
            }
        }
        if (empty($vars['comment'])) {
            $errors[] = 'AdminRightsCommentEmpty';
        }
        return $errors;
    }

    /**
     * @param $vars
     */
    public function assignRight($vars) {
        $member = new Member();
        $member = $member->findByUsername($vars['username']);
        $query = "
            INSERT INTO
                rightsvolunteers
            SET
                IdRight = '" . $this->dao->escape($vars['right']) . "',
                IdMember = '" . $member->id . "',
                Scope = '" . $this->dao->escape($vars['scope']) . "',
                Level = '" . $this->dao->escape($vars['level']) . "',
				Comment = '" . $this->dao->escape($vars['comment']) . "',
				created = NOW()";
        $this->dao->query($query);
    }

//    /**
//     * get list of members which have a right assigned
//     *
//     * @access public
//     * @return list of members
//     */
//    public function getMembers($includeLevelZero = false)
//    {
//        $query = '
//            SELECT
//                m.Username,
//                m.id as id,
//                m.status
//            FROM
//                rights r,
//                rightsvolunteers rv,
//                members m
//            WHERE
//                m.Status in (' . Member::ACTIVE_ALL . ')
//                AND rv.IdMember = m.id
//                AND rv.IdRight = r.id';
//        if (!$includeLevelZero) {
//            $query .= ' AND rv.Level <> 0';
//        }
//        $query .= '
//            ORDER BY
//                m.Username
//            ';
//        return $this->bulkLookup($query);
//    }
//
    /**
     * get list of members with all assigned rights
     *
     * @access public
     * @return list of members with rights
     */
    public function getMembersWithRights($member = false, $includeLevelZero = false)
    {
        $query = '
            SELECT
                m.Username,
                m.id as id,
                m.status,
                g.Name as PlaceName,
                gc.Name as CountryName,
                r.id rightId,
                rv.Level,
                rv.Scope,
                rv.Comment
            FROM
                rights r,
                rightsvolunteers rv,
                members m,
                geonames g,
                geonamescountries gc
            WHERE
                m.Status in (' . Member::ACTIVE_ALL . ')';
        if ($member) {
            $query .= ' AND m.id = ' . $member->id;
        }
        $rights = implode("','", $this->getAllowedRights());
        if (!empty($rights)) {
            $query .= "
            AND
				r.Name IN ('" . $rights . "') ";
        }
        $query .= '
                AND rv.IdMember = m.id
                AND rv.IdRight = r.id
                AND m.IdCity = g.geonameid
                AND g.country = gc.country ';
        if (!$includeLevelZero) {
            $query .= ' AND rv.Level <> 0';
        }
        $query .= '
            ORDER BY
                m.Username,
                r.Name
            ';
        $result = $this->bulkLookup($query);

        $membersWithRights = array();
        foreach ($result as $mwr) {
            if (!isset($membersWithRights[$mwr->Username])) {
                $memberDetails = new StdClass();
                $memberDetails->id = $mwr->id;
                $memberDetails->Status = $mwr->status;
                $memberDetails->PlaceName = $mwr->PlaceName;
                $memberDetails->CountryName = $mwr->CountryName;
                $memberDetails->Rights = array();
                $membersWithRights[$mwr->Username] = $memberDetails;
            }
            $rightDetails = new StdClass();
            $rightDetails->level = $mwr->Level;
            $rightDetails->scope = $mwr->Scope;
            $rightDetails->comment = $mwr->Comment;
            $membersWithRights[$mwr->Username]->Rights[$mwr->rightId] = $rightDetails;
        }
        return $membersWithRights;
    }

    /**
     * get list of rights with members with that right
     *
     * @access public
     * @return list of rights with members
     */
    public function getRightsWithMembers($rightId = false, $includeLevelZero = false)
    {
        $query = '
            SELECT
                r.id rightId,
                rv.Level,
                rv.Scope,
                rv.Comment,
                m.Username,
                m.id as id,
                m.status,
                g.Name as PlaceName,
                gc.Name as CountryName
            FROM
                rights r,
                rightsvolunteers rv,
                members m,
                geonames g,
                geonamescountries gc
            WHERE
                m.Status in (' . Member::ACTIVE_ALL . ')
                AND rv.IdMember = m.id
                AND rv.IdRight = r.id';
        if ($rightId) {
            $query .= ' AND r.id = ' . $rightId;
        }
        $rights = implode("','", $this->getAllowedRights());
		if (!empty($rights)) {
			$query .= "
            AND
				r.Name IN ('" . $rights . "') ";
		}
        $query .= '
                AND m.IdCity = g.geonameid
                AND g.country = gc.country ';
        if (!$includeLevelZero) {
            $query .= ' AND rv.Level <> 0';
        }
        $query .= '
            ORDER BY
                r.Name,
                m.Username
            ';
        $result = $this->bulkLookup($query);

        $rightsWithMembers = array();
        foreach ($result as $rwm) {
            if (!isset($rightsWithMembers[$rwm->rightId])) {
                $rightDetails = new StdClass();
                $rightDetails->Members = array();
                $rightsWithMembers[$rwm->rightId] = $rightDetails;
            }
            $memberDetails = new StdClass();
            $memberDetails->Status = $rwm->status;
            $memberDetails->Username = $rwm->Username;
            $memberDetails->PlaceName = $rwm->PlaceName;
            $memberDetails->CountryName = $rwm->CountryName;
            $memberDetails->level = $rwm->Level;
            $memberDetails->scope = $rwm->Scope;
            $memberDetails->comment = $rwm->Comment;
            $rightsWithMembers[$rwm->rightId]->Members[$rwm->id] = $memberDetails;
        }
        return $rightsWithMembers;
    }

	private function getAllowedRights() {
		$member = $this->getLoggedInMember();
		if (!$member) {
			return array('None');
		}
		$memberRights = $member->getOldRights();
		$scope= str_replace('"', '', $memberRights['Rights']['Scope']);
		$rights = array();
		if (stripos($scope, 'All') === false) {
			$rights = explode(',', $scope);
		}
		
		return $rights;
	}
	
    /**
     * get all rights defined or rights allowed for member
     *
     * @access public
     * @return array list of rights
     */
    public function getRights($memberRightsOnly = false, $member = false) {
		$query = "
            SELECT
                *
            FROM
                rights";
		if ($memberRightsOnly) {
            $rights = $this->getAllowedRights();
            if (count($rights) > 0) {
			    $query .= " WHERE
				    Name IN ('" . implode("','", $rights) . "') ";
		    }
        }
		$query .= "
			ORDER BY
                Name
            ";
        $memberRights = array();
        if ($member) {
            $memberRights = $member->getOldRights();
        }
        $result = $this->bulkLookup($query, array('id'));

        foreach($memberRights as $right) {
            if (isset($result[$right['id']])) {
                unset($result[$right['id']]);
            }
        }
        return $result;
    }

    public function checkEditVarsOk($vars) {
        $errors = array();
        if (empty($vars['scope'])) {
            $errors[] = 'AdminRightsScopeEmpty';
        } else {
            // check if scope is well formed
            if (!$this->checkScopeWellFormed($vars['scope'])) {
                $errors[] = 'AdminRightsScopeNotWellFormed';
            }
        }
        if (empty($vars['comment'])) {
            $errors[] = 'AdminRightsCommentEmpty';
        }
        return $errors;
    }

    public function edit($vars) {
        $temp = new Member();
        $member = $temp->findByUsername($vars['username']);
        $query = "
            UPDATE
                rightsvolunteers
            SET
                Level = '" . $this->dao->escape($vars['level']) . "',
                Scope = '" . $this->dao->escape($vars['scope']) . "',
                Comment = '" . $this->dao->escape($vars['comment']) . "',
                Updated = NOW()
            WHERE
                IdMember = " . $member->id . "
                AND IdRight = " . $this->dao->escape($vars['rightid']) . "
            ";
        $this->dao->query($query);
        return true;
    }

    /**
     * Removes a right from a member
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
                rightsvolunteers
            SET
                Level = '0',
                Scope = '" . $this->dao->escape($vars['scope']) . "',
                Comment = '" . $this->dao->escape( $comment ) . "',
                Updated = NOW()
            WHERE
                IdMember = " . $member->id . "
                AND IdRight = " . $this->dao->escape($vars['rightid']) . "
            ";
        $this->dao->query($query);
        return true;
    }

    public function checkCreateVarsOk($vars) {
        $errors = array();
        if (empty($vars['name'])) {
            $errors[] = 'AdminRightsNameEmpty';
        } else {
            $query = "
                SELECT
                    *
                FROM
                    rights r
                WHERE
                    r.Name LIKE '" . $this->dao->escape($vars['name']) . "'";
            $name = $this->singleLookup($query);
            if ($name) {
                $errors[] = 'AdminRightsRightExists';
            }
        }
        if (empty($vars['description'])) {
            $errors[] = 'AdminRightsDescriptionEmpty';
        }
        return $errors;
    }

    public function createRight($vars) {
        $query = "
            INSERT INTO
                rights
            SET
                `Name` = '" . $this->dao->escape($vars['name']) . "',
                `Description` = '" . $this->dao->escape($vars['description']) . "'";
        $this->dao->query($query);

        return true;
    }
}