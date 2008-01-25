<?php
/**
* Country model
* 
* @package country
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class Country extends PAppModel {
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
		$members = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$members[] = $row;
		}
		return $members;
	}

	public function getMembersOfCountry($countrycode) {
        $query = sprintf("SELECT username,cities.name AS city FROM members,cities,countries 
                 WHERE `Status`='Active' AND members.IdCity=cities.id AND cities.IdCountry=countries.id 
                 AND countries.isoalpha2='%s' LIMIT 20",$this->dao->escape($countrycode));
		echo $query;
        return $this->getMembersAll($query);
        }
    
	public function getMembersOfRegion($regioncode) {
        $query = sprintf("SELECT username, cities.name AS city FROM members, cities,regions 
                 WHERE `Status`='Active' AND members.IdCity=cities.id AND cities.idregion=regions.id AND
                 regions.name='%s' LIMIT 20",$this->dao->escape($regioncode));
		return $this->getMembersAll($query);
        }	

	public function getMembersOfCity($citycode) {
        $query = sprintf("SELECT username,cities.name AS city FROM members,cities 
                    WHERE `Status`='Active' AND members.IdCity=cities.id AND cities.name='%s' LIMIT 20", 
                    $this->dao->escape($citycode));
		return $this->getMembersAll($query);
	}	
    
	/**
	* Returns a 3D array of all countries
	* Format:
	*	[Continent]
	*		[Country-Code]
	*			[Name] Name of the Country
	*			[Number] Number of members living in this country
	*/
	public function getAllCountries() {
		$query = "SELECT countries.isoalpha2 as code, countries.name,
            countries.continent, COUNT(members.id) AS number
			FROM countries INNER JOIN cities LEFT JOIN members on cities.id = members.idCity 
			AND members.Status='Active' and  cities.IdCountry=countries.Id  
			GROUP BY countries.isoalpha2
            ORDER BY continent asc, countries.name ";
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve Country list.');
		}
		
        $countries = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$countries[$row->continent][$row->code]['name'] = $row->name;
			$countries[$row->continent][$row->code]['number'] = $row->number;
		}
		
        return $countries;
	}

	public function getAllRegions($countrycode) {
		$query = sprintf("SELECT name AS region FROM regions WHERE regions.country_code='%s' ORDER BY
regions.name", $this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve region list.');
		}
		$regions = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$regions[] = $row->region;
		}
		
        return $regions;
	}    
    
	public function getAllCities($idregion) {
		$query = sprintf("SELECT cities.Name AS city, count(members.id) AS NbMember FROM cities
            LEFT JOIN  members ON cities.id = members.idCity AND members.Status = 'Active' 
            WHERE idRegion=%d GROUP BY  cities.id ORDER BY cities.Name",$idregion);
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
