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

class Places extends RoxModelBase {
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
     * getWikiPage fetches the information from the old geonames_cache DB to create the
     * name of the wiki page. This is due to the fact that the content of th geonames_cache doesn't match
     * with geonames for some reasons.
     *
     * @param string $country
     * @param string $admin1
     * @param string $geonameid
     * @return boolean|mixed
     */
    public function getWikiPage($country = false, $admin1 = false, $geonameid = false) {
        // Figured out the geoname id from geonames
        $query = "
            SELECT
                g.geonameid geonameid
            FROM
                geonames g
            WHERE ";
        if ($country) {
            if ($admin1) {
                $query .= "g.fcode = 'ADM1' AND g.country = '" . $this->dao->escape($country) . "' ";
                $query .= "AND g.admin1 = '" . $this->dao->escape($admin1) . "' ";
            } else {
                $query .= "g.fcode LIKE 'PCLI%' AND g.country = '" . $this->dao->escape($country) . "' ";
            }
            $result = $this->singleLookup($query);
            if (!$result) {
                return false;
            }
            $geonameid = $result->geonameid;
        }
        $query = "SELECT name FROM geonames_cache WHERE geonameid = '" . $this->dao->escape($geonameid) . "'";
        $result = $this->singleLookup($query);
        if (!$result) {
            return false;
        }
        if (isset($result->name)) {
            return str_replace(' ', '', ucwords($result->name));
        } else {
            $query = "SELECT name FROM geonames WHERE geonameid = '" . $this->dao->escape($geonameid) . "'";
            $result = $this->singleLookup($query);
            if (!$result) {
                return false;
            }
            if (isset($result->name)) {
                return str_replace(' ', '', ucwords($result->name));
            } else {
                return false;
            }
        }
    }

