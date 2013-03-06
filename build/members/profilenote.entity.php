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
     * @author Fake51
     */

    /**
     * represents a single entry in the mycontacts table (notes)
     *
     * @author Fake51
     * @package    Apps
     * @subpackage Entities
     */
class ProfileNote extends RoxEntityBase
{

    protected $_table_name = 'mycontacts';

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id)
        {
            $this->findById(intval($id));
        }
    }
    
    /**
     * returns the notes for a member
     *
     * @param object $member - member entity to find groups for
     * @access public
     * @return array
     */
    public function getNotes($member)
    {
        if (!is_object($member) || !($member_id = $member->getPKValue()))
        {
            return array();
        }

        $links = $this->findByWhereMany("IdMember = '{$member_id}'");

        $notes = array();
        foreach ($links as &$link)
        {
            $notes[] = $link->id;
            unset($link);
        }
        unset($links);
        if (empty($notes))
        {
            return array();
        }

        $where = "id IN ('" . implode("','", $notes) . "') ORDER BY Category";
        return $this->createEntity('ProfileNote')->findByWhereMany($where);
    }

    /**
     * returns the note written for a $member by $writer
     *
     * @param object $member - member entity to find groups for
     * @access public
     * @return array
     */
    public function getNote($from, $for)
    {
        if (!is_object($from) || !($from_id = $from->getPKValue()))
        {
            return array();
        }
        if (!is_object($for) || !($for_id = $for->getPKValue()))
        {
            return array();
        }
        $where = "IdMember = {$from_id} AND IdContact = {$for_id}";
        return $this->createEntity('ProfileNote')->findByWhereMany($where);
    }
}
