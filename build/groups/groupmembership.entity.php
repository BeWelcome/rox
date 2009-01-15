<?php

//todo: base group atom on different class

/**
 * represents a single group
 *
 */
class GroupMembership extends RoxEntityBase
{

    public function __construct($ini_data)
    {
        parent::__construct($ini_data);
    }


    /**
     * returns a membership entity for a given group for a member
     *
     * @param object $group - Group to look in
     * @param object $member - Member to look for
     * @access public
     * @return object
     */
    public function getMembership($group, $member)
    {
        if (!is_object($group) ||
            !is_object($member) ||
            !($gpk = $group->getPrimaryKey()) ||
            !($mpk = $member->getPrimaryKey()) ||
            !($member_id = intval($member->$mpk)) ||
            !($group_id = intval($group->$gpk)))
        {
            return false;
        }

        return $this->findByWhere("IdMember = {$member_id} AND IdGroup = {$group_id}");
        
    }

    /**
     * return the members of the group that have joined in the last two weeks
     *
     * @param object $group - A group entity object
     * @access public
     * @return array
     */
    public function getNewGroupMembers($group)
    {
        $where = "created >= CURDATE() - INTERVAL 2 week";

        return $this->getGroupMembers($group, $where);
    }


    /**
     * return the members of the group
     *
     * @param object $group - Group entity object to get members for
     * @param string $where - Optional where clause to use when finding members
     * @access public
     * @return array
     */
    public function getGroupMembers($group, $where = '')
    {
        if (!is_object($group) ||
            !($gpk = $group->getPrimaryKey()) || 
            !($group_id = intval($group->$gpk)))
        {
            return false;
        }


        $where_clause = "IdGroup = '{$group_id}' AND Status = 'In'";
        if (isset($where) && strlen($where))
        {
            $where_clause .= " AND {$where}";
        }

        $links = $this->findByWhereMany($where_clause);

        $members = array();
        foreach ($links as &$link)
        {
            $members[] = $link->IdMember;
            unset($link);
        }
        unset($links);
        
        $where = "id IN ('" . implode("','", $members) . "')";
        return $this->_entity_factory->create('Member')->findByWhereMany($where);
    }

    /**
     * return the groups for a member
     *
     * @param object $member - member entity to find groups for
     * @access public
     * @return array
     */
    public function getMemberGroups($member)
    {
        if (!is_object($member) ||
            !($mpk = $member->getPrimaryKey()) ||
            !($member_id = intval($member->$mpk)))
        {
            return false;
        }

        $links = $this->findByWhereMany("IdMember = '{$member_id}'");

        $groups = array();
        foreach ($links as &$link)
        {
            $groups[] = $link->IdGroup;
            unset($link);
        }
        unset($links);

        $where = "id IN ('" . implode("','", $groups) . "')";
        return $this->_entity_factory->create('Group')->findByWhereMany($where);
    }



    /**
     * Check if a member id is connected with a group
     *
     * @param object $member - member entity to check
     * @param object $group - group entity to check
     * @access public
     * @return bool
     */
    public function isMember($group, $member)
    {
        if (!is_object($group) ||
            !is_object($member) ||
            !($gpk = $group->getPrimaryKey()) ||
            !($mpk = $member->getPrimaryKey()) ||
            !($member_id = intval($member->$mpk)) ||
            !($group_id = intval($group->$gpk)))
        {
            return false;
        }

        if ($yeah = $this->findByWhere("IdMember = '{$member_id}' AND IdGroup = '{$group_id}'"))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * puts a member in a group, aka joining the group
     *
     * @param object $group - the group the member joins
     * @param int $member_id - id of the member that joins
     * @access public
     * @todo add return vals
     */
    public function memberJoin($group, $member)
    {
        if (!is_object($group) ||
            !is_object($member) ||
            !($gpk = $group->getPrimaryKey()) ||
            !($mpk = $member->getPrimaryKey()) ||
            !($member_id = intval($member->$mpk)) ||
            !($group_id = intval($group->$gpk)))
        {
            return false;
        }

        // only bother if member is not already ... a member        
        if (!$this->isMember($group, $member))
        {
            if ($group->Type == "NeedAcceptance")
            {
                $status = "WantToBeIn"; // case this is a group with an admin
                // Notfiy the group accepter
                $group->notifyGroupAdmin($group, $member_id, isset($_GET['Comment']) ? $_GET['Comment'] : '');
            }
            else
            {
                $status = "In";
            }

            $this->Status = $status;
            $this->IdGroup = $group_id;
            $this->IdMember = $member_id;
            $this->created = date('Y-m-d H:i:s');

            return $this->insert();

        }
        else
        {
            return false;
        }
    }

    /**
     * Deletes a member from a group
     *
     * @param object $group - Group to leave
     * @param object $member - Member that leaves
     * @access public
     * @return bool
     */
    public function memberLeave($group, $member)
    {
        if (!is_object($group) ||
            !is_object($member) ||
            !($gpk = $group->getPrimaryKey()) ||
            !($mpk = $member->getPrimaryKey()) ||
            !($member_id = intval($member->$mpk)) ||
            !($group_id = intval($group->$gpk)) ||
            !$this->findByWhere("IdMember = '{$member_id}' AND IdGroup = '{$group_id}'"))
        {
            return false;
        }

        return $this->delete();
    }

    /**
     * updates a groupmembership object
     *
     * @param string $acceptgroupmail
     * @param string $comment
     * @access public
     * @return bool
     */
    public function updateMembership($acceptgroupmail, $comment)
    {
        $this->IacceptMassMailFromThisGroup = $acceptgroupmail;
        $this->updated = date('Y-m-d H:i:s');
        return $this->update();
    }

}