    /**
     * get (alternate) name of given city
     */
    private function getCityName($geonameid) {
        $query = "SELECT *  FROM (
                SELECT
                    g.geonameid geonameid, g.name name, 0 ispreferred, 0 isshort, 'geo' source
                FROM
                    geonames g
                WHERE
                    geonameid = {$geonameid}
                UNION SELECT
                    a.geonameid geonameid, a.alternatename name, ispreferred, isshort, 'alt' source
                FROM
                    geonamesalternatenames a
                WHERE
                    geonameid = {$geonameid}
                    AND isolanguage = '{$this->lang}'
                ORDER BY
                    geonameid, ispreferred DESC, isshort DESC, source, name
            ) geo
            GROUP BY
                geonameid";
        $result = $this->dao->query($query);
        if (!$result) {
            return false;
        }
        $row = $result->fetch(PDB::FETCH_OBJ);
        return $row->name;
    }

    /**
     * get members and count based on privacy setting
     *
     */
    private function getMembersFiltered($query) {
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
        $countQuery = $this->dao->query("SELECT FOUND_ROWS() as cnt");
        $count = $countQuery->fetch(PDB::FETCH_OBJ)->cnt;

        $members = array();
        $cities = array();
        while($row = $result->fetch(PDB::FETCH_OBJ)) {
            if (!isset($cities[$row->idCity])) {
                $cities[$row->idCity] = $this->getCityName($row->idCity);
            }
            $row->city = $cities[$row->idCity];
            $members[] = $row;
        }
        return array($count, $members);
    } // end of getMembersAll

    /**
     * get count of all members for a given country
     *
     */
    private function getTotalMemberCountCountry($country) {
        $countQuery = sprintf("
            SELECT
                COUNT(*) cnt
            FROM
                members m,
                geonames g
            WHERE
                m.status = 'Active'
                AND m.MaxGuest >= 1
                AND m.IdCity = g.geonameid
                AND g.country = '%s'", $this->dao->escape($country));
        $row = $this->singleLookup($countQuery);
        return $row->cnt;
    }

    /**
     * get count of all members for a given admin1 of country
     *
     */
    private function getTotalMemberCountRegion($country, $admin1) {
        $countQuery = sprintf("
            SELECT
                COUNT(*) cnt
            FROM
                members m,
                geonames g
            WHERE
                m.status = 'Active'
                AND m.MaxGuest >= 1
                AND m.IdCity = g.geonameid
                AND g.country = '%s'
                AND g.admin1 = '%s'", $this->dao->escape($country), $this->dao->escape($admin1));
        $row = $this->singleLookup($countQuery);
        return $row->cnt;
    }

    /**
     * get count of all members for a given city (geonameid)
     *
     */
    private function getTotalMemberCountCity($city) {
        $countQuery = sprintf("
            SELECT
                COUNT(*) cnt
            FROM
                members m
            WHERE
                m.status = 'Active'
                AND m.MaxGuest >= 1
                AND m.IdCity = %s", $this->dao->escape($city));

        $row = $this->singleLookup($countQuery);
        return $row->cnt;;
    }

    /**
     * Get all members of a country
     * @param string $countryCode Two-letter country code, i.e. "BE"
     * @return object Region with its name and ID
     */
    public function getMembersOfCountry($countrycode, $pageNumber) {
        $totalCount = $this->getTotalMemberCountCountry($countrycode);
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.idCity,
                m.username,
                IF(m.ProfileSummary != 0, 1, 0) AS HasProfileSummary
            FROM
                geonames g,
                members m
            WHERE
                m.Status = 'Active'
                AND m.MaxGuest >= 1
                AND g.geonameId = m.idCity
                AND g.country = '%s'
            ORDER BY
                m.Accomodation ASC, HasProfileSummary DESC, m.LastLogin DESC",
            $this->dao->escape($countrycode));
        list($count, $members) = $this->getMembersFiltered($query ." LIMIT "
            . ($pageNumber-1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE);
        return array($count, $totalCount, $members);
    }

    public function getMembersOfRegion($regioncode, $countrycode, $pageNumber) {
        $totalCount = $this->getTotalMemberCountRegion($countrycode, $regioncode);
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.username,
                m.idCity,
                IF(m.ProfileSummary != 0, 1, 0) AS HasProfileSummary
            FROM
                geonames g,
                members m
            WHERE
                m.Status = 'Active'
                AND m.MaxGuest >= 1
                AND m.idCity = g.geonameid
                AND g.admin1 = '%2\$s'
                AND g.country = '%1\$s'
                AND g.fclass = 'P'
            ORDER BY
                m.Accomodation ASC, HasProfileSummary DESC, m.LastLogin DESC",
            $this->dao->escape($countrycode), $this->dao->escape($regioncode));
        list($count, $members) = $this->getMembersFiltered($query ." LIMIT "
            . ($pageNumber-1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE);
        return array($count, $totalCount, $members);
    }

    public function getMembersOfCity($cityCode, $cityName, $pageNumber) {
        $totalCount = $this->getTotalMemberCountCity($cityCode);
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.username,
                m.idCity,
                IF(m.ProfileSummary != 0, 1, 0) AS HasProfileSummary
            FROM
                geonames g,
                members m
            WHERE
                m.Status = 'Active'
                AND m.MaxGuest >= 1
                AND m.IdCity = g.geonameid
                AND g.geonameid = '%s'
            ORDER BY
                m.Accomodation ASC, HasProfileSummary DESC, m.LastLogin DESC",
            $this->dao->escape($cityCode));
        list($count, $members) = $this->getMembersFiltered($query ." LIMIT "
            . ($pageNumber-1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE);
        return array($count, $totalCount, $members);
    }

    private function compareCountryNames($a, $b) {
        return strcmp($a->name, $b->name);
        // $this->collator->compare($a->name, $b->name);
    }

    public function getContinents() {
        $words = new MOD_words();
        $continents = array(
            "AM" => array($words->getSilent('PlacesAmerica'), $words->getSilent("PlacesAmericaCont")),
            "EA" => array($words->getSilent('PlacesEurAsia'), $words->getSilent("PlacesEurAsiaCont")),
            "AF" => array($words->getSilent('PlacesAfrica'),  $words->getSilent("PlacesAfricaCont")),
            "OC" => array($words->getSilent('PlacesOceania'), $words->getSilent("PlacesOceaniaCont")),
            "AN" => array($words->getSilent('PlacesAntarctica'), $words->getSilent("PlacesAntarcticaCont"))
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
                m.Status = 'Active'
                AND m.MaxGuest >= 1
                AND m.IdCity = g.geonameid
                AND g.fclass = 'P'
                AND g.country = c.country
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
                continent ASC, country, isshort DESC, ispreferred DESC, source ASC, name ASC) x
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
                ga.geonameid = a.geonameid
                AND ga.country = '%1\$s'
                AND ga.fcode = 'ADM1'
                AND a.isoLanguage = '%2\$s'
            ORDER BY
                admin1, isshort DESC, ispreferred DESC, source ASC, region ASC) x
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
                g.admin1 admin1
            FROM
                members m,
                geonames g
            WHERE
                g.country = '%1\$s'
                AND g.fclass = 'P'
                AND g.geonameid = m.IdCity
                AND m.Status = 'Active'
                AND m.MaxGuest >= 1
            GROUP BY
                g.admin1", $this->dao->escape($countrycode));

        $result = $this->dao->query($query);
        if (!$result) {
            return false;
        }
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            if (array_key_exists($row->admin1, $regions)){
                $regions[$row->admin1]['number'] = $row->number;
            }
        }
        
        // remove regions without members
        foreach ($regions as $key=>$region){
            if ($region['number']==0){
                unset($regions[$key]);
            }
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
            SELECT geonameid, city, ispreferred,isshort, source,count(m.id) NbMember
            FROM (
                SELECT * from (
                    SELECT g.geonameid geonameid, a.alternatename city,
                        a.ispreferred ispreferred, a.isshort isshort, 'alt' source
                    FROM
                        geonamesalternatenames a,
                        geonames g
                    WHERE
                        g.country = '%1\$s'
                        AND g.admin1 = '%2\$s'
                        AND g.geonameid = a.geonameid
                        AND a.isoLanguage = '%3\$s'
                    ORDER BY isshort DESC, ispreferred DESC
                ) allA
            GROUP BY geonameid
            ) AByGid,
            members m 
            WHERE m.idcity = AByGid.geonameid
                AND m.status = 'Active'
                AND m.MaxGuest >= 1
            GROUP BY geonameid
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
                AND m.MaxGuest >= 1
            GROUP BY
                geonameid
            ORDER BY
                geonameid, isshort DESC, ispreferred DESC, source ASC, city ASC) ag
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
        uasort($cities, function($a, $b){ return strcmp($a->city, $b->city); });
        return $cities;
    }
}

?>
