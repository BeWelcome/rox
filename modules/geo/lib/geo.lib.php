<?php
/*

Copyright (c) 2007 BeVolunteer

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
 * Collection of methods related to geographical data.
 * 
 * An example for its use:
 * $geo = MOD_geo::get();	// get the singleton instance
 * $id = $geo->getCityID($cityname);
 * 
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class MOD_geo
{
    /**
     * Singleton instance
     * 
     * @var MOD_geo
     * @access private
     */
    private static $_instance;
    
	private function __construct()
    {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
    }
    
    /**
     * singleton getter
     * 
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    /**
     * @param void
     * @return strings the names of all countries in table countries
     */
    public function getAllCountries()
    {
        $query = 'SELECT SQL_CACHE `id`, `Name` FROM `countries` ORDER BY `Name`';
	    $s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve countries!');
		}
		$countries = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$countries[$row->id] = $row->Name;
		}
		return $countries;
    }

    /**
     * @param string $cityname precise, case sensitive name of city
     * @return int unambiguous identifier of a city in database
     */
    public function getCityID($cityname, $mode = false)
    {
        $query = '
SELECT SQL_CACHE `id`
FROM `cities`
WHERE `Name` = \'' . $cityname . '\'';
        $s = $this->dao->query($query);
		$cityIDs = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$cityIDs[] = $row->id;
		}
		if (count($cityIDs) > 1 && !$mode) {
		    throw new PException(
		         'Number of found cities for ' . $cityname . 
		         ' is ' . count($cityIDs) .
		         '. Can\'t determine unambiguous city id.');
		}
		if (!isset($cityIDs[0])) {
		    return false;
		}
		return $cityIDs[0];
    }
    
    /**
     * @param int $cityID of city in database
     * @return string name of a city in database
     */
    public function getCityName($cityID)
    {
        $query = '
SELECT SQL_CACHE `Name`
FROM `cities`
WHERE `id` = \'' . $cityID . '\'';
        $s = $this->dao->query($query);
		$cityName = array();
		$row = $s->fetch(PDB::FETCH_OBJ);
		$cityName[] = $row->Name;
		return $cityName[0];
    }
}
?>