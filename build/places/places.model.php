<?php
/**
* Places model
* 
* @package places
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class Places extends PAppModel {
	private $_dao;
	
	public function __construct() {
		parent::__construct();
	}

	
	public function getCountryInfo($countrycode) {
		$query = sprintf("SELECT `geonames_countries`.`name`, `geonames_countries`.`continent`,countries.id as IdCountry
			FROM `geonames_countries`,`countries`
			WHERE `geonames_countries`.`iso_alpha2` = '%s' and `geonames_countries`.`iso_alpha2`=`countries`.`isoalpha2`", 
			$this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve info about countries list.');
		}
		return $result->fetch(PDB::FETCH_OBJ);
	}
    
	public function getRegionInfo($regioncode) {
		$query = sprintf("SELECT name AS region, id AS idregion FROM regions WHERE regions.name = '%s'",
			$this->dao->escape($regioncode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve info about Region.');
		}
		return $result->fetch(PDB::FETCH_OBJ);
	}	

	public function getCityInfo($citycode) {
		$query = sprintf("SELECT cities.name AS city, cities.id AS IdCity FROM cities WHERE cities.name = '%s'",
			$this->dao->escape($citycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		return $result->fetch(PDB::FETCH_OBJ);
	}	

    private function getMembersAll($query) {
        // this condition makes sure that unlogged people won't see non-public profiles
        if (!(APP_User::isBWLoggedIn('NeedMore,Pending'))) {
            $query = str_ireplace("FROM ","FROM memberspublicprofiles,",$query);
            $query = str_ireplace("WHERE ","WHERE members.id=memberspublicprofiles.IdMember AND ",$query);
        }

        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		return $result;
	} // end of getMembersAll

	public function getMembersOfCountry($countrycode) {
        $query = sprintf("SELECT members.BirthDate,members.HideBirthDate,members.Accomodation,username,cities.name AS city FROM members,cities,countries 
                 WHERE `Status`='Active' AND members.IdCity=cities.id AND cities.IdCountry=countries.id 
                 AND countries.isoalpha2='%s'",$this->dao->escape($countrycode));
        return $this->getMembersAll($query);
        }
    
/*
* This retrieve the list of volunteers for a place
* volunteers are the one of the Local Vol group
* @IdLocation is the geoname id where the members is volunteering
*/
	public function getVolunteersOfPlace($IdLocation) {
        $query = sprintf("SELECT members.BirthDate,members.HideBirthDate,members.Accomodation,username,cities.name AS city FROM members,cities,countries,groups, groups_locations,membersgroups
                 WHERE `members`.`Status`='Active' AND groups.Name='BewelcomeLV' AND groups_locations.IdGroupMembership=membersgroups.id 
								 AND membersgroups.IdMember=members.id AND membersgroups.IdGroup=groups.id AND members.IdCity=cities.id AND cities.IdCountry=countries.id 
                 AND groups_locations.IdLocation='%d'",$this->dao->escape($IdLocation));
        return $this->getMembersAll($query);
        } // end of getVolunteersOfPlace
    
	public function getMembersOfRegion($regioncode) {
        $query = sprintf("SELECT members.BirthDate,members.HideBirthDate,members.Accomodation,username, cities.name AS city FROM members, cities,regions 
                 WHERE `Status`='Active' AND members.IdCity=cities.id AND cities.idregion=regions.id AND
                 regions.name='%s' and regions.feature_code='ADM1'",$this->dao->escape($regioncode));
		return $this->getMembersAll($query);
        }	

	public function getMembersOfCity($citycode) {
        $query = sprintf("SELECT members.BirthDate,members.HideBirthDate,members.Accomodation,username,cities.name AS city FROM members,cities 
                    WHERE `Status`='Active' AND members.IdCity=cities.id AND cities.name='%s'", 
                    $this->dao->escape($citycode));
		return $this->getMembersAll($query);
	}	
    
	/**
	* Returns a 3D array of all countries
	* Format:
	*	[Continent]
	*		[Places-Code]
	*			[Name] Name of the Places
	*			[Number] Number of members living in this places
	*/  
    
	public function getAllCountries() {
		$query = "SELECT countries.isoalpha2 as code, countries.name,
            countries.continent, COUNT(members.id) AS number
			FROM countries,cities,members where members.Status='Active' and cities.IdCountry=countries.Id and members.IdCity=cities.id  
			GROUP BY countries.isoalpha2
            ORDER BY continent asc, countries.name ";
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve Places list.');
		}
		$number = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$number[$row->code] = $row->number;
		}
		
		$query = "SELECT `isoalpha2` AS `code`, `name`, `continent`
			FROM `countries`
			ORDER BY `continent` ASC, `name` ASC";
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve Places list.');
		}
        $countries = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$countries[$row->continent][$row->code]['name'] = $row->name;
			if (isset($number[$row->code]) && $number[$row->code]) {
				$countries[$row->continent][$row->code]['number'] = $number[$row->code];
			} else {
				$countries[$row->continent][$row->code]['number'] = 0;
			}
		}
		
        return $countries;
	}

	/**
	retrieve the list of all regions for a given country
	@$countrycode is either the country code of the country or the countries.id
	the number of members in the area is to be kept up to date by a cron or by some SQL for volunteers query
	*/
	public function getAllRegions($countrycode) {
		if (is_numeric($countrycode)) {
			$query = sprintf("SELECT regions.name AS region, NbMembers AS number
			FROM regions WHERE  IdCountry='%d' and regions.feature_code='ADM1'
           ORDER BY regions.name", $this->dao->escape($countrycode));
		}
		else {
			$query = sprintf("SELECT regions.name AS region, NbMembers AS number
			FROM regions WHERE  regions.country_code='%s' and regions.feature_code='ADM1'
           ORDER BY regions.name", $this->dao->escape($countrycode));
		}
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve region list.');
		}
		$regions = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$regions[$row->region]['name'] = $row->region;
			$regions[$row->region]['number'] = $row->number;
		}
		
        return $regions;
	}    // end of getAllRegions
    
	/**
	retrieve the list of all regions for a given country
	@$idregion is either the region Name of the region or the regions.id
	the number of members in the area is to be kept up to date by a cron or by some SQL for volunteers query
	*/
	public function getAllCities($idregion) {
		if (is_numeric($idregion)) {
		$query = sprintf("SELECT cities.Name AS city, cities.NbMembers as NbMember FROM cities
			   where IdRegion=%d  and (cities.NbMembers>0) ORDER BY cities.Name",$idregion);
		}
		else {
		$query = sprintf("SELECT cities.Name AS city,  cities.NbMembers as NbMember FROM cities,regions
			   where regions.id=cities.IdRegion and regions.Name='%s' and ( cities.NbMembers>0) ORDER BY cities.Name",$idregion);
		}
		
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve city list.');
		}
		$cities = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$cities[] = $row;
		}
		
        return $cities;
	}  // end of getAllCities
}
	
?>
