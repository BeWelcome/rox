<?php

/*
Copyright (c) 2009 BeVolunteer

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
     * @package    Apps
     * @subpackage Entities
     * @author     Fake51
     * @copyright  2009 BeVolunteer
     * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL 2
     * @link       http://www.bewelcome.org
     */

    /**
     * represents a single group
     *
     * @package    Apps
     * @subpackage Entities
     * @author     Fake51
     */
class ProfileVisit extends RoxEntityBase
{
    protected $_table_name = 'profilesvisits';

    /**
     * records a visit on one members profile from another member
     *
     * @param Member $visited - visited profile
     * @param Member $visitor - visiting member
     *
     * @access public
     * @return bool
     */
    public function recordVisit(Member $visited, Member $visitor)
    {
        if (!$visited->isLoaded() || !$visitor->isLoaded())
        {
            return false;
        }
        // todo: refactor pending implementation of replace method in Entity
        // todo: fix bad table model (created column is always updated when no value is set)
        $sql = "REPLACE INTO profilesvisits (IdMember, IdVisitor, updated) VALUES ({$visited->getPKValue()}, {$visitor->getPKValue()}, now())";
        $result = $this->dao->query($sql);
    }

    /**
     * returns all members, that have visited $members profile
     *
     * @param Member $member - profile to check visits for
     *
     * @access public
     * @return array
     */
    public function getVisitsForMember(Member $member)
    {
        if (!$member->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("IdMember = {$member->getPKValue()} ORDER BY updated DESC");
    }

    /**
     * returns a subset of the profile visits for a member
     *
     * @param Member      $member - profile to check
     * @param PagerWidget $pager  - pager containing data on subset
     *
     * @access public
     * @return array
     */
    public function getVisitingMembersSubset(Member $member, PagerWidget $pager)
    {
        if (!$member->isLoaded())
        {
            return array();
        }
        $return = array();
        if ($result = $this->dao->query(<<<SQL
            SELECT 
                m.*, 
                p.updated 
            FROM 
                members AS m,
                {$this->getTableName()} AS p 
            WHERE 
                m.id = p.IdVisitor 
                AND p.IdMember = {$member->getPKValue()} 
                AND m.Status NOT IN ('Banned', 'TakenOut', 'Rejected','ActiveHidden', 'Buggy') 
            ORDER BY 
                p.updated DESC 
            LIMIT {$pager->getActiveStart()}, {$pager->getActiveLength()}
SQL
        )) {
            while ($row = $result->fetch(PDB::FETCH_ASSOC))
            {
                $m = $this->createEntity('Member')->loadFromArray($row);
                $m->visited = $row['updated'];
                $return[] = $m;
            }
        }
        return $return;
    }

    /**
     * returns number of members visiting $member
     *
     * @param Member $member - profile to check count for
     *
     * @access public
     * @return int
     */
    public function getVisitCountForMember(Member $member)
    {
        if (!$member->isLoaded())
        {
            return 0;
        }
        return $this->sqlCount("
            SELECT 
                COUNT(m.id) 
            FROM 
                members AS m, 
                {$this->getTableName()} AS p 
            WHERE 
                p.IdMember = {$member->getPKValue()} 
                AND p.IdVisitor = m.id
        ");
    }
}
