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
 * geo model
 *
 * @package geo
 * @author Philipp
 */
class GeoModel extends RoxModelBase {
    
   
    public function __construct() {
        parent::__construct();
    }
    
    /**
    * Search for locations in the geonames database using the SPAF-Webservice
    *
    * @param search The location to search for
    * @return The matching locations
    */
    public function suggestLocation($search, $max = false,$fcode = '')
    {
        if (strlen($search) <= 1) { // Ignore too small queries
            return '';
        }
        $google_conf = PVars::getObj('config_google');
        if (!$google_conf || !$google_conf->geonames_webservice || !$google_conf->maps_api_key) {
            throw new PException('Google config error!');
        }
        require_once SCRIPT_BASE.'lib/misc/SPAF_Maps.class.php';
        $spaf = new SPAF_Maps($search);
        
        $spaf->setConfig('geonames_url', $google_conf->geonames_webservice_custom);
        $spaf->setConfig('google_api_key', $google_conf->maps_api_key);
		$spaf->setConfig('style','FULL');
		$spaf->setConfig('lang',$_SESSION['lang']);
		$spaf->setConfig('fcode',$fcode);		
        
        // If the request wants more than 10 members
        if ($max) $spaf->setMaxResults($max);
        
        $results = $spaf->getResults();
        foreach ($results as &$res) {
            $res['zoom'] = $spaf->calcZoom($res);
        }
		
		//just for testing addGeonameId, to be removed
		//$this->addGeonameId($results[0]['geonameId'],'member_primary');		
        return $results;
    }
    
	
	/**
	* Get list of Poppulated places matching $search
	**/
	
	public function getGeonamesHierarchy($search,$style,$lang = 'en')
	{
        if (strlen($search) <= 1) { // Ignore too small queries
            return '';
        }
        $google_conf = PVars::getObj('config_google');
        if (!$google_conf || !$google_conf->geonames_webservice) {
            throw new PException('Google config error!');
        }
        require_once SCRIPT_BASE.'lib/misc/SPAF_Maps.class.php';
        $spaf = new SPAF_Maps($search);
        
        $spaf->setConfig('geonames_url', $google_conf->geonames_webservice_custom);
		$spaf->setConfig('style',$style);
		$spaf->setConfig('service','hierarchy?geonameId=');
		$spaf->setConfig('lang',$lang);

		
        $results = $spaf->getResults();

        return $results;
    }
	
	
	/**
	* Add information for a specific geonameId to our database.
	* - add itself and all its parents to geonames_cache
	* - add all translations to geonames_altnames
	* - add hierarchy information to geonames_hierarchy
	**/
	
