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

require_once SCRIPT_BASE . 'build/groups/group.entity.php';

class GeoGroup extends Group
{

    /**
     * return the members of the group that have joined in the last two weeks
     *
     * @todo fix this to work with local members
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
     * @param bool $local
     * @access public
     * @return array
     */
    public function getMembers($status = false, $offset = 0, $limit = null, $local = false)
    {
        return parent::getMembers($status, $offset, $limit, true);
    }


    /**
     * return the members of the group
     *
     * @param string $status - which status to check for (In, WantToBeIn, Kicked)
     * @access public
     * @return array
     */
    public function getMemberCount($status = false, $local = false)
    {
        return parent::getMemberCount($status, true);
    }


    /**
     * puts a member in a group, aka joining the group
     *
     * @param int $member_id - id of the member that joins
     * @access public
     * @return bool
     */
    public function memberJoin($member, $status, $is_local = false)
    {
        return parent::memberJoin($member, $status, true);
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
        return false;
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
        return false;
    }

    /**
     * returns a member entity representing the group owner, if there is one
     *
     * @return mixed - member entity or false
     * @access public
     */
    public function getGroupOwner()
    {
        return false;
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
        return false;
    }

    /**
     * finds a local group for a geo location
     *
     * @param object $geo
     * @access public
     * @return object|false
     */
    public function getGroupForGeo(Geo $geo, $local = null)
    {
        return parent::getGroupForGeo($geo, true);
    }

    /**
     * creates group for a given geo entity
     *
     * @param object $geo
     * @access public
     * @return object
     */
    public function lazyCreateGeoGroup(Geo $geo)
    {
        if ($this->isLoaded())
        {
            $group = $this->createEntity('Group');
        }
        else
        {
            $group = $this;
        }
        $group->Name = $geo->name;
        $group->Type = 'Public';
        $group->IdGeoname = $geo->getPKValue();
        $group->IsLocal = true;
        $group->insert();
        return $group;
    }

    /**
     * returns the geo entity the group is connected to
     *
     * @access public
     * @return object
     */
    public function getGeo()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Geo')->findById($this->IdGeoname);
    }

    /**
     * returns a relative url for the group
     *
     * @access public
     * @return string
     */
    public function getUrl($route)
    {
        if (empty($route) || !($geo = $this->getGeo()))
        {
            return '';
        }
        $loc_strings = array();
        if ($geo->isCountry())
        {
            $loc_strings[] = $geo->fk_countrycode;
        }
        else
        {
            $loc_strings[] = $geo->name;
            foreach ($geo->getAncestorLine() as $ancestor)
            {
                if ($ancestor->isCountry())
                {
                    break;
                }
                $loc_strings[] = $ancestor->name;
            }
            $loc_strings[] = $ancestor->fk_countrycode;
        }
        $router = new RequestRouter;
        return $router->url($route, array('location' => implode('/', array_reverse($loc_strings))));
    }
}
