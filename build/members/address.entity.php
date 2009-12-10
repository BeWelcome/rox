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
     * represents a single address
     *
     * @author     Fake51
     * @package    Apps
     * @subpackage Entities
     */
class Address extends RoxEntityBase
{

    protected $_table_name = 'addresses';

    private $_city;
    private $_country;
    private $_region;

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id)
        {
            $this->findById(intval($id));
        }
    }

    /**
     * returns geo entity for city
     *
     * @access public
     * @return Geo
     */
    public function getCity()
    {
        if (!$this->_city)
        {
            if (!$this->isLoaded())
            {
                return false;
            }
            $this->_city = $this->createEntity('Geo')->findById($this->IdCity);
        }
        return $this->_city;
    }

    /**
     * returns geo entity for associated country
     *
     * @access public
     * @return Geo
     */
    public function getCountry()
    {
        if (!$this->_country)
        {
            if (!($geo = $this->getCity()))
            {
                return false;
            }
            $this->_country = $geo->getCountry();
        }
        return $this->_country;
    }

    /**
     * returns geo entity for associated region
     *
     * @access public
     * @return Geo
     */
    public function getRegion()
    {
        if (!$this->_region)
        {
            if (!($geo = $this->getCity()))
            {
                return false;
            }
            $this->_region = $geo->getRegion();
        }
        return $this->_region;
    }

    /**
     * returns an address entity for the given member
     *
     * @param Member $member - Member entity
     * @param int    $rank   - address rank
     *
     * @access public
     * @return Address
     */
    public function getMemberAddress(Member $member, $rank = 0)
    {
        if ($this->isLoaded() || !$member->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Address')->findByWhere("IdMember = {$member->id} AND Rank = '{$this->dao->escape($rank)}'");
    }
}
