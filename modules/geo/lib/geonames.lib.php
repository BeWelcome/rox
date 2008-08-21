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

	
		
	/** 
	* get updates from geonames
	**/
	
	
	public function getUpdate() {
		$geomodel = new GeoModel();

		//retireve all GeonameIds we already have in geonames_cache and only add new ones.
		$allids = $geomodel->getAllGeonameIds();
		$storedGeonameIds = array();
		foreach($allids as $key => $value) {
			array_push($storedGeonameIds,$value->geonameid);
		}
		
		$changes = $this->fetchUrl('http://download.geonames.org/export/dump/modifications-2008-08-20.txt');
		foreach($changes as $change) {
			if (is_numeric($change[0]) && in_array($change[0],$storedGeonameIds)) {
				$geomodel->updateGeonameId($change[0]);
			}
		}
	}
	
	
	
	
	 function fetchUrl ($url) {
    // parse URL
    if (!$elements = @parse_url($url)) {
      return '';
    }
    
    // add default port
    if (!isset($elements['port'])) {
      $elements['port'] = 80;
    }
    
    // open socket
    $fp = fsockopen($elements['host'], $elements['port'], $errno, $errstr, 20);
    if (!$fp) {
      return '';
    }
    
    // assemble path
    $path = $elements['path'];
    if (isset($elements['query'])) {
      $path .= '?'.$elements['query'];
    }
    
    // assemble HTTP request header
    $request  = "GET $path HTTP/1.1\r\n";
    $request .= "Host: ".$elements['host']."\r\n";
    $request .= "Connection: Close\r\n\r\n";
    
    // send HTTP request header and read output
    $result = '';
    fwrite($fp, $request);
    while (!feof($fp)) {
      $result[] = fgetcsv($fp, 0,"\t");
    }
    
    // close socket connection
    fclose($fp);
    
    // strip extra text from result
    //return preg_replace('/^[^<>]*(<.*>)[^<>]*$/s', '$1', $result);
	return $result;
  }

   
  
}
?>