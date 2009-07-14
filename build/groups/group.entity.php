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
 * represents a single group
 *
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
        if (is_scalar($result))
        {
            $result = stripslashes($result);
        }
        return $result;
    }

    public function __construct($group_id = false)
    {
        parent::__construct();
        if (intval($group_id))
        {
            $this->findById(intval($group_id));
        }
    }


    /**
     * Uses an array of terms to create a create to search for groups with
     * simple or search on names for now
     *
     * @todo implement proper group search - this will wait on various db implementations
     * @param array $terms - array of strings to be used in search
     * @return mixed false or group of arrays that match any of the terms
     * @access public
     */
    public function findBySearchTerms($terms = array(), $offset, $limit = 10)
    {
        if (empty($terms))
        {
            return $this->findAll($page, 10);
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

        return $this->findByWhereMany($clause, $offset, $limit);

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
            return false;
        }

        return $this->createEntity('GroupMembership')->getGroupMembers($this, 'In', 'IacceptMassMailFromThisGroup = "yes"');
    }

    /**
     * return the members of the group
     *
     * @param string $status - which status to check for (In, WantToBeIn, Kicked)
     * @access public
     * @return array
     */
    public function getMemberCount($status = false)
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        $status = (($status) ? $status : 'In');

        return count($this->createEntity('GroupMembership')->getGroupMembers($this, $status));
        
    }



    /**
     * Check if a member id is connected with a group
     *
     * @param int $member_id - id of the member to check
     * @access public
     * @return bool
     */
    public function isMember($member)
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        return $this->createEntity('GroupMembership')->isMember($this, $member);
    }

    /**
     * puts a member in a group, aka joining the group
     *
     * @param int $member_id - id of the member that joins
     * @access public
     * @return bool
     */
    public function memberJoin($member, $status)
    {
        if ($this->_has_loaded === false)
        {
            return false;
        }

        return $this->createEntity('GroupMembership')->memberJoin($this, $member, $status);
    }

    /**
     * deletes a member from a group, aka leaving the group
     *
     * @param object $member - the member that leaves
     * @access public
     * @return bool
     */
    public function memberLeave($member)
    {
        if ($this->_has_loaded === false)
        {
            return false;
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
        $picture = ((!empty($input['Picture'])) ? $this->dao->escape($input['Picture']) : '');

        if ($this->createEntity('Group')->findByWhere("Name = '{$group_name}'"))
        {
            return false;
        }

        $this->Name = $group_name;
        $this->Type = $type;
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
        $description_id = ((!$this->IdDescription) ? $words->InsertInMTrad($this->dao->escape($description), 'groups.IdDescription', $this->getPKValue()) : $words->ReplaceInMTrad($this->dao->escape($description), 'groups.IdDescription', $this->getPKValue(), $this->IdDescription));

        if (!$description_id)
        {
            return false;
        }
        elseif ($this->IdDescription != $description_id)
        {
            $this->IdDescription = $description_id;
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
    public function updateSettings($description, $type, $visible_posts, $picture = '')
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
    public function isGroupOwner($member)
    {
        if (!is_object($member) || !$member->isPKSet() || !$this->isLoaded())
        {
            return false;
        }

        $role = $this->createEntity('Role')->findByName('GroupOwner');
        return (($member->hasRole($role, $this)) ? true : false);
    }

    /**
     * returns a member entity representing the group owner, if there is one
     *
     * @return mixed - member entity or false
     * @access public
     */
    public function getGroupOwner()
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        $role = $this->createEntity('Role')->findByName('GroupOwner');
        $priv_scope = $this->createEntity('PrivilegeScope')->getMemberWithRoleObjectAccess($role, $this);
        if (!$priv_scope)
        {
            return false;
        }
        return $this->createEntity('Member', $priv_scope->IdMember);
    }

    /**
     * sets ownership for a group - owner has admin powers + more for a group
     *
     * @param object $member
     * @access public
     * @return bool
     */
    public function setGroupOwner($member)
    {
        if (!$this->isLoaded() || !($role = $this->createEntity('Role')->findByName('GroupOwner')))
        {
            return false;
        }

        // if any previous owner is set, remove previous owner first
        if ($prev_owner = $this->getGroupOwner())
        {
            $role->removeFromMember($prev_owner, $role->getScopesForMemberRole($prev_owner, $this->getPKValue()));
        }

        return $role->addForMember($member, array('Group' => $this->getPKValue()));
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

}