	public function addGeonameId($geonameId,$usagetype)
	{
//	echo "<br>---<br> in addGeonameId<br>";
		//get id for usagetype:
		$usagetypeId = $this->getUsagetypeId($usagetype)->id;
		
		//retrieve all information from geonames
		$data = $this->getGeonamesHierarchy($geonameId,'FULL');
		//var_dump($data);
		//retireve all GeonameIds we already have in geonames_cache and only add new ones.
		$result = $this->bulkLookup(
            "
			SELECT `geonameid`
			FROM `geonames_cache`
			ORDER BY `geonameid` Asc
            "
        );
		
		$storedGeonameIds = array();
		foreach($result as $key => $value) {
			array_push($storedGeonameIds,$value->geonameid);
		}
	//	var_dump($storedGeonameIds);
		foreach ($data as $level => $dataset) { 
		var_dump($dataset);	
			//initialize empty values:
			if (!isset($dataset['lat'])) $dataset['lat'] = '';
			if (!isset($dataset['lng'])) $dataset['lng'] = '';	
			if (!isset($dataset['name'])) $dataset['name'] = '';			
			if (!isset($dataset['population'])) $dataset['population'] = '';
			if (!isset($dataset['fcl'])) $dataset['fcl'] = '';			
			if (!isset($dataset['fcode'])) $dataset['fcode'] = '';
			if (!isset($dataset['countryCode'])) $dataset['countryCode'] = '';
			if (!isset($dataset['adminCode1'])) $dataset['adminCode1'] = '';
			if (!isset($dataset['timezone'])) $dataset['timezone'] = '';
						
				
			if (!in_array($dataset['geonameId'],$storedGeonameIds)) {
			
				
				//write to geonames_cache
				$insert = $this->dao->query(
				"	
				INSERT INTO geonames_cache
					SET
					geonameid = '".$this->dao->escape($dataset['geonameId'])."',
					latitude = '".$this->dao->escape($dataset['lat'])."',
					longitude= '".$this->dao->escape($dataset['lng'])."',
					name = '".$this->dao->escape($dataset['name'])."',
					population = '".$this->dao->escape($dataset['population'])."',
					fclass = '".$this->dao->escape($dataset['fcl'])."',
					fcode = '".$this->dao->escape($dataset['fcode'])."',
					fk_countrycode = '".$this->dao->escape($dataset['countryCode'])."',
					fk_admincode = '".$this->dao->escape($dataset['adminCode1'])."',
					timezone = '".$this->dao->escape($dataset['timezone'])."'
					"
				);
				if(!$insert) $return = false;
			
			
				//write new data to hirarchy table
				if (isset($parentId)) {
					$hierarchy = $this->addHierarchy($dataset['geonameId'],$parentId);
					if(!$hierarchy) $retun = false;
							echo "- addHierarchy end - ";	
				}
			
			}
			// update the usage table
			$update = $this->updateUsageCounter($dataset['geonameId'],$usagetypeId,'add');
			
			//set the parentId for next level
			$parentId = $dataset['geonameId'];

			
		}
		echo "<br>--- end add --<br>";
		var_dump($return);
		var_dump($update);
		var_dump($parentId);		
			if((isset($return) && !$return) || !$update || !$parentId) 
				return false;
			else return true;
	}
	
	public function addHierarchy($geonameId,$parentId) {
	echo "<br><br>- addHierarchy begin - ";
		$inuse = $this->singleLookup(
			"
			SELECT `id`			
			FROM `geo_hierarchy`
			WHERE `geoId` = '".$geonameId."'
			AND `parentId` = '".$parentId."'
		");
		
		if (!$inuse) {
			return $this->dao->query(
				"
				INSERT INTO `geo_hierarchy`
				SET 
					`geoId` = '".$this->dao->escape($geonameId)."',
					`parentId` = '".$this->dao->escape($parentId)."'
			");
		}
		echo "- addHierarchy end - ";	
	}
	
	// update information about usage of the location
	// $type can be 'add' or 'remove'
			
	public function updateUsageCounter($geonameId,$usagetypeId,$type){	
		echo "- addUsageCounter begin - ";		
		$inuse = $this->singleLookup(
			"
			SELECT `id`			
			FROM `geo_usage`
			WHERE `typeId` = '".$usagetypeId."'
			AND `geoId` = '".$this->dao->escape($geonameId)."'
			");
		
		if ($inuse && $type == 'add') {
			return $this->dao->query(
				"	
				UPDATE geo_usage
					SET `count` = `count` + 1
					WHERE `id` = '".$inuse->id."'  
				");				
		} elseif (!$inuse && $type == 'add') {
			return $this->dao->query(
				"	
				INSERT INTO geo_usage
					SET
					id = 'NULL',
					geoId = ".$this->dao->escape($geonameId).",
					typeId = '".$usagetypeId."',
					count = '1'
				");
		} elseif ($inuse && type == 'remove') {
			return $this->dao->query(
				"	
				UPDATE geo_usage
					SET `count` = `count` - 1
					WHERE `id` = '".$inuse->id."'  
				");	
		} else {
			return false;
		}
					
	
	}	
		

	
	public function getUsagetypeId($usagetype)
	{
		return $this->singleLookup(
			"
			SELECT `id`		
			FROM `geo_type`
			WHERE `name` = '".$usagetype."'
			");		
	}
    
	

	
	
	
	/** 
	* stuff to merge existing Geodata from addreesses to the geonames
	* 
	**/
	public function addressesToGeonames() {
		$Ids = $this->getIdFromAddresses();
		foreach($Ids as $Id) {
			$add = $this->addGeonameId($Id->IdCity,'member_secondary');
		}
	}
	
	private function getIdFromAddresses() {
		return $this->bulkLookup (
			"
				SELECT `IdCity`
				FROM `addresses`
			");
	}

	
}
 
?>
