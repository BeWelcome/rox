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
     * represents a single gallery
     *
     * @package Apps
     * @subpackage Entities
     */
class GalleryItem extends RoxEntityBase
{
    protected $_table_name = 'gallery_items';

    public function __construct($group_id = false)
    {
        parent::__construct();
        if (intval($group_id))
        {
            $this->findById(intval($group_id));
        }
    }
    
    /**
     * return the items of a gallery
     *
     * @param object $gallery - Gallery entity object to get items for
     * @param object $status - status to look for
     * @param string $where - Optional where clause to use when finding items
     * @access public
     * @return array
     */
    public function getGalleryItems($gallery, $status = '', $where = '', $offset = 0, $limit = null)
    {
        if (!is_object($gallery) || !($group_id = $group->getPKValue()))
        {
            return array();
        }

        $where_clause = "IdGroup = '{$group_id}'" . (($status = $this->dao->escape($status)) ? " AND Status = '{$status}'" : '');
        if (isset($where) && strlen($where))
        {
            $where_clause .= " AND {$where}";
        }
        $where_clause .= " ORDER BY created";

        if ($limit)
        {
            $where_clause .= " LIMIT {$this->dao->escape($limit)}";
        }

        if ($offset)
        {
            $where_clause .= " OFFSET {$this->dao->escape($offset)}";
        }

        $links = $this->findByWhereMany($where_clause);

        $members = array();
        foreach ($links as &$link)
        {
            $members[] = $link->IdMember;
            unset($link);
        }
        unset($links);
        
        $sql = "SELECT m.* FROM members AS m, {$this->getTableName()} AS mg WHERE m.Status IN ('Active', 'Pending') AND m.id IN ('" . implode("','", $members) . "') AND mg.IdMember = m.id AND mg.IdGroup = {$group_id} ORDER BY mg.created ASC";
        return $this->createEntity('Member')->findBySQLMany($sql);
    }

}

