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
     * represents related group of a group
     *
     * @package Apps
     * @subpackage Entities
     */
class RelatedGroup extends RoxEntityBase
{

    protected $_table_name = 'groups_related';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if a group id is connected with a group
     *
     * @param object $relatedgroup - child group entity to check
     * @param object $group - parent group entity to check
     * @access public
     * @return bool
     */
    public function isRelatedGroup(Group $group, Group $relatedgroup)
    {
        if (!is_object($group) ||  !is_object($relatedgroup)) {
            return false;
        }
        $groupId = $group->getPKValue();
        $relatedgroupId = $relatedgroup->getPKValue();
        $where_clause = "related_id = '{$relatedgroupId}' AND group_id = '{$groupId}' AND deletedby IS NULL";
        if ($this->findByWhere($where_clause)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a related group to a group
     *
     * @param object $group - the group where the related group is added
     * @param int $relatedgroupId - id of the related group
     * @access public
     */
    public function AddRelatedGroup(Group $group, Group $relatedgroup, Member $member)
    {
        if (!is_object($group) || !is_object($relatedgroup) || !is_object($member)) {
            return false;
        }

        // only bother if related group is not already a related group
        if (!$this->isRelatedGroup($group, $relatedgroup)) {
            $this->group_id = $group->getPKValue();
            $this->related_id = $relatedgroup->getPKValue();
            $this->addedby = $member->getPKValue();
            //$this->created = date('Y-m-d H:i:s');
            if ($this->group_id != $this->related_id) {
                return $this->insert();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function deleteRelatedGroup(Member $member)
    {
        if (!$this->isLoaded()) {
            return false;
        }
        if (!is_object($member) && !is_numeric($member->getPKValue())) {
            return false;
        }
        $this->ts = date('Y-m-d H:i:s');
        $this->deletedby = intval($member->getPKValue());
        return $this->update();
    }



    /**
     * return the history of related groups of the group
     *
     * @param Group $group - Group object to get history of locations for
     * @access public
     * @return array
     */
    public function getRelatedGroupsLog($group, $offset = 0, $limit = null)
    {
        if (!is_object($group) && !is_numeric($group->getPKValue())) {
            return false;
        }
        $groupId = $group->getPKValue();
        $where_clause = "group_id = '{$groupId}'";
        $this->sql_order = "ts DESC";
        $logs = $this->findByWhereMany($where_clause, $offset, $limit);
        foreach ($logs as &$log) {
            $log->relatedgroup = $this->createEntity('Group', $log->related_id);
            if ($log->deletedby == "") {
                $log->member = $this->createEntity('Member', $log->addedby);
                $log->RelatedGroupAction = "AddedRelatedGroup";
            } else {
                $log->member = $this->createEntity('Member', $log->deletedby);
                $log->RelatedGroupAction = "RemovedRelatedGroup";
            }
        }
        return $logs;
    }

}


