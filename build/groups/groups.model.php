<?php


class GroupsModel extends PAppModel
{
    private $_group_list = 0;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function findGroup($group_id)
    {
        $group = new Group($group_id);
        if ($group->getData()) return $group;
        else return 0;
    }
    
    public function getGroups()
    {
        if ($this->_group_list != 0) {
            // do nothing
        } else if (!$result = $this->dao->query(
            "
SELECT *
FROM groups
            "
        )) {
            // db query went wrong
        } else {
            $this->_group_list = array();
            while ($group_data = $result->fetch(PDB::FETCH_OBJ)) {
                $this->_group_list[] = $group_data;
            }
        }
        return $this->_group_list;
    }
    
    public function getGroupsForMember($member_id)
    {
        if (!$result = $this->dao->query(
            "
SELECT groups.*
FROM groups, membersgroups
WHERE membersgroups.IdGroup = groups.id
AND membersgroups.IdMember = $member_id
            "
        )) {
            // db query went wrong
            return 0;
        } else {
            $group_list = array();
            while ($group_data = $result->fetch(PDB::FETCH_OBJ)) {
                $group_list[] = $group_data;
            }
            return $group_list;
        }
    }
}

/**
 * represents a single group
 *
 */
class Group extends PAppModel
{
    private $_group_id;
    private $_group_data = false;
    private $_group_memberships = 0;
    
    public function __construct($group_id)
    {
        parent::__construct();
        $this->_group_id = $group_id;
    }
    
    
    public function getData()
    {
        if ($this->_group_data) {
            // do nothing
        } else if (!$result = $this->dao->query(
            "
SELECT *
FROM groups
WHERE id = $this->_group_id
            "
        )) {
            // db query went wrong
        } else if (!$group_data = $result->fetch(PDB::FETCH_OBJ)) {
            // group not found
        } else {
            $this->_group_data = $group_data;
        }
        return $this->_group_data;
    }
    
    
    public function getMemberships($max_count)
    {
        $members = array();
        
        // TODO: check the $max_count argument
        if ($this->_group_memberships != 0) {
            // nothing to be done
        } else if (!$result = $this->dao->query(
            "
SELECT members.Username, membersgroups.*
FROM membersgroups, members
WHERE membersgroups.IdGroup = $this->_group_id
AND members.id = membersgroups.IdMember
            "
        )) {
            // something went wrong with db query
        } else {
            $memberships = array();
            while ($member = $result->fetch(PDB::FETCH_OBJ)) {
                // TODO: check for $max_count
                $memberships[] = $member;
            }
            $this->_group_memberships = $memberships;
        }
        return $this->_group_memberships;
    }
}


?>