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

    /**
     * Get details for a region
     * @param string $regionName Name of region, i.e. "Flanders"
     * @param string $countryCode Two-letter country code, i.e. "BE"
     * @return object Region with its name and ID
     */
    public function getRegionInfo($regionName, $countryCode) {
        $query = sprintf("
            SELECT
                name AS region,
                geonameId AS idregion
            FROM
                geonames_cache
            WHERE
                fcode = 'ADM1'
                AND
                name = '%s'
                AND
                fk_countrycode = '%s'
            ", $this->dao->escape($regionName),
                $this->dao->escape($countryCode));

        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve info about Region.');
        }
      return $result->fetch(PDB::FETCH_OBJ);
    }	

	public function getCityInfo($cityname,$regionname="",$countrycode="") {
		$query = sprintf("SELECT geonames_cache.name AS city, geonames_cache.geonameid AS IdCity FROM geonames_cache WHERE geonames_cache.name = '%s' and geonames_cache.fk_countrycode='%s'",
			$this->dao->escape($cityname),$this->dao->escape($countrycode));
			
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve the city.');
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

    /**
     * Get all members of a country
     * @param string $countryCode Two-letter country code, i.e. "BE"
     * @return object Region with its name and ID
     */
    public function getMembersOfCountry($countrycode) {
        $query = sprintf("
            SELECT
                members.BirthDate,
                members.HideBirthDate,
                members.Accomodation,
                members.idCity,
                members.username,
                geonames_cache.name AS city
            FROM
                members,
                geonames_cache
            WHERE
                members.Status = 'Active'
                AND
                geonames_cache.geonameId = members.idCity
                AND
                geonames_cache.fk_countrycode = '%s'
            ORDER BY
                city
            ",$this->dao->escape($countrycode));

        return $this->getMembersAll($query);
    }

    public function getMembersOfRegion($regioncode, $countrycode) {
        $query = sprintf("
            SELECT
                members.BirthDate,
                members.HideBirthDate,
                members.Accomodation,
                members.username,
                geonames_cache.name AS city
            FROM
                members,
                geonames_cache,
                geonames_cache as geonames_cache2
            WHERE
                Status = 'Active'
                AND
                geonames_cache.fk_countrycode = '%s'
                AND
                members.idCity = geonames_cache.geonameid
                AND
                geonames_cache.parentAdm1Id = geonames_cache2.geonameid
                AND
                geonames_cache2.name = '%s'
            ORDER BY
                city
            ", $this->dao->escape($countrycode), $this->dao->escape($regioncode));

        return $this->getMembersAll($query);
    }

    public function getMembersOfCity($cityname, $regionname = "", $countrycode = "") {
        $query = sprintf("
            SELECT
                members.BirthDate,
                members.HideBirthDate,
                members.Accomodation,
                username,
                geonames_cache.name AS city
            FROM
                members,
                geonames_cache 
            WHERE
                Status = 'Active'
                AND
                members.IdCity = geonames_cache.geonameid
                AND
                geonames_cache.name = '%s'
                AND
                geonames_cache.fk_countrycode = '%s'
            ", $this->dao->escape($cityname), $this->dao->escape($countrycode));
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
        $query = "
            SELECT
                geonames_countries.iso_alpha2 AS code,
                geonames_countries.name,
                geonames_countries.continent,
                COUNT(members.id) AS number
            FROM
                geonames_countries,
                geonames_cache,
                members
            WHERE
                members.Status = 'Active'
                AND
                geonames_countries.iso_alpha2 = geonames_cache.fk_countrycode
                AND
                members.IdCity = geonames_cache.geonameid
            GROUP BY
                geonames_countries.iso_alpha2
            ORDER BY
                continent ASC,
                geonames_countries.name
        ";
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
     * Retrieve the list of all regions for a given country
     * @param string $countrycode Two-letter country code, i.e. "FR"
     * @return array List of regions with number of members in them
     */
    public function getAllRegions($countrycode) {
        $query = sprintf("
            SELECT
                geonames_cache.name AS region,
                COUNT(members.id) as number
            FROM
                geonames_cache,
                geonames_cache AS geonames_cache2,
                geonames_cache AS geonames_cache3,
                members
            WHERE
                geonames_cache.fk_countrycode = '%s'
                AND
                geonames_cache.fcode = 'ADM1'
                AND
                geonames_cache2.geonameid = members.idCity
                AND
                geonames_cache3.geonameid = geonames_cache2.parentAdm1Id
                AND
                geonames_cache3.geonameid = geonames_cache.geonameid
                AND
                members.status = 'Active'
            GROUP BY
                geonames_cache.name
            ORDER BY
                geonames_cache.name
            ", $this->dao->escape($countrycode));

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
    }

    /**
     * Retrieve list of all cities for a region that have members
     * @param int $regionId Geoname ID of region
     * @return array List of cities with number of members
     */
    public function getAllCities($regionId) {
        $query = sprintf("
            SELECT
                geonames_cache.name AS city,
                COUNT(members.id) AS NbMember
            FROM
                geonames_cache,
                members
            WHERE
                geonames_cache.parentAdm1Id = %d
                AND
                members.status = 'Active'
                AND
                members.idCity = geonames_cache.geonameId
            GROUP BY
                geonames_cache.name
            ORDER BY
                geonames_cache.name
            ", $regionId);

        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve city list.');
        }
        $cities = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $cities[] = $row;
        }
        return $cities;
    }
}

?>
