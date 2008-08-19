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
    
    public function getMyGroups()
    {
        if (!isset($_SESSION['IdMember'])) {
            return array();
        } else {
            return $this->getGroupsForMember($_SESSION['IdMember']);
        }
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
    
    
    /**
     * remember the last visited groups, so 
     *
     * @param int $now_group_id id of the group you are visiting now
     */
    public function setGroupVisit($group_id)
    {
        if (
            (!isset($_SESSION['my_group_visits'])) ||
            (!$group_visits = unserialize($_SESSION['my_group_visits'])) ||
            (!is_array($group_visits))
        ) {
            $group_visits = array();
        }
        $group_visits[$group_id] = microtime(true);
        
        // sort by value, while preserving the keys
        asort($group_visits);
        $_SESSION['my_group_visits'] = serialize(array_slice($group_visits, 0, 5));
        // unset($_SESSION['my_group_visits']);
    }
    
    public function getLastVisited()
    {
        if (
            (!isset($_SESSION['my_group_visits'])) ||
            (!$group_visits = unserialize($_SESSION['my_group_visits'])) ||
            (!is_array($group_visits))
        ) {
            return array();
        } else {
            $groups = array();
            foreach($group_visits as $id => $time) {
                $groups[] = $this->findGroup($id);
            }
            return $groups;
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
    
    
    public function getMembers()
    {
        return $this->getMemberships(30);
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
    
    public function isMember($member_id) {
        $group_id = $this->getData()->id;
        if (!$result = $this->dao->query(
            "
SELECT *
FROM membersgroups
WHERE IdGroup = $group_id
AND IdMember = $member_id
            "
        )) {
            return false;
        } else if (!$member = $result->fetch(PDB::FETCH_OBJ)) {
            return false;
        } else {
            return true;
        }
        
    }

    public function memberJoin($member_id) {
        $group_id = $this->getData()->id;
        $this->dao->query('
INSERT INTO membersgroups 
(IdMember, IdGroup)
VALUES 
(' . $member_id . ', ' . $group_id . ')
');
    }

    public function memberLeave($member_id) {
        $group_id = $this->getData()->id;
        $this->dao->query('
DELETE FROM membersgroups 
WHERE  IdMember = ' . $member_id . '
  AND  IdGroup  = ' . $group_id . '
');
    }

}


?>