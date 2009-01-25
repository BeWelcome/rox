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
		$query = sprintf("SELECT `name`, `continent`
			FROM `geonames_countries`
			WHERE `iso_alpha2` = '%s'",
			$this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
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
		$query = sprintf("SELECT cities.name AS city, cities.id AS cityId FROM cities WHERE cities.name = '%s'",
			$this->dao->escape($citycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		return $result->fetch(PDB::FETCH_OBJ);
	}	

    private function getMembersAll($query) {
        // this condition makes sure that unlogged people won't see non-public profiles
        if (!(APP_User::isBWLoggedIn())) {
            $query = str_ireplace("FROM ","FROM memberspublicprofiles,",$query);
            $query = str_ireplace("WHERE ","WHERE members.id=memberspublicprofiles.IdMember AND ",$query);
        }

        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		return $result;
	}

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
        $query = sprintf("SELECT members.ProfileSummary,members.BirthDate,membersgroups.Comment as VolComment,
				 	members.HideBirthDate,members.Accomodation,username,cities.name AS city FROM members,cities,countries,groups, groups_locations,membersgroups
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

	public function getAllRegions($countrycode) {
		$query = sprintf("SELECT regions.name AS region, COUNT(members.id) AS number
			FROM regions,cities,members WHERE members.Status='Active' AND cities.IdCountry=regions.IdCountry AND members.IdCity=cities.id AND regions.country_code='%s' and regions.feature_code='ADM1'
			GROUP BY region
            ORDER BY regions.name", $this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve region list.');
		}
		$number = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$number[$row->region] = $row->number;
		}
		$query = sprintf("SELECT regions.name AS region FROM regions WHERE regions.country_code='%s' and regions.feature_code='ADM1' ORDER BY
regions.name", $this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve region list.');
		}
		$regions = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$regions[$row->region]['name'] = $row->region;
			if (isset($number[$row->region]) && $number[$row->region]) {
				$regions[$row->region]['number'] = $number[$row->region];
			} else {
				$regions[$row->region]['number'] = 0;
			}
		}
		
        return $regions;
	}    
    
	public function getAllCities($idregion) {
		$query = sprintf("SELECT cities.Name AS city, count(members.id) AS NbMember FROM cities,members 
			   where cities.id = members.idCity AND members.Status = 'Active'  and (NbMember>0) and IdRegion=%d GROUP BY  cities.id ORDER BY cities.Name",$idregion);
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve city list.');
		}
		$cities = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$cities[] = $row->city;
		}
		
        return $cities;
	} 
	
}
?>
