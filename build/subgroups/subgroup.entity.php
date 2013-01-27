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
     * @author mahouni
     */

    /**
     * represents subgroup of a group
     *
     * @package Apps
     * @subpackage Entities
     */
class Subgroup extends RoxEntityBase
{

    protected $_table_name = 'groups_subgroups';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if a group id is connected with a group
     *
     * @param object $subgroup - child group entity to check
     * @param object $group - parent group entity to check
     * @access public
     * @return bool
     */
    public function isSubgroup(Group $group, Group $subgroup)
    {
        if (!is_object($group) ||  !is_object($subgroup))
        {
            return false;
        }
        $group_id = $group->getPKValue();
        $subgroup_id = $subgroup->getPKValue();
        $where_clause = "subgroup_id = '{$subgroup_id}' AND group_id = '{$group_id}' AND deletedby IS NULL";
        if ($this->findByWhere($where_clause))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Add a subgroup to a group
     *
     * @param object $group - the group where the subgroup is added
     * @param int $subgroup_id - id of the subgroup
     * @access public
     */
    public function AddSubgroup(Group $group, Group $subgroup, Member $member)
    {
        if (!is_object($group) || !is_object($subgroup) || !is_object($member))
        {
            return false;
        }

        // only bother if subgroup is not already a subgroup       
        if (!$this->isSubgroup($group, $subgroup))
        {
            $this->group_id = $group->getPKValue();
            $this->subgroup_id = $subgroup->getPKValue();
            $this->addedby = $member->getPKValue();
            //$this->created = date('Y-m-d H:i:s');
            if ($this->group_id != $this->subgroup_id)
            {
                return $this->insert();
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


    public function DeleteSubgroup(Member $member)
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        if (!is_object($member) && !is_numeric($member->getPKValue()))
        {
            return false;
        }
        $this->ts = date('Y-m-d H:i:s');
        $this->deletedby = intval($member->getPKValue());
        return $this->update();
    }



    /**
     * return the history of subgroups of the group
     *
     * @param Group $group - Group object to get history of locations for
     * @access public
     * @return array
     */
    public function getSubgroupsLog($group, $offset = 0, $limit = null)
    {
        if (!is_object($group) && !is_numeric($group->getPKValue()))
        {
            return false;
        }
        $group_id = $group->getPKValue();
        $where_clause = "group_id = '{$group_id}'";
        $this->sql_order = "ts DESC";
        $logs = $this->findByWhereMany($where_clause, $offset, $limit);
        foreach ($logs as &$log) {
            $log->subgroup = $this->createEntity('Group', $log->subgroup_id);
            if ($log->deletedby == "")
            {
                $log->member = $this->createEntity('Member', $log->addedby);
                $log->SubgroupAction = "AddedSubgroup";
            } else {
                $log->member = $this->createEntity('Member', $log->deletedby);
                $log->SubgroupAction = "RemovedSubgroup";
            }
            
        }
        return $logs;
        

    }




}











