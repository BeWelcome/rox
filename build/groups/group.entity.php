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
     * @author Fake51
     */

use App\Doctrine\MemberStatusType;

/**
     * represents a single group
     *
     * @package Apps
     * @subpackage Entities
     */
class Group extends RoxEntityBase
{

    protected $_table_name = 'groups';

    protected $_validations = array('Name', 'Type');

    /**
     * overrides the __get method of Component
     * in order fix the output
     *
     * @param string $key - variable to get
     * @return mixed
     * @access public
     */
    public function __get($key)
    {
        $result = parent::__get($key);
        if (is_scalar($result) && $result !== false)
        {
            $result = stripslashes($result);
        }
        return $result;
    }

    public function __construct($groupId = false)
    {
        parent::__construct();
        if (intval($groupId))
        {
            $this->findById(intval($groupId));
        }
    }

    /*
     * Loads the entity based on the data array.
     *
     * Additionally gets the timestamp of the latest post to the group
     *
     */
    protected function loadEntity(array $data)
    {
        if ($status = parent::loadEntity($data))
        {
            // get latest post timestamp (if any)
            $query = "SELECT UNIX_TIMESTAMP(MAX( p.create_time )) AS ts
FROM groups AS g, forums_threads AS t, forums_posts AS p
WHERE g.id = " . $this->id . "
AND g.id = t.IdGroup
AND t.last_postid = p.id";
            if ($result = $this->dao->query($query)) {
                $timestamp = $result->fetch(PDB::FETCH_OBJ);
                $this->latestPost = $timestamp->ts;
            } else {
                $this->latestPost = 0;
            }
        }
        return $status;
    }

    /**
     * @inheritDoc
     */
    public function countAll()
    {
        $sql = <<<SQL
SELECT
    count(id) as count
FROM
(SELECT
    g.id
FROM
    groups g,
    forums_threads ft,
    forums_posts fp
where g.id = ft.IdGroup AND g.approved AND NOT (g.Name LIKE '[Archived]%')  AND ft.last_postId = fp.id AND DateDIFF(now(), fp.create_time) < 366
group by g.id) as id
SQL;
        return $this->sqlCount($sql);
    }

    /**
     * @inheritDoc
     */
    public function findAll($offset = 0, $limit = 0)
    {
        $sql = <<<SQL
SELECT
    g.*
FROM
    groups g,
    forums_threads ft,
    forums_posts fp
WHERE g.id = ft.IdGroup AND g.approved = 1 AND NOT (g.Name LIKE '[Archived]%') AND ft.last_postId = fp.id AND DateDIFF(NOW(), fp.create_time) < 366
GROUP BY g.id
SQL;
        if ($this->sql_order !== '') {
            $sql .= "\nORDER BY " . $this->sql_order;
        }
        $sql .= " LIMIT {$limit} OFFSET {$offset}";

        return $this->findBySQLMany($sql);
    }

    /**
     * Uses an array of terms to create a create to search for groups with
     * simple or search on names for now
     *
     * @param array $terms - array of strings to be used in search
     * @param int $offset
     * @param int $limit
     * @return mixed false or group of arrays that match any of the terms
     * @access public
     * @todo implement proper group search - this will wait on various db implementations
     */
    public function findBySearchTerms($terms = array(), $offset = 0, $limit = 10)
    {
        if (empty($terms))
        {
            return $this->findAll($offset, $limit);
        }

        foreach ($terms as &$term)
        {
            if (is_string($term))
            {
                $term = "{$this->_table_name}.Name LIKE '%" . $this->dao->escape($term) . "%'";
            }
            else
            {
                unset($term);
            }
        }

        $clause = implode(' or ', $terms);

        return $this->findByWhereMany('approved = 1 AND (' . $clause . ')', $offset, $limit);

    }

