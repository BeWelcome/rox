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
        $this->lang = $this->session->get('_locale');
        if ($this->lang == 'zh-hant') {
            $this->lang = 'zh-TW';
        }
        if ($this->lang == 'pt-br') {
            $this->lang = 'pt';
        }
        if ($this->lang == 'zh-hans') {
            $this->lang = 'zh-CN';
        }
    }

    /**
     * get (alternate) name of given city
     */
    private function getCityName($geonameid) {
        $query = "
                SELECT
                    t.object_id AS geoname_id, t.content name
                FROM
                    geo__names_translations t
                WHERE
                    t.object_id = {$geonameid}
        ";
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
        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
        }
        $countQuery = $this->dao->query("SELECT FOUND_ROWS() as cnt");
        $count = $countQuery->fetch(PDB::FETCH_OBJ)->cnt;

        $members = [];
        $cities = [];
        while($row = $result->fetch(PDB::FETCH_OBJ)) {
            if (!isset($cities[$row->location])) {
                $cities[$row->location] = $this->getCityName($row->location);
            }
            $row->city = $cities[$row->location];
            $members[] = $row;
        }
        return [$count, $members];
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
                member m,
                geo__names g,
                address a
            WHERE
                m.status IN ('Active', 'OutOfRemind')
                AND m.id = a.member_id
                AND a.location = g.geoname_id
                AND g.feature_class = 'P'
                AND g.country_id = '%s'", $this->dao->escape($country));
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
                member m,
                geo__names g,
                address a
            WHERE
                m.status IN ('Active', 'OutOfRemind')
                AND m.id = a.member_id
                AND a.location = g.geoname_id
                AND g.feature_class = 'P'
                AND g.country_id = '%s'
                AND g.admin_1_id = '%s'", $this->dao->escape($country), $this->dao->escape($admin1));
        $row = $this->singleLookup($countQuery);
        return $row->cnt;
    }

    /**
     * get count of all members for a given city (geonameId)
     *
     */
    private function getTotalMemberCountCity($city) {
        $countQuery = sprintf("
            SELECT
                COUNT(*) cnt
            FROM
                member m,
                address a
            WHERE
                m.status IN ('Active', 'OutOfRemind')
                AND m.id = a.member_id
                AND a.location = %s", $this->dao->escape($city));

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
                m.HideAttribute,
                m.Accommodation,
                a.location,
                m.username
            FROM
                geo__names g,
                member m,
                address a
            WHERE
                m.status IN ('Active', 'OutOfRemind')
                AND m.MaxGuests >= 1
                AND m.id = a.member_id
                AND a.active = 1
                AND a.location = g.geoname_id
                AND g.country_id = '%s'
                AND g.feature_class = 'P'
            ORDER BY
                m.Accommodation DESC, m.LastActive DESC",
            $this->dao->escape($countrycode));
        [$count, $members] = $this->getMembersFiltered($query ." LIMIT "
            . ($pageNumber-1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE);
        return [$count, $totalCount, $members];
    }

    public function getMembersOfRegion($regioncode, $countrycode, $pageNumber) {
        $totalCount = $this->getTotalMemberCountRegion($countrycode, $regioncode);
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                m.BirthDate,
                m.HideAttribute,
                m.Accommodation,
                m.username,
                a.location,
                IF(m.AboutMe != 0, 1, 0) AS HasAboutMe
            FROM
                geo__names g,
                address a,
                member m
            WHERE
                m.status IN ('Active', 'OutOfRemind')
                AND m.MaxGuests >= 1
                AND m.id = a.member_id
                AND a.location = g.geoname_id
                AND g.admin_1_id = '%2\$s'
                AND g.country_id = '%1\$s'
                AND g.feature_class = 'P'
            ORDER BY
                m.Accommodation DESC, HasAboutMe DESC, m.LastActive DESC",
            $this->dao->escape($countrycode), $this->dao->escape($regioncode));
        [$count, $members] = $this->getMembersFiltered($query ." LIMIT "
            . ($pageNumber-1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE);
        return [$count, $totalCount, $members];
    }

    public function getMembersOfCity($cityCode, $cityName, $pageNumber) {
        $totalCount = $this->getTotalMemberCountCity($cityCode);
        $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                m.BirthDate,
                m.HideAttribute,
                m.Accommodation,
                m.username,
                a.location,
                IF(m.AboutMe != 0, 1, 0) AS HasAboutMe
            FROM
                geo__names g,
                address a,
                member m
            WHERE
                m.status IN ('Active', 'OutOfRemind')
                AND m.MaxGuests >= 1
                AND m.id = a.member_id
                AND a.active = 1
                AND a.location = g.geoname_id
                AND g.geoname_id = '%s'
                AND g.feature_class = 'P'
            ORDER BY
                m.Accommodation DESC, HasAboutMe DESC, m.LastActive DESC",
            $this->dao->escape($cityCode));
        [$count, $members] = $this->getMembersFiltered($query ." LIMIT "
            . ($pageNumber-1) * self::MEMBERS_PER_PAGE . ", " . self::MEMBERS_PER_PAGE);
        return [$count, $totalCount, $members];
    }

    private function compareCountryNames($a, $b) {
        return strcmp((string) $a->name, (string) $b->name);
        // $this->collator->compare($a->name, $b->name);
    }

    public function getContinents() {
        $words = new MOD_words();
        $continents = [
            "AM" => [$words->getSilent('PlacesAmerica'), $words->getSilent("PlacesAmericaCont")],
            "EA" => [$words->getSilent('PlacesEurAsia'), $words->getSilent("PlacesEurAsiaCont")],
            "AF" => [$words->getSilent('PlacesAfrica'),  $words->getSilent("PlacesAfricaCont")],
            "OC" => [$words->getSilent('PlacesOceania'), $words->getSilent("PlacesOceaniaCont")],
            "AN" => [$words->getSilent('PlacesAntarctica'), $words->getSilent("PlacesAntarcticaCont")]
        ];
        uasort($continents, function($a, $b) { return strcmp((string) $a[0], (string) $b[0]); });
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
                g.country_id AS country_id,
                g.country AS country,
                COUNT(m.id) AS count
            FROM
                geo__names g,
                member m,
                address a
            WHERE
                m.status IN ('Active', 'OutOfRemind')
                AND m.id = a.member_id
                AND a.location = g.geoname_id
                AND g.feature_class = 'P'
            GROUP BY
                g.country_id";

        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve country member counts.');
        }
        $count = [];
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $count[$row->country_id] = $row->count;
        }

        // Get all countries based on current language
        // use mysql only query to get only the first match
        $query = "
            SELECT
                c.country_id AS country_id,
                t.content AS name,
                c.continent AS continent
            FROM
                geo__countries c,
                geo__names_translations t
            WHERE
                c.country = t.object_id
                AND t.locale = '" . $this->dao->escape($this->lang) . "'
            ORDER BY
                continent ASC, country_id
            ";
        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve country list.');
        }

        // Pack both database results into country list
        $countries = [];
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            if (!isset($countries[$row->continent][$row->country_id])) {
                $data = new StdClass();
                $data->name = $row->name;
                $data->country = $row->country_id;
                $countries[$row->continent][$row->country_id] = $data;
            }
            if (isset($count[$row->country_id]) && 0!== $count[$row->country_id]) {
                $countries[$row->continent][$row->country_id]->number = $count[$row->country_id];
            } else {
                $countries[$row->continent][$row->country_id]->number = 0;
            }
        }
        // $this->collator = new Collator('root');
        foreach ($countries as &$continent) {
            usort($continent, [$this, 'compareCountryNames']);
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
        $query = sprintf("
            SELECT
                g.admin_1_id AS admin1,
                t.content AS name
            FROM
                geo__names g,
                geo__names_translations t
            WHERE
                g.country_id = '%1\$s'  
                AND g.feature_code = 'ADM1'
                AND g.geoname_id = t.object_id
                AND t.locale = '%2\$s'
            ORDER BY
                admin1, name                
            ", $this->dao->escape($countrycode), $this->dao->escape($this->lang));
        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve region list.');
        }

        $regions = [];
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            if (!isset($regions[$row->admin1])) {
                $regions[$row->admin1]['name'] = $row->name;
                $regions[$row->admin1]['number'] = 0;
            }
        }
        uasort($regions, function($a, $b){ return strcmp((string) $a['name'], (string) $b['name']); });
        // get numbers for admin units
        $query = sprintf("
            SELECT
                COUNT(m.id) number,
                g.admin_1_id AS admin1
            FROM
                member m,
                address a,
                geo__names g
            WHERE
                g.country_id = '%1\$s'
                AND g.feature_class = 'P'
                AND g.geoname_id = a.location
                AND a.active = 1
                AND a.member_id = m.id
                AND m.status IN ('Active', 'OutOfRemind')
                AND m.MaxGuests >= 1
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

    /**
     * Retrieve list of all cities for a region that have members
     * @param int $regionId Geoname ID of region
     * @return array List of cities with number of members
     */
    public function getAllCities($regioncode, $countrycode) {
        // get all cities for a given region
        // use MYSQL specific query trick to get only the first interesting result
        $query = sprintf("
            SELECT g.geoname_id, t.content AS city, count(m.id) AS count
            FROM
                member m,
                address a,
                geo__names g,
                geo__names_translations t
            WHERE
                m.status IN ('Active', 'OutOfRemind')
                AND m.MaxGuests >= 1
                AND m.id = a.member_id
                AND a.active = 1
                and a.location = g.geoname_id
                AND g.country_id = '%1\$s'
                AND g.admin_1_id = '%2\$s'
                AND g.geoname_Id = t.object_id
                AND t.locale = '%3\$s'
            GROUP BY
                geoname_id
            ORDER BY
                geoname_id, city
            ", $this->dao->escape($countrycode), $this->dao->escape($regioncode), $this->dao->escape($this->lang));
        $result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve city list.');
        }
        $cities = [];
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $cities[] = $row;
        }
        uasort($cities, function($a, $b){ return strcmp((string) $a->city, (string) $b->city); });
        return $cities;
    }
}
