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
    
 // small helpers to retrieve some data:
 
 /** getDataById
 ** will return the name (if available in the requested language i, else in english), the parent region (adm1) and country
**/
 
     public function getDataById($geonameId,$lang = false)
     {
        $resultset =  $this->singleLookup( 
            "
            SELECT * 
            FROM `geonames_cache`
            WHERE `geonameid` = '".$geonameId."' 
            ");
        
        if ($lang) {
            $alternateName = $this->singleLookup(
                "
                SELECT *
                FROM `geonames_alternate_names`
                WHERE `geonameId` = '".$geonameId."'
                AND `isoLanguage` = '".$lang."'
                ORDER BY `isPreferredName`
                ");
        }
        // var_dump ($alternateName);
        // var_dump ($resultset);
        if (isset($alternateName) && $alternateName) $resultset['alternateName'] = $alternateName;
        return $resultset;
    }
    

    /**
    * searches for geo entities, based on either id or name
    *
    * @param int|string $name - strange mixed param ... there are methods for loading locations by id, use them!
	* @access public
    * @return array
    */
    public function loadLocation($name)
    {
		$result = array();
		if (is_numeric($name))
        {
            if ($geo = $this->createEntity('Geo')->findById($name))
            {
                $result[] = $geo;
            }
		}
		else
        {
			$result = $this->createEntity('Geo')->findByNameWildcard($name);
		}
        return $result;
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
        
        $spaf->setConfig('geonames_url', $google_conf->geonames_webservice_fallback); // FALLBACK DISABLED - to active again, use $google_conf->geonames_webservice_custom
        $spaf->setConfig('google_api_key', $google_conf->maps_api_key);
        $spaf->setConfig('style','FULL');
        $spaf->setConfig('lang',$_SESSION['lang']);
        $spaf->setConfig('fcode',$fcode);
        
        // If the request wants more than 10 members
        if ($max) $spaf->setMaxResults($max);
        
        //Try to get results - FIRST TIME
        $count = 0;
        $results = @$spaf->getResults();
        
        while (!$results && ++$count <= 3) { //Try to get results - ANOTHER TIME
            if ($count == 1) { // still didn't work, so use the commercial geonames webservice
                $spaf->setConfig('geonames_url', $google_conf->geonames_webservice_fallback);
            }
            $spaf->results = false;
            $results = @$spaf->getResults();
            if ($count == 3 && !$results) { // giving up
                MOD_log::get()->write("Connection to geonames webservice failed! (free & commercial)", "Geo");
            }
        }
            
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
    
    public function getGeonamesHierarchy($geonameId,$style,$lang = 'en')
    {
        if (!$geonameId) {
            return false;
        }
        $geoname = new GeoNamesData($geonameId);
        $geonames_hierarchy = array();
        while ($parent = $geoname->getParent())
        {
            $geonames_hierarchy[] = $geoname;
            // do stuff with parent
            $geoname = $parent;
        }
        $result = array_reverse($geonames_hierarchy);
        // FOR TESTING:
        // var_dump($result);
        // exit();
        return $result;
    }
    
    /**
    * inserts/updates a single row into the geonames_alternate_names table
    *
    * @param int $geonameId
    * @param string $lang
    * @param string $name
    * @param bool $pref
    * @param bool $short
    *
    * @access public
    * @return void
    **/
    public function addAlternateName($geonameId, $lang, $name, $pref = false, $short = 0) 
    {
        if ($lang && $name && $geonameId) 
        {
            $pref = ($lang == 'en') ? 1 : 0;
            $found = $this->singleLookup(
                "
SELECT `geonameid`
FROM `geonames_alternate_names`
WHERE `geonameid` = '".$this->dao->escape($geonameId)."'
AND `isolanguage` = '".$this->dao->escape($lang)."'
            ");
            if (!$found) {
				$result = $this->dao->query(
				"
INSERT INTO 
    `geonames_alternate_names` 
SET
    geonameid = '".$this->dao->escape($geonameId)."',
    isolanguage = '".$this->dao->escape($lang)."',
    alternateName = '".$this->dao->escape($name)."',
    isPreferredName = '".$this->dao->escape($pref)."',
    isShortName = '".$this->dao->escape($short)."'
				"
				);
				return true;
		    } else {
		        $result = $this->dao->query(
				"
UPDATE 
    `geonames_alternate_names`
SET
    alternateName = '".$this->dao->escape($name)."',
    isPreferredName = '".$this->dao->escape($pref)."'
WHERE 
    geonameid = '".$this->dao->escape($geonameId)."'
AND 
    isolanguage = '".$this->dao->escape($lang)."'
				"
				);
				return false;
		    }
		}
    }
    
    /**
    * Add information for a specific geonameId to our database.
    * - add itself and all its parents to geonames_cache
    * - add all translations to geonames_altnames
    * - add hierarchy information to geonames_hierarchy
    **/
    public function addGeonameId($geonameId,$usagetype)
    {
        $parentAdm1Id = 0;
        $parentCountryId = 0;
        $ii = 0;
        
        //get id for usagetype:
        $usagetypeId = $this->getUsagetypeId($usagetype)->id;
        //retrieve all information from geonames
        
        $data = $this->getGeonamesHierarchy($geonameId,'FULL');
        
        //retireve all GeonameIds we already have in geonames_cache and only add new ones.
        $result = $this->getAllGeonameIds();
        $storedGeonameIds = array();
        foreach($result as $key => $value) {
            array_push($storedGeonameIds,$value->geonameid);
        }
        
        // go through the reverse array of geoname data objects
        foreach ($data as $level => $dataset) { 
            if (!in_array($dataset->geonameId,$storedGeonameIds)) {

                // add all alternate names
                foreach($dataset->alternate_names as $altname) {
                    $this->addAlternateName($geonameId,$altname[0],$altname[1]);
                }
                
                //write to geonames_cache
                $insert = $this->dao->query(
                "    
INSERT INTO 
    geonames_cache
SET
    geonameid = '".$this->dao->escape($dataset->geonameId)."',
    latitude = '".$this->dao->escape($dataset->lat)."',
    longitude= '".$this->dao->escape($dataset->lng)."',
    name = '".$this->dao->escape($dataset->name)."',
    population = '".$this->dao->escape($dataset->population)."',
    fclass = '".$this->dao->escape($dataset->fcl)."',
    fcode = '".$this->dao->escape($dataset->fcode)."',
    fk_countrycode = '".$this->dao->escape($dataset->countryCode)."',
    fk_admincode = '".$this->dao->escape($dataset->adminCode1)."',
    timezone = '".$this->dao->escape($dataset->timezone['name'])."',
    parentAdm1Id = '".$this->dao->escape($parentAdm1Id)."',
    parentCountryId = '".$this->dao->escape($parentCountryId)."'
                    "
                );
                if(!$insert) $return = false;
            
                //write new data to hierarchy table
                if (isset($parentId)) {
                    $hierarchy = $this->addHierarchy($dataset->geonameId,$parentId);
                    if(!$hierarchy) $retun = false;
                }
            
            }

            // update the usage table
            $update = $this->updateUsageCounter($dataset->geonameId,$usagetypeId,'add');

            //set the parentIds for next level
            if ($dataset->fcode == 'ADM1') {
                $parentAdm1Id = $dataset->geonameId;
            } elseif ($dataset->fcode == 'PCLI') {
                $parentCountryId = $dataset->geonameId;
            }
            $parentId = $dataset->geonameId;
        }
        
        if((isset($return) && !$return) || !$update || !$parentId) {
            return false;
        } else return true;
    }
    
    public function addHierarchy($geonameId,$parentId) {
//    echo "<br><br>- addHierarchy begin - ";
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
                    `parentId` = '".$this->dao->escape($parentId)."',
                    `comment` = 'geonames'
            ");
        }
//        echo "- addHierarchy end - ";    
    }
    
    /**
    * Updateinformation about usage of a location.
    *
    * @param string $type   can be 'add' or 'remove'
    * @param string $term
    *
    * @access public
    * @return query result | false
    **/
    public function updateUsageCounter($geonameId,$usagetypeId,$type){    
//        echo "- addUsageCounter begin - ";        
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
    * Updateinformation for a specific geonameId to our database.
    * - add itself and all its parents to geonames_cache
    * - add all translations to geonames_altnames
    * - add hierarchy information to geonames_hierarchy
    **/
    
    public function updateGeonameId($geonameId) 
    {
        $parentAdm1Id = 0;
        $parentCountryId = 0;

        //retrieve all information from geonames
        $data = $this->getGeonamesHierarchy($geonameId,'FULL');
        
        //retireve all GeonameIds we already have in geonames_cache and only add new ones.
        $result = $this->getAllGeonameIds();
        $storedGeonameIds = array();
        foreach($result as $key => $value) {
            array_push($storedGeonameIds,$value->geonameid);
        }

        //retireve info currently sotred in DB for specific  GeonameIds
        $result = $this->singleLookup(
            "
            SELECT `geonames_cache`.*,`geo_hierarchy`.`parentid`
            FROM `geonames_cache`,`geo_hierarchy`
            WHERE geonames_cache.geonameid = '".$geonameId."' AND geo_hierarchy.geoid = '".$geonameId."' AND `geo_hierarchy`.`comment` = 'geonames'
            "
            );
        if (!$result) {
            throw new PException('GeoGeonameIdLookupFailed');
            return false;
        }
        
        //write to geonames_cache_backup table:
        $insert = $this->dao->query(
"    
INSERT INTO geonames_cache_backup
    SET
    id = NULL,
    geonameid = '".$this->dao->escape($result->geonameid)."',
    latitude = '".$this->dao->escape($result->latitude)."',
    longitude = '".$this->dao->escape($result->longitude)."',
    name = '".$this->dao->escape($result->name)."',
    population = '".$this->dao->escape($result->population)."',
    fclass = '".$this->dao->escape($result->fclass)."',
    fcode = '".$this->dao->escape($result->fcode)."',
    fk_countrycode = '".$this->dao->escape($result->fk_countrycode)."',
    fk_admincode = '".$this->dao->escape($result->fk_admincode)."',
    timezone = '".$this->dao->escape($result->timezone)."',
    parentid = '".$this->dao->escape($result->parentid)."',
    date_updated = NOW()
    "
);
        if(!$insert) {
            throw new PException('FailedToStoreGeonameDataForBackup');
            $return = false;
        }

        //update:

        // go through the reverse array of geoname data objects
        foreach ($data as $level => $dataset) { 
            if (!in_array($dataset->geonameId,$storedGeonameIds) OR $dataset->geonameId == $geonameId) {

                // add all alternate names
                foreach($dataset->alternate_names as $altname) {
                    $this->addAlternateName($geonameId,$altname[0],$altname[1]);
                }

                //write to geonames_cache
                $insert = $this->dao->query(
                "    
REPLACE INTO 
    geonames_cache
SET
    geonameid = '".$this->dao->escape($dataset->geonameId)."',
    latitude = '".$this->dao->escape($dataset->lat)."',
    longitude= '".$this->dao->escape($dataset->lng)."',
    name = '".$this->dao->escape($dataset->name)."',
    population = '".$this->dao->escape($dataset->population)."',
    fclass = '".$this->dao->escape($dataset->fcl)."',
    fcode = '".$this->dao->escape($dataset->fcode)."',
    fk_countrycode = '".$this->dao->escape($dataset->countryCode)."',
    fk_admincode = '".$this->dao->escape($dataset->adminCode1)."',
    timezone = '".$this->dao->escape($dataset->timezone['name'])."',
    parentAdm1Id = '".$this->dao->escape($parentAdm1Id)."',
    parentCountryId = '".$this->dao->escape($parentCountryId)."'
                "
                );

                if(!$insert) {
                    throw new PException('FailedToUpdateGeonamesInfoInDb');
                    $return = false;
                }
            
                //delete old hierarchy row
                $delet = $this->dao->query(
                    "
                    DELETE 
                    FROM `geo_hierarchy`
                    WHERE geo_hierarchy.geoid = '".$geonameId."' AND `geo_hierarchy`.`comment` = 'geonames'
                    "
                    );                
                    
                //write new data to hierarchy table
                if (isset($parentId)) {
                    $hierarchy = $this->addHierarchy($dataset->geonameId,$parentId);
                    if(!$hierarchy) $retun = false;
                }
            
            }

            // hack to make sure we're dealing with useful data - should be fixed later
            $dataset['fcode'] = ((!empty($dataset['fcode'])) ? $dataset['fcode'] : '');
            //set the parentId for next level
            if ($dataset['fcode'] == 'ADM1') {
                $parentAdm1Id = $dataset['geonameId'];
            }
            if ($dataset['fcode'] == 'PCLI') {
                $parentCountryId = $dataset['geonameId'];
            }
            $parentId = $dataset['geonameId'];
         
        }

        if((isset($return) && !$return) || !$update || !$parentId) {
            return false;
        } else return true;
    }
    
    
    /** update the counters in geo_usage
    * how often is a geoid referenced by a specific usagetype
    **/
    public function updateGeoCounters() {
    
        // geht the usage type ids
        $blogTypeId = $this->getUsagetypeId('trip')->id;
        $addressTypeId = $this->getUsagetypeId('member_primary')->id;
    
        //readd ids from blog table
        $blogIds = $this->getIdFromBlog();    
        
        //readd ids from address table
        $addressIds = $this->getIdFromAddresses();
        
        //read hierarchy
        $hierarchy = $this->bulkLookup (
            "
                SELECT `geoId`, `parentId`
                FROM `geo_hierarchy`
            ");
        
        // read curren counters
        $usage = $this->bulkLookup (
            "
                SELECT * 
                FROM `geo_usage`
            ");
        
        //calculate numbers for used Id (lowest hierarchy)
        foreach ($blogIds as $Id) {
            if (!isset($blogCount[$Id->blog_geonameid])) $blogCount[$Id->blog_geonameid] = 0;
        }
            $blogCount[$Id->blog_geonameid]++;
        foreach ($addressIds as $Id) {
            if (!isset($addressCount[$Id->IdCity])) $addressCount[$Id->IdCity] = 0;        
            $addressCount[$Id->IdCity]++;
        }
        
        //caluculate numbers for higher hirarchy levels

        
        $harray=array() ;
        foreach ($hierarchy as $value) {
            $harray[$value->geoId] = $value->parentId;
        }
    
        $hblogCount = array();
        $worldid = 6295630; //globe, top level
        $hblogCount[$worldid] = $this->countHierarchy($harray, $blogCount, $hblogCount, $worldid);    
        
        $haddressCount = array();
        $haddressCount[$worldid] = $this->countHierarchy($harray, $addressCount, $haddressCount, $worldid);    
        
        //flusha usage table
        $return = $this->dao->query(
            "TRUNCATE TABLE `geo_usage`"
        );
        
        //write to db
        foreach ($hblogCount as $key=>$value) {
            if ($value !=0) {
                $return = $this->dao->query(
                    "    
                    INSERT INTO geo_usage
                    SET
                        id = 'NULL',
                        geoId = ".$key.",
                        typeId = ".$blogTypeId.",
                        count = ".$value."
                    "
                    );
                }
        }
        foreach ($haddressCount as $key=>$value) {
            if ($value !=0) {            
                $return = $this->dao->query(
                    "    
                    INSERT INTO geo_usage
                    SET
                        id = 'NULL',
                        geoId = ".$key.",
                        typeId = ".$addressTypeId.",
                        count = ".$value."
                    "
                    );
            }
        }        
        return $return;
    }
  

    
    private function countHierarchy($harray, $carray, &$hCount,$id){
        $counter = 0;
        $nextids = array_keys($harray,$id);

        if (!empty($nextids)) {
            foreach ($nextids as $value) {
                $result = $this->countHierarchy($harray, $carray, $hCount, $value);
                $hCount[$value] = $result;
                $counter += $result;
            }
        } elseif (isset($carray[$id])) {
            $counter += $carray[$id];
        }
        return $counter;
    }
    
    /** 
    * stuff to merge existing Geodata from addreesses to the geonames
    * 
    **/
    public function RenewGeo() {
        $error = array();
        
        
        //flush table
        $this->dao->query(
            "SET FOREIGN_KEY_CHECKS = 0"
        );
        $flush_geonames_cache = $this->dao->query(
            "TRUNCATE TABLE `geonames_cache`"
        );

        $flush_geonames_cache = $this->dao->query(
            "TRUNCATE TABLE `geonames_cache`"
        );        

        $flush_geo_hierarchy = $this->dao->query(
            "TRUNCATE TABLE `geo_hierarchy`"
        );

        $flush_geo_usage = $this->dao->query(
            "TRUNCATE TABLE `geo_usage`"
        );

        $this->dao->query(
            "SET FOREIGN_KEY_CHECKS = 1"
        );
        //readd ids from address table
        $AddressIds = $this->getIdFromAddresses();
        $counter['members'] = 0;
        
        foreach($AddressIds as $Id) {
            if ($Id->IdCity) {
                $addaddresses = $this->addGeonameId($Id->IdCity,'member_primary');
                $counter['members']++;
            }
        }
        if (!$addaddresses) $error = 'Failed to readd address geoids';
        
        //readd ids from blog table
        $BlogIds = $this->getIdFromBlog();
        $counter['blog'] = 0;
        foreach($BlogIds as $Id) {
            if ($Id->blog_geonameid) {
                $addblogs = $this->addGeonameId($Id->blog_geonameid,'trip');
                $counter['blog']++;
            }
        }
        
        $result['error'] = $error;
        $result['counter'] = $counter;
        
        return $result;
    }
    
    /** 
    * rebuilds geonames_alternate_names table with data from geonames
    * 
    **/
    public function RenewAltNames($addonly = true) 
    {
        $error = array();
        $counter = 0;
        //get all geoname ids
        $geonameIds = $this->getAllGeonameIds();
        $all_geonameids = array();
        foreach($geonameIds as $Id) {
            array_push($all_geonameids, $Id->geonameid);
        }
        $current_names = $this->bulkLookup(
            "
SELECT DISTINCT `geonameid`
FROM `geonames_alternate_names`
WHERE `geonameId` IN ('" . implode("','", $all_geonameids)."') 
            ");
        $geonameids_with_name = array();
        foreach($current_names as $name) {
            array_push($geonameids_with_name, $name->geonameid);
        }
        if (!$addonly) {
            //empty old geonames_alternate_names table
            $flush_alternate_names = $this->dao->query(
                "TRUNCATE TABLE `geonames_alternate_names`"
            );
        }
        foreach($geonameIds as $Id) {
            if (!in_array($Id->geonameid,$geonameids_with_name)) {
                $data = GeoNamesData::get($Id->geonameid);                            
                if (is_array($data->alternate_names)) {
                    foreach ($data->alternate_names as $lang => $name) {
                        if ($this->addAlternateName($Id->geonameid,$lang,$name))
                            $counter++;
                    }
                }
            }
        }

        $result['error'] = $error;
        $result['counter']['alternate_names'] = (isset($counter)) ? $counter : 0;
        
        return $result;
    }
    
    
    private function getIdFromAddresses() {
        return $this->bulkLookup (
            "
                SELECT `IdCity`
                FROM `addresses`
            ");
    }
    
    private function getIdFromBlog() {
        return $this->bulkLookup (
            "
                SELECT `blog_geonameid`
                FROM `blog_data`
            ");
    }
    
    public function getAllGeonameIds() {
    
        $result = $this->bulkLookup(
        "
        SELECT `geonameid`
        FROM `geonames_cache`
        ORDER BY `geonameid` Asc
        "
        );
//        var_dump($result);
//        if (!$result) {
            // throw new PException('GeoGeonameIdLookupFailed');
            // return false;
        // } else #
        return $result;
    }
    
    /**
    * Check if a geonameid exists, if not, add it to the DB.
    **/
    
    public function checkGeonameId($geonameId,$usagetype = false)
    {
        //check wether we have that id in our DB
        $location = $this->getDataById($geonameId);
        if (!$location) {
            //add it to the DB
            return $this->addGeonameId($geonameId,$usagetype);
        } else return true; 

    }    
    
    
    /**
    * Check if a geonameid exists, if not, add it to the DB.
    **/
    
    public function getContinents()
    {
        //get all countries from the geo countries table
        return $this->createEntity('Geo')->findByWhereMany("fcode = 'cont'"); 
    }

    
    
}
 
?>
