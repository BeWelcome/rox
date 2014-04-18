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
 * @author Philipp
 */
class MOD_geonames
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


    private function updateGeonames($date) {
        $result = true;
        $changes = $this->fetchFile('http://download.geonames.org/export/dump/modifications-'.$date.'.txt');
        foreach($changes as $change) {
            if (is_numeric($change[0]) && ($change[6] == 'A' || $change[6] == 'P')) {
                // (0 geonameid, 1 name, 2 @skip, 3 @skip, 4 latitude, 5 longitude, 6 fclass, 7 fcode, 8 country, 9 @skip, 10 admin1,
                // 11 @skip, 12 @skip, 13 @skip, 14 population, 15 @skip, 16 @skip, 17 @skip, 18 moddate);
                $res = $this->dao->query("
				    REPLACE INTO
				        `geonames`
				    SET
				        geonameid = '".$this->dao->escape($change[0])."',
				        name = '".$this->dao->escape($change[1])."',
				        latitude = '".$this->dao->escape($change[4])."',
				        longitude = '".$this->dao->escape($change[5])."',
				        fclass = '".$this->dao->escape($change[6])."',
				        fcode = '".$this->dao->escape($change[7])."',
				        country = '".$this->dao->escape($change[8])."',
				        admin1 = '".$this->dao->escape($change[10])."',
				        population = '".$this->dao->escape($change[14])."',
				        moddate = '".$this->dao->escape($change[18])."'
				        ");
                if (!$res) {
                    $result = false;
                }
                if ($change[6] == 'A') {
                    // update geonamesadminunits accordingly
                    $res = $this->dao->query("
    				    REPLACE INTO
    				        `geonamesadminunits`
    				    SET
    				        geonameid = '".$this->dao->escape($change[0])."',
    				        name = '".$this->dao->escape($change[1])."',
    				        fclass = '".$this->dao->escape($change[6])."',
    				        fcode = '".$this->dao->escape($change[7])."',
    				        country = '".$this->dao->escape($change[8])."',
    				        admin1 = '".$this->dao->escape($change[10])."',
    				        moddate = '".$this->dao->escape($change[18])."'
    				        ");
                    if (!$res) {
                        $result = false;
                    }
                }
            }
        }

        $deletes = $this->fetchFile('http://download.geonames.org/export/dump/deletes-'.$date.'.txt');
        foreach($deletes as $delete) {
            if (is_numeric($delete[0])) {
                $res = $this->dao->query("
    				DELETE FROM
    				    `geonames`
    				WHERE
    				    geonameid = '" . $this->dao->escape($delete[0]) . "'");
                if (!$res) {
                    $result = false;
                }
            }
        }
        return $result;
    }

	private function updateAltnames($date) {
	    $result = true;
		$changes = $this->fetchFile('http://download.geonames.org/export/dump/alternateNamesModifications-'.$date.'.txt');
		foreach($changes as $change) {
			if (is_numeric($change[0])) {
                $query= "
				    REPLACE INTO
				        `geonamesalternatenames`
				    SET
				        alternateNameId = '".$this->dao->escape($change[0])."',
				        geonameid = '".$this->dao->escape($change[1])."',
				        isolanguage = '".$this->dao->escape($change[2])."',
				        alternateName = '".$this->dao->escape($change[3])."',
				        ispreferred = '".$this->dao->escape($change[4])."',
				        isshort = '".$this->dao->escape($change[5])."'
				        isColloquial = '".$this->dao->escape($change[6])."'
				        isHistoric = '".$this->dao->escape($change[7])."'";
				$res = $this->dao->query($query);
				if (!$res) {
				    $result = false;
				}
			}
		}

		$deletes = $this->fetchFile('http://download.geonames.org/export/dump/alternateNamesDeletes-'.$date.'.txt');
		foreach($deletes as $delete) {
			if (is_numeric($delete[0])) {
				$res = $this->dao->query("
    				DELETE FROM
    				    `geonamesalternatenames`
    				WHERE
    				    alternatenameid = '" . $this->dao->escape($delete[0]) . "'
    				    AND geonameid = '" . $this->dao->escape($delete[1]) . "'");
				if (!$res) {
				    $result = false;
				}
			}
		}
		return $result;
	}

    /**
	* get updates from geonames
	**/
	public function getUpdate() {
		$result = $this->updateGeonames(date('Y-m-d', time() - 86400)); // Yesterday
		$result |= $this->updateGeonames(date('Y-m-d', time() - 192800)); // the day before yesterday
		if (date('d', time()) == '01') {
		    // \todo: Update country list on the first day of a month
		}
		return $result;
	}

	public function getAltnamesUpdate() {
		$result = $this->updateAltnames(date('Y-m-d', time() - 86400)); // Yesterday
		$result |= $this->updateAltnames(date('Y-m-d', time() - 192800)); // the day before yesterday
		return $result;
	}

    function fetchFile($url) {
        $content = array();
        $handle = @fopen( $url, "r");
        if (!$handle) {
            return $content;
        }
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
            $content[] = $data;
        }
        return $content;
    }
}
?>