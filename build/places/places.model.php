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
    const MEMBERS_PER_PAGE = 20;

    private $_dao;
    // used to store the current UI language reduced to the first tag
    // zh-Hans will be zh
    private $lang;

    public function __construct() {
        parent::__construct();
        $langarr = explode('-', $_SESSION['lang']);
        $this->lang = $langarr[0];
    }

    /**
     * Get country details from database (name, Geoname ID, continent)
     * @param string $countrycode Country ISO alpha2 code, i.e. "BE"
     * @return object|bool Database object, false if no match in database
     */
    public function getCountryInfo($countrycode) {
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                geonames_cache.name,
                geonames_cache.geonameId as IdCountry,
                geonames_countries.continent
            FROM
                geonames_cache,
                geonames_countries
            WHERE
                geonames_cache.fcode LIKE 'PCL%%'
                AND
                geonames_countries.iso_alpha2 = '%s'
                AND
                geonames_cache.fk_countrycode = geonames_countries.iso_alpha2
            ", $this->dao->escape($countrycode));
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
    public function getRegionInfo($regioncode, $countryCode) {
        $query = sprintf("
            SELECT
                g.name region,
                g.admin1 admin1,
                g.country country,
                0 ispreferred,
                0 isshort,
                'geo' source
            FROM
                geonames g
            WHERE
                fcode = 'ADM1'
                AND country = '%2\$s'
                AND admin1 = '%1\$s'
            UNION SELECT
                a.alternatename region,
                g.admin1 admin1,
                g.country country,
                0 ispreferred,
                0 isshort,
                'alt' source
            FROM
                geonames g
            WHERE
                fcode = 'ADM1'
                AND country = '%2\$s'
                AND admin1 = '%1\$s'
            ", $this->dao->escape($regioncode),
                $this->dao->escape($countryCode));
        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve info about Region.');
        }
      return $result->fetch(PDB::FETCH_OBJ);
    }

    public function getCityInfo($cityname, $regionname = "", $countrycode = "") {
        $query = sprintf("
            SELECT
                geonames_cache.name AS city,
                geonames_cache.geonameid AS IdCity
            FROM
                geonames_cache
            WHERE
                geonames_cache.name = '%s'
                AND
                geonames_cache.fk_countrycode = '%s'
            ", $this->dao->escape($cityname), $this->dao->escape($countrycode));

        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve the city.');
        }
        return $result->fetch(PDB::FETCH_OBJ);
    }

    private function getMembersAll($query) {
        // this condition makes sure that unlogged people won't see non-public profiles
        if (!(APP_User::isBWLoggedIn('NeedMore,Pending')))
        {
            $query = str_ireplace("FROM","FROM memberspublicprofiles mpp,",$query);
            $query = str_ireplace("WHERE","WHERE m.id = mpp.IdMember AND",$query);
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
    public function getMembersOfCountry($countrycode, $pageNumber) {
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.idCity,
                m.username,
                g.name AS city
            FROM
                members m,
                geonames g
            WHERE
                m.Status = 'Active'
                AND g.geonameId = m.idCity
                AND g.country = '%s'
            ORDER BY
                m.Accomodation ASC, m.LastLogin DESC
            LIMIT " . ($pageNumber-1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE
            ,$this->dao->escape($countrycode));
        $result = $this->getMembersAll($query);
        if (!$result) {
            return false;
        }

        $countQuery = $this->dao->query("SELECT FOUND_ROWS() as cnt");
        $count = $countQuery->fetch(PDB::FETCH_OBJ)->cnt;

        $members = array();
        while($row = $result->fetch(PDB::FETCH_OBJ)) {
            $members[] = $row;
        }
        return array($count, $members);
    }

    public function getMembersOfRegion($regioncode, $countrycode, $pageNumber) {
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.username,
                g.name AS city
            FROM
                members m,
                geonames g
            WHERE
                m.Status = 'Active'
                AND m.idCity = g.geonameid
                AND g.admin1 = '%2\$s'
                AND g.country = '%1\$s'
                AND g.fclass = 'P'
            ORDER BY
                m.Accomodation ASC, m.LastLogin DESC
            LIMIT " . ($pageNumber-1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE,
            $this->dao->escape($countrycode), $this->dao->escape($regioncode));
        $result = $this->getMembersAll($query);
        if (!$result) {
            return false;
        }

        $countQuery = $this->dao->query("SELECT FOUND_ROWS() as cnt");
        $count = $countQuery->fetch(PDB::FETCH_OBJ)->cnt;

        $members = array();
        while($row = $result->fetch(PDB::FETCH_OBJ)) {
            $members[] = $row;
        }
        return array($count, $members);
    }

    public function getMembersOfCity($cityCode, $cityName, $pageNumber) {
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.username,
                '%s' city
            FROM
                members m,
                geonames g
            WHERE
                m.Status = 'Active'
                AND m.IdCity = g.geonameid
                AND g.geonameid = '%s'
            ORDER BY
                m.Accomodation ASC, m.LastLogin DESC
            LIMIT
                " . ($pageNumber -1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE,
            $this->dao->escape($cityName), $this->dao->escape($cityCode));
        $result = $this->getMembersAll($query);
        if (!$result) {
            return false;
        }

        $countQuery = $this->dao->query("SELECT FOUND_ROWS() as cnt");
        $count = $countQuery->fetch(PDB::FETCH_OBJ)->cnt;

        $members = array();
        while($row = $result->fetch(PDB::FETCH_OBJ)) {
            $members[] = $row;
        }
        return array($count, $members);
    }

    private function compareCountryNames($a, $b) {
        return strcmp($a->name, $b->name);
        // $this->collator->compare($a->name, $b->name);
    }

    public function getContinents() {
        $words = new MOD_words();
        $continents = array(
            "AM" => array($words->getSilent('PlacesAmerica'), "PlacesAmericaCont"),
            "EA" => array($words->getSilent('PlacesEurAsia'), "PlacesEurAsiaCont"),
            "AF" => array($words->getSilent('PlacesAfrica'),  "PlacesAfricaCont"),
            "OC" => array($words->getSilent('PlacesOceania'), "PlacesOceaniaCont"),
            "AN" => array($words->getSilent('PlacesAntarctica'), "PlacesAntarcticaCont")
        );
        uasort($continents, function($a, $b) { return strcmp($a[0], $b[0]); });
        return $continents;
    }

    /**
     * Get a list of all countries with number of members for each country
     * @return array List of continents, containing array of countries
     */
    public function getAllCountries() {
        // Get countries that have members and count members
        $query = "
            SELECT
                c.country country,
                COUNT(m.id) number
            FROM
                geonamescountries c,
                geonames g,
                members m
            WHERE
                c.country = g.country
                AND g.fclass = 'P'
                AND m.IdCity = g.geonameid
                AND m.Status = 'Active'
            GROUP BY
                c.country";

        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve country member counts.');
        }
        $number = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $number[$row->country] = $row->number;
        }

        // Get all countries based on current language
        // use mysql only query to get only the first match
        $query = "SELECT * FROM (
            SELECT
                c.country country,
                a.alternatename name,
                c.continent continent,
                a.ispreferred ispreferred,
                a.isshort isshort,
                'alternate' source
            FROM
                geonamescountries c,
                geonamesalternatenames a
            WHERE
                a.geonameid = c.geonameid
                AND a.isolanguage = '" . $this->dao->escape($this->lang) . "'
                AND a.isHistoric = 0
            UNION SELECT
                country,
                name,
                continent,
                0 ispreferred,
                0 isshort,
                'geoname' source
            FROM
                geonamescountries c
            ORDER BY
                continent ASC, country, source ASC, ispreferred DESC, isshort DESC, name ASC) x
            GROUP BY country
            ";
        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve country list.');
        }

        // Pack both database results into country list
        $countries = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            if (!isset($countries[$row->continent][$row->country])) {
                $data = new StdClass;
                $data->name = $row->name;
                $data->country = $row->country;
                $countries[$row->continent][$row->country] = $data;
            }
            if (isset($number[$row->country]) && $number[$row->country]) {
                $countries[$row->continent][$row->country]->number = $number[$row->country];
            } else {
                $countries[$row->continent][$row->country]->number = 0;
            }
        }
        // $this->collator = new Collator('root');
        foreach($countries as &$continent) {
            usort($continent, array($this, 'compareCountryNames'));
        }
        return $countries;
    }

    /**
     * Retrieve the list of all regions for a given country
     * @param string $countrycode Two-letter country code, i.e. "FR"
     * @return array List of regions with number of members in them
     */
    public function getAllRegions($countrycode) {
        // first get region names
        // use mysql only trick to get the first result for each group
        $query = sprintf("SELECT * FROM (
            SELECT
                a.admin1 admin1, a.name region, 0 ispreferred, 0 isshort, 'geo' source
            FROM
                geonames a
            WHERE
                a.country = '%1\$s'
                AND a.fcode = 'ADM1'
            UNION SELECT
                ga.admin1 admin1, a.alternatename region, a.ispreferred ispreferred, a.isshort isshort, 'alt' source
            FROM
                geonames ga,
                geonamesalternatenames a
            WHERE
                ga.country = '%1\$s'
                AND ga.fcode = 'ADM1'
                AND ga.geonameid = a.geonameid
                AND a.isoLanguage = '%2\$s'
            ORDER BY
                admin1, source, ispreferred DESC, isshort DESC, region) x
            GROUP BY
                admin1
            ", $this->dao->escape($countrycode), $this->dao->escape($this->lang));
        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve region list.');
        }

        $regions = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            if (!isset($regions[$row->admin1])) {
                $regions[$row->admin1]['name'] = $row->region;
                $regions[$row->admin1]['number'] = 0;
            }
        }
        uasort($regions, function($a, $b){ return strcmp($a['name'], $b['name']); });
        // get numbers for admin units
        $query = sprintf("
            SELECT
                COUNT(m.id) number,
                a.admin1 admin1
            FROM
                members m,
                geonames g,
                geonames a
            WHERE
                a.country = '%1\$s'
                AND a.fcode = 'ADM1'
                AND g.country = a.country
                AND g.admin1 = a.admin1
                AND g.fclass = 'P'
                AND m.IdCity = g.geonameid
                AND m.Status = 'Active'
            GROUP BY
                a.admin1", $this->dao->escape($countrycode));
        $result = $this->dao->query($query);
        if (!$result) {
            return false;
        }
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $regions[$row->admin1]['number'] = $row->number;
        }

        return $regions;
    }

    public function checkRegionExists($regioncode, $countrycode) {
        $query = sprintf("
            SELECT
                a.admin1, a.country
            FROM
                geonames a
            WHERE
                a.fcode = 'ADM1'
                AND a.admin1 = '%1\$s'
                AND a.country = '%2\$s'", $this->dao->escape($regionscode), $this->dao->escape($countrycode));
        $result = $this->dao->query($query);
        if (!result) {
            return false;
        }
        $row = $result->fetch(PDB::FETCH_OBJ);
        if ($row) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Retrieve list of all cities for a region that have members
     * @param int $regionId Geoname ID of region
     * @return array List of cities with number of members
     */
    public function getAllCities($regioncode, $countrycode) {
        // get all cities for a given region
        // use MYSQL specific query trick to get only the first interesting result
        $query = sprintf("SELECT * FROM (
            SELECT
                g.geonameid geonameid, a.alternatename city, a.ispreferred ispreferred, a.isshort isshort, 'alt' source, COUNT(m.id) NbMember
            FROM
                geonames g,
                members m,
                geonamesalternatenames a
            WHERE
                g.country = '%1\$s'
                AND g.admin1 = '%2\$s'
                AND g.geonameid = m.IdCity
                AND g.geonameid = a.geonameid
                AND a.isoLanguage = '%3\$s'
                AND m.status = 'Active'
            GROUP BY
                geonameid
            UNION SELECT
                g.geonameid g, g.name AS city, 0 ispreferred, 0 isshort, 'geo' source, COUNT(m.id) NbMember
            FROM
                geonames g,
                members m
            WHERE
                g.country = '%1\$s'
                AND g.admin1 = '%2\$s'
                AND g.geonameid = m.IdCity
                AND m.status = 'Active'
            GROUP BY
                geonameid
            ORDER BY
                geonameid, source, ispreferred DESC, isshort DESC, city) x
            GROUP BY
                geonameid
            ", $this->dao->escape($countrycode), $this->dao->escape($regioncode), $this->dao->escape($this->lang));
        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve city list.');
        }
        $cities = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $cities[] = $row;
        }
        // Now that we know all citions fetch members
        return $cities;
    }
}

?>
