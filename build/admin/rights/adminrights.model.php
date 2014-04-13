<?php

/**
 * Class AdminRightsModel
 */
class AdminRightsModel extends RoxModelBase {

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
        }
        if ($vars['level'] == 0) {
            $errors[] = 'AdminRightsNoLevelSelected';
        }
        if (empty($vars['scope'])) {
            $errors[] = 'AdminRightsScopeEmpty';
        } else {
            // check if scope is well formed
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
            UPDATE
                rightsvolunteers
            SET
                IdRight = '" . $this->dao->escape($vars['right']) . "',
                IdMember = '" . $member->id . "',
                Scope = '" . $this->dao->escape($vars['scope']) . "',
                Level = '" . $this->dao->escape($vars['level']) . "'";
        error_log($query);
        $this->dao->query($query);
    }

    /**
     * get list of members which have a right assigned
     *
     * @access public
     * @return list of members
     */
    public function getMembers($includeLevelZero = false)
    {
        $query = '
            SELECT
                m.Username,
                m.id as id,
                m.status
            FROM
                rights r,
                rightsvolunteers rv,
                members m
            WHERE
                m.Status in (' . Member::ACTIVE_ALL . ')
                AND rv.IdMember = m.id
                AND rv.IdRight = r.id';
        if (!$includeLevelZero) {
            $query .= ' AND rv.Level <> 0';
        }
        $query .= '
            ORDER BY
                m.Username
            ';
        return $this->bulkLookup($query);
    }

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
                rv.Scope
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
            $rightsWithMembers[$rwm->rightId]->Members[$rwm->id] = $memberDetails;
        }
        return $rightsWithMembers;
    }

    /**
     * get all rights defined
     *
     * @access public
     * @return array list of rights
     */
    public function getRights() {
        $query = '
            SELECT
                *
            FROM
                rights
            ORDER BY
                Name
            ';
        return $this->bulkLookup($query, array('id'));
    }

    public function getRightScopeAndLevelForMember($member, $rightId) {
        return array(5, '"All", "alot", "and some more"');
    }
}