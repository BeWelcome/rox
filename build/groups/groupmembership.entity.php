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

    /**
     * represents membership of a group
     *
     * @package Apps
     * @subpackage Entities
     */
class GroupMembership extends RoxEntityBase
{

    protected $_table_name = 'membersgroups';

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * returns a membership entity for a given group for a member
     *
     * @param object $group - Group to look in
     * @param object $member - Member to look for
     * @access public
     * @return object|false
     */
    public function getMembership(Group $group, Member $member)
    {
        if (!is_object($group) ||  !is_object($member) || !($member_id = $member->getPKValue()) || !($group_id = $group->getPKValue()))
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
    public function getNewGroupMembers(Group $group)
    {
        $where = "created >= CURDATE() - INTERVAL 2 week";

        return $this->getGroupMembers($group, 'In', $where);
    }

    /**
     * return the count of members of the group
     *
     * @param object $group - Group entity object to get members for
     * @access public
     * @return array
     */
    public function getGroupMembersCount($group)
    {
        if (!is_object($group) || !($group_id = $group->getPKValue()))
        {
            return array();
        }

        $sql = "SELECT COUNT(*) AS count FROM members AS m, " . $this->getTableName() 
            . " AS mg WHERE mg.IdGroup = " . $group_id . " AND mg.Status = 'In' "
            . " AND mg.IdMember = m.id AND m.Status IN (" . Member::ACTIVE_ALL . ")";
        
        $rr = $this->dao->query($sql);
        $count = 0;
        if ($rr) {
            $row = $rr->fetch(PDB::FETCH_OBJ);
            $count = $row->count;
        }
        return $count;
    }
    
    /**
     * return the members of the group
     *
     * @param object $group - Group entity object to get members for
     * @param object $status - status to look for
     * @param string $where - Optional where clause to use when finding members
     * @access public
     * @return array
     */
    public function getGroupMembers($group, $status = '', $where = '', $offset = 0, $limit = null, $bylastlogin = false)
    {
        if (!is_object($group) || !($group_id = $group->getPKValue()))
        {
            return array();
        }

        $notLoggedIn = true;
        if (isset($_SESSION["IdMember"])) {
            $notLoggedIn = false;
        }
        $where_clause = "IdGroup = '{$group_id}'" . (($status = $this->dao->escape($status)) ? " AND Status = '{$status}'" : '');
        if (isset($where) && strlen($where))
        {
            $where_clause .= " AND {$where}";
        }        
        $where_clause .= " ORDER BY created";

        $links = $this->findByWhereMany($where_clause);

        $members = array();
        foreach ($links as &$link)
        {
            $members[] = $link->IdMember;
            unset($link);
        }
        unset($links);

        $limit_clause = "";
        $offset_clause = "";
        if ($limit)
        {
            $limit_clause = " LIMIT {$this->dao->escape($limit)}";
        }

        if ($offset)
        {
            $offset_clause = " OFFSET {$this->dao->escape($offset)}";
        }
        
        $orderby = "mg.created ASC";
        if ($bylastlogin == TRUE){
            $orderby = "covertracks DESC";
        }
        
        $sql = "
            SELECT
                m.*,
                IF(m.LastLogin >= CURDATE() - INTERVAL 1 week, (CURDATE() - INTERVAL FLOOR(RAND() * 100) MINUTE), m.LastLogin) as covertracks
                FROM members AS m, {$this->getTableName()} AS mg";
        if ($notLoggedIn) {
            $sql .= ", memberspublicprofiles as mp ";
        }        
        $sql .= " WHERE m.Status IN ( " . Member::ACTIVE_ALL . ") AND m.id IN ('" . implode("','", $members) . "') AND mg.IdMember = m.id AND mg.IdGroup = {$group_id}";
        if ($notLoggedIn) {
            $sql .= " AND mp.IdMember=m.id";
        }
        $sql .= " ORDER BY {$orderby}{$limit_clause}{$offset_clause}";
        return $this->createEntity('Member')->findBySQLMany($sql);
    }

    /**
     * return the groups for a member
     *
     * @param Member $member - member entity to find groups for
     * @param string $status - member status enum('In','WantToBeIn','Kicked','Invited')
     * @access public
     * @return array
     */
    public function getMemberGroups($member, $status = null)
    {
        if (!is_object($member) || !($member_id = $member->getPKValue()))
        {
            return array();
        }

        $links = $this->findByWhereMany("IdMember = '{$member_id}'" . ((!empty($status)) ? " AND Status = '" . $this->dao->escape($status) . "'" : ''));

        $groups = array();
        foreach ($links as &$link)
        {
            $groups[] = $link->IdGroup;
            unset($link);
        }
        unset($links);
        if (empty($groups))
        {
            return array();
        }

        $where = "id IN ('" . implode("','", $groups) . "') ORDER BY Name";
        return $this->createEntity('Group')->findByWhereMany($where);
    }



    /**
     * Check if a member id is connected with a group
     *
     * @param object $member - member entity to check
     * @param object $group - group entity to check
     * @access public
     * @return bool
     */
    public function isMember(Group $group, Member $member, $only_in = true)
    {
        if (!is_object($group) ||  !is_object($member) || !($member_id = $member->getPKValue()) || !($group_id = $group->getPKValue()))
        {
            return false;
        }

        if ($this->findByWhere("IdMember = '{$member_id}' AND IdGroup = '{$group_id}'" . (($only_in) ? " AND Status = 'In'" : '')))
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
     * @param string $status - string containing the membership state, defaults to 'In'
     * @access public
     */
    public function memberJoin(Group $group, Member $member, $status = 'In')
    {
        if (!is_object($group) ||  !is_object($member) || !($member_id = $member->getPKValue()) || !($group_id = $group->getPKValue()))
        {
            return false;
        }

        // only bother if member is not already ... a member        
        if (!$this->isMember($group, $member, false))
        {
            $this->Status = $this->dao->escape($status);
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
    public function memberLeave(Group $group, Member $member)
    {
        if (!is_object($group) ||  !is_object($member) || !($member_id = $member->getPKValue()) || !($group_id = $group->getPKValue()) || !$this->findByWhere("IdMember = '{$member_id}' AND IdGroup = '{$group_id}'"))
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
        if (!$this->isLoaded())
        {
            return false;
        }

        if ($comment)
        {
            $words = $this->getWords();
            $comment_id = ((!$this->Comment) ? $words->InsertInMTrad($this->dao->escape($comment), 'membersgroups.Comment', $this->getPKValue()) : $words->ReplaceInMTrad($this->dao->escape($comment), 'membersgroups.Comment', $this->getPKValue(), $this->Comment));

            if ($comment_id != $this->Comment)
            {
                $this->Comment = $comment_id;
            }
        }
        $this->IacceptMassMailFromThisGroup = $acceptgroupmail;
        $this->notificationsEnabled = ($acceptgroupmail == 'yes' ? 1 : 0);
        $this->updated = date('Y-m-d H:i:s');
        return $this->update();
    }

    /**
     * updates the groupmembership status
     *
     * @param string $status - the new status of the membership
     * @return bool
     * @access public
     */
    public function updateStatus($status)
    {
        if (!$this->isLoaded() || empty($status))
        {
            return false;
        }

        $this->Status = $this->dao->escape($status);
        return $this->update();
    }

}