    /**
     * return the members of the group that have joined in the last two weeks
     *
     * @access public
     * @return array
     */
    public function getNewMembers()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        return $this->createEntity('GroupMembership')->getNewGroupMembers($this);
    }


    /**
     * return the members of the group
     *
     * @param string $status - which status to check for (In, WantToBeIn, Kicked)
     * @param int $offset
     * @param int $limit
     * @access public
     * @return array
     */
    public function getMembers($status = false, $offset = 0, $limit = null)
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        $status = (($status) ? $status : 'In');

        return $this->createEntity('GroupMembership')->getGroupMembers($this, $status, '', $offset, $limit);
    }


    /**
     * return the x recently logged in members of the group
     *
     * @param int $numberOfMembers- number of last logged in members to return
     * @access public
     * @return array - GroupMembership entities of x recently logged in members
     */
    public function getLastLoggedInMembers($numberOfMembers = 20)
    {
        if (!$this->_has_loaded || !is_int($numberOfMembers))
        {
            return false;
        }

        $bylastlogin = true;
        $memberships = $this->createEntity('GroupMembership')->getGroupMembers($this, 'In', '', 0, null, $bylastlogin);
        $ms_lastloggedin = array_slice($memberships, 0, $numberOfMembers);
        return $ms_lastloggedin;
    }



    /**
     * return the members of the group accepting email from the other group members
     *
     * @param string $status - which status to check for (In, WantToBeIn, Kicked)
     * @access public
     * @return array
     */
    public function getEmailAcceptingMembers()
    {
        if (!$this->_has_loaded)
        {
            return array();
        }

        return $this->createEntity('GroupMembership')->getGroupMembers($this, 'In', 'IacceptMassMailFromThisGroup = "yes"');
    }

    /**
     * return the members of the group
     *
     * @param string $status - which status to check for (In, WantToBeIn, Kicked)
     * @access public
     * @return int
     */
    public function getMemberCount($status = false)
    {
        if (!$this->_has_loaded)
        {
            return 0;
        }

        $status = (($status) ? $status : 'In');

        return $this->createEntity('GroupMembership')->getGroupMembersCount($this);

    }



    /**
     * Check if a member id is connected with a group
     *
     * @param int $memberId - id of the member to check
     * @access public
     * @return bool
     */
    public function isMember($memberId)
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        return $this->createEntity('GroupMembership')->isMember($this, $memberId);
    }

    /**
     * puts a member in a group, aka joining the group
     *
     * @param int $memberId - id of the member that joins
     * @access public
     * @return bool
     */
    public function memberJoin($memberId, $status)
    {
        if ($this->_has_loaded === false)
        {
            return false;
        }

        return $this->createEntity('GroupMembership')->memberJoin($this, $memberId, $status);
    }

    /**
     * deletes a member from a group, aka leaving the group
     *
     * @param object $member - the member that leaves
     * @access public
     * @return bool
     */
    public function memberLeave(Member $member)
    {
        if ($this->_has_loaded === false)
        {
            return false;
        }

        if ($this->isGroupOwner($member))
        {
            $this->removeGroupOwner($member);
        }

        return $this->createEntity('GroupMembership')->memberLeave($this, $member);
    }

    /**
     * Create a group given some input
     *
     * @param array $input - array containing Group_  and Type
     * @access public
     * @return mixed Will return the insert id of the operation or false
     */
    public function createGroup($input)
    {
        $group_name = $this->dao->escape($input['Group_']);
        $type = $this->dao->escape($input['Type']);
        $visible_posts = $this->dao->escape($input['VisiblePosts']);
        $picture = ((!empty($input['Picture'])) ? $this->dao->escape($input['Picture']) : '');

        if ($this->createEntity('Group')->findByWhere("Name = '{$group_name}'"))
        {
            return false;
        }

        $this->Name = $group_name;
        $this->Type = $type;
        $this->VisiblePosts = $visible_posts;
        $this->Picture = $picture;
        $this->created = date('Y-m-d H:i:s');
        return $this->insert();
    }

    /**
     * Delete a group
     * Removes a row from the groups table and unsets data in the entity so it can't be reused
     *
     * @access public
     * @return bool
     */
    public function deleteGroup()
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        $members = $this->getMembers();
        foreach ($members as $member)
        {
            $this->memberLeave($member);
        }

        if ($this->delete())
        {
            $this->memberships = false;
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * sets or updates the description for a group
     *
     * @param string $description - string describing the group
     * @return bool
     * @access public
     */
    public function setDescription($description)
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        $description = str_replace(array("\r\n", "\r", "\n"), "", nl2br($description));

        $words = $this->getWords();
        $descriptionId = ((!$this->IdDescription) ? $words->InsertInMTrad($this->dao->escape($description), 'groups.IdDescription', $this->getPKValue()) : $words->ReplaceInMTrad($description, 'groups.IdDescription', $this->getPKValue(), $this->IdDescription));

        if (!$descriptionId)
        {
            return false;
        }
        elseif ($this->IdDescription != $descriptionId)
        {
            $this->IdDescription = $descriptionId;
            return $this->update();
        }

        return true;
    }

    /**
     * returns the description for a group
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        if (!$this->isLoaded() || !$this->IdDescription)
        {
            return '';
        }

        return $this->getWords()->mTrad($this->IdDescription);
    }

    /**
     * updates a groups settings
     *
     * @param string $description - the description of the group
     * @param string $type - how public the group is
     * @param string $visible_posts - if the forum posts of the group should be visible or not
     * @access public
     * @return bool
     */
    public function updateSettings($description, $type, $visible_posts, $visible_comment, $picture = '')
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        if (!$this->setDescription($description))
        {
            return false;
        }

        $this->Type = $this->dao->escape($type);
        $this->VisiblePosts = $this->dao->escape($visible_posts);
        $this->Picture = (($picture) ? $this->dao->escape($picture) : $this->Picture);
        return $this->update();
    }

    /**
     * checks whether a given member entity is the owner of the group
     *
     * @param object $member - entity to check for
     * @return bool
     * @access public
     */
    public function isGroupAdmin($member)
    {
        if (!is_object($member) || !$member->isPKSet() || !$this->isLoaded())
        {
            return false;
        }

        $role = $this->createEntity('Role')->findByName('GroupsAdmin');
        return (($member->hasRole($role)) ? true : false);
    }


    /**
     * checks whether a given member entity is the owner of the group
     *
     * @param object $member - entity to check for
     * @return bool
     * @access public
     */
    public function isGroupOwner($member)
    {
        if (!is_object($member) || !$member->isPKSet() || !$this->isLoaded())
        {
            return false;
        }

        $found = false;
        $groupOwners = $this->getGroupOwners();
        if ($groupOwners) {
            foreach($groupOwners as $groupOwner)
            {
                if ($groupOwner->id == $member->id) {
                    $found = true;
                    break;
                }
            }
        }

        return $found;
    }


    /**
     * returns member entities representing the group owners, if group has owners
     *
     * @return mixed - member entities or false
     * @access public
     */
    public function getGroupOwners()
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        $role = $this->createEntity('Role')->findByName('GroupOwner');
        $priv_scopes = $this->createEntity('PrivilegeScope')->getMembersWithRoleObjectAccess($role, $this);
        if (!$priv_scopes)
        {
            return false;
        }
        $loggedIn = false;
        if ($this->getLoggedInMember()) {
            $loggedIn = true;
        }

        $group_owners = array();
        foreach ($priv_scopes as $priv_scope) {
            $group_owner = $this->createEntity('Member', $priv_scope->IdMember);
            if (strpos(MemberStatusType::ACTIVE_WITH_MESSAGES, $group_owner->Status) !== false) {
                if ($loggedIn) {
                    $group_owners[] = $group_owner;
                }
            }
        }
        return $group_owners;
    }

    /**
     * sets ownership for a group - owner has admin powers + more for a group
     *
     * @param Member $member
     * @access public
     * @return bool
m     */
    public function setGroupOwner(Member $member)
    {
        /** @var $role \Role */
        if (!$this->isLoaded() || !($role = $this->createEntity('Role')->findByName('GroupOwner')) || !$this->isMember($member))
        {
            return false;
        }

        return $role->addForMember($member, array('Group' => $this->getPKValue()));
    }

    /**
     * removes ownership of group from member
     *
     * @access public
     * @return bool
     */
    public function removeGroupOwner($member)
    {
        if (!$this->isLoaded() || !is_object($member) || !$this->isGroupOwner($member))
        {
            return false;
        }
        if (!($role = $this->createEntity('Role')->findByName('GroupOwner')))
        {
            return false;
        }
        return $role->removeFromMember($member, $role->getScopesForMemberRole($member, $this->getPKValue()));
    }

    /**
     * finds a local group for a geo location
     *
     * @param object $geo
     * @access public
     * @return object|false
     */
    public function getGroupForGeo(Geo $geo, $local = false)
    {
        if (!$geo->isLoaded())
        {
            return false;
        }
        return $this->findByWhere("IdGeoname = '{$geo->getPKValue()}'" . (($local) ? " AND IsLocal = TRUE" : ''));
    }


    /**
     * find related groups for a group
     *
     * @param int $groupId - id of the group
     * @return mixed false or array of groups that are related with the group
     * @access public
     */
    public function findRelatedGroups($groupId, $offset = 0, $limit = null)
    {
        if (!is_numeric($groupId)) {
            return false;
        }
        $where = "{$this->_table_name}.id IN  (
SELECT gr.related_id
FROM groups_related as gr
WHERE gr.group_id = " . intval($groupId) . " AND gr.deletedby IS NULL
ORDER BY group_id)";

        return $this->findByWhereMany($where, $offset, $limit);
    }


}

