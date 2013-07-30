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
 * Search model
 *
 * @package Search
 * @author shevek
 */
class SearchModel extends RoxModelBase
{
    // const ORDER_NOSORT = 0; // Not needed as this would be the same as for MEMBERSHIP
    const ORDER_USERNAME = 2;
    const ORDER_AGE = 4;
    const ORDER_ACCOM = 6;
    const ORDER_LOGIN = 8;
    const ORDER_MEMBERSHIP = 10;
    const ORDER_COMMENTS = 12;
    const ORDER_DISTANCE = 14;

    const SUGGEST_MAX_ITEMS = 30;

    private static $ORDERBY = array(
        self::ORDER_USERNAME => array('WordCode' => 'SearchOrderUsername', 'Column' => 'm.Username'),
        self::ORDER_ACCOM => array('WordCode' => 'SearchOrderAccommodation', 'Column' => 'm.Accomodation'),
        self::ORDER_DISTANCE => array('WordCode' => 'SearchOrderDistance', 'Column' => 'Distance'),
        self::ORDER_LOGIN => array('WordCode' => 'SearchOrderLogin', 'Column' => 'LastLogin'),
        self::ORDER_MEMBERSHIP => array('WordCode' => 'SearchOrderMembership', 'Column' => 'm.created'),
        self::ORDER_COMMENTS => array('WordCode' => 'SearchOrderComments', 'Column' => 'CommentCount'));

    public static function getOrderByArray() {
        return self::$ORDERBY;
    }

    private function getOrderBy($orderBy) {
        $orderType = $orderBy - ($orderBy % 2);
        $order = self::$ORDERBY[$orderType]['Column'];
        if ($orderBy % 2 == 1) {
            $order .= " DESC";
        } else {
            $order .= " ASC";
        }
        switch ($orderType) {
        	case self::ORDER_ACCOM:
        	case self::ORDER_COMMENTS:
        	    $order .= ', Distance ASC, HasProfileSummary DESC, HasProfilePhoto DESC, LastLogin DESC';
        	    break;
        	case self::ORDER_DISTANCE:
        	    $order .= ', m.Accomodation, HasProfileSummary DESC, HasProfilePhoto DESC, LastLogin DESC';
        	    break;
        }
        return $order;
    }

    /*
     * Return locations based on the name of the location
     * used when the Sphinx daemon isn't running
     */
    private function getLocationsFromDatabase($location) {
        $query = "
            SELECT
                g.geonameid AS geonameid, g.name AS name, g.latitude AS latitude,
                g.longitude AS longitude, a.name AS admin1, c.name AS country, COUNT(m.IdCity) AS cnt, 'SearchPlaces' AS category
            FROM
                members m, geonames g
            LEFT JOIN
                geonamescountries c
            ON
                g.country = c.country
            LEFT JOIN
                geonamesadminunits a
            ON
                g.country = a.country
                AND g.admin1 = a.admin1
                AND a.fcode = 'ADM1'
            WHERE
                g.name LIKE '" . $location . "%'
                AND g.fclass = 'P'
                AND g.fcode <> 'PPLH' AND g.fcode <> 'PPLW' AND g.fcode <> 'PPLQ' AND g.fcode <> 'PPLCH'
                AND m.IdCity = g.geonameid
                AND m.Status = 'Active'
            GROUP BY
                m.IdCity
            ORDER BY
                cnt DESC";
        $locations = $this->bulkLookup($query);

        return $locations;
    }

    //------------------------------------------------------------------------------
    // fage_value return a  the age value corresponding to date
    private function fage_value($dd) {
        $pieces = explode("-",$dd);
        if(count($pieces) != 3) return 0;
        list($year,$month,$day) = $pieces;
        $year_diff = date("Y") - $year;
        $month_diff = date("m") - $month;
        $day_diff = date("d") - $day;
        if ($month_diff < 0) $year_diff--;
        elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
        return $year_diff;
    } // end of fage_value

    private function ReplaceWithBR($ss,$ReplaceWith=false) {
        if (!$ReplaceWith) return ($ss);
        return(str_replace("\n","<br>",$ss));
    }

    private function FindTrad($IdTrad,$ReplaceWithBr=false) {

        $AllowedTags = "<b><i><br>";
        if ($IdTrad == "")
            return ("");

        if (isset($_SESSION['IdLanguage'])) {
             $IdLanguage=$_SESSION['IdLanguage'] ;
        }
        else {
             $IdLanguage=0 ; // by default laguange 0
        }
        // Try default language
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad AND
    IdLanguage= $IdLanguage
            "
        );
        $row = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                //LogStr("Blank Sentence for language " . $IdLanguage . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
            } else {
               return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        // Try default eng
        $query = $this->dao->query(
           "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad  AND
    IdLanguage = 0
            "
        );
        $row = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                //LogStr("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=" . $IdTrad, "Bug");
            } else {
               return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        // Try first language available
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad
ORDER BY id ASC
LIMIT 1
            "
        );
        $row = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                //LogStr("Blank Sentence (any language) memberstrads.IdTrad=" . $IdTrad, "Bug");
            } else {
               return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        return ("");
    } // end of FindTrad

    private function getNamePart($namePartId) {
        $namePart = "";
        if ($namePartId == 0) {
            return $namePart;
        }
        if (MOD_crypt::IsCrypted($namePartId) == 1) {
        } else {
            $namePart = MOD_crypt::get_crypted($namePartId, "");
        }
        return $namePart;
    }

    /**
     * If distance was provided create condition to take them into account
     *
     * @param  array		$vars: Variables from query (passed by reference)
     *
     * @return string    WHERE condition
     *
     */
    private function locationWhere($vars, $admin1, $country) {
        if ($country) {
            if ($admin1) {
                // We run based on an admin unit
                $where = "AND a.IdCity = g.geonameid
                AND g.admin1 = '" . $admin1 . "'
                AND g.country = '" . $country . "'";
            } else {
                // we're looking for all members of a country
                $where = "AND a.IdCity = g.geonameid
                AND g.country = '" . $country . "'";
            }
        } else {
            $where = "AND a.IdCity = g.geonameid";
            if (!empty($vars['search-location'])) {
                // a simple place with a square rectangle around it
                $distance = $vars['search-distance'];
                // calculate rectangle around place with given distance
                $lat = deg2rad(doubleval($vars['search-latitude']));
                $long = deg2rad(doubleval($vars['search-longitude']));

                $latne = rad2deg(($distance + 12740 * $lat) / 12740);
                $latsw = rad2deg((12740 * $lat - $distance) / 12740);

                $radiusAtLongitude = 6370 * cos($long);
                $longne = rad2deg(($distance + $radiusAtLongitude * $long) / $radiusAtLongitude);
                $longsw = rad2deg(($radiusAtLongitude * $long - $distance) / $radiusAtLongitude);

                // now fetch all location from geonames which are in that given rectangle
                $query = "
                    SELECT
                        g.geonameid AS geonameid
                    FROM
                        geonames g
                    WHERE
                        g.fclass = 'P'
                        AND g.fcode <> 'PPLH' AND g.fcode <> 'PPLW' AND g.fcode <> 'PPLQ' AND g.fcode <> 'PPLCH'
                        AND g.latitude < " . $latne . "
                        AND g.latitude > " . $latsw . "
                        AND g.longitude < " . $longne . "
                        AND g.longitude > " . $longsw;

                $where .= "
                        AND g.geonameid IN ('";
                $geonameids = $this->bulkLookup($query);
                foreach($geonameids as $geonameid) {
                    $where .= $geonameid->geonameid . "', '";
                }
                $where = substr($where, 0, -3) . ")";
            }
        }
        return $where;
    }

    /*
     *
     */
    private function getMemberDetails(&$vars, $admin1 = false, $country = false) {
        // First get current page and limits
        $limit = $vars['search-number-items'];
        $pageno = 1;
        foreach(array_keys($vars) as $key) {
            if (strstr($key, 'search-page-') !== false) {
                $pageno = str_replace('search-page-', '', $key);
            }
        }
        $start = ($pageno -1) * $limit;
        $vars['search-page-current'] = $pageno;
        // *FROM* and *WHERE* will be replaced later on (don't change)
        $str = "
            SELECT SQL_CALC_FOUND_ROWS
                m.id,
                m.Username,
                m.created,
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.TypicOffer,
                m.Restrictions,
                m.ProfileSummary,
                m.Occupation,
                m.Gender,
                m.HideGender,
                m.MaxGuest,
                m.FirstName,
                m.SecondName,
                m.LastName,
                date_format(m.LastLogin,'%Y-%m-%d') AS LastLogin,
                IF(m.ProfileSummary != 0, 1, 0) AS HasProfileSummary,
                IF(mp.photoCount IS NULL, 0, 1) AS HasProfilePhoto,
                g.latitude AS Latitude,
                g.longitude AS Longitude,
                ((g.latitude - " . $vars['search-latitude'] . ") * (g.latitude - " . $vars['search-latitude'] . ") +
                        (g.longitude - " . $vars['search-longitude'] . ") * (g.longitude - " . $vars['search-longitude'] . "))  AS Distance,
                g.name AS CityName,
                gc.name AS CountryName,
                IF(c.IdToMember IS NULL, 0, c.commentCount) AS CommentCount
            *FROM*
                addresses a,
                geonames g,
                geonamescountries gc,
                members m
            LEFT JOIN (
                SELECT
                    COUNT(*) As commentCount, IdToMember
                FROM
                    comments, members m2
                WHERE
                    IdFromMember = m2.id
                    AND m2.Status IN ('Active', 'ChoiceInActive', 'OutOfRemind')
                GROUP BY
                    IdToMember ) c
            ON
                c.IdToMember = m.id
            LEFT JOIN (
                SELECT
                    COUNT(*) As photoCount, IdMember
                FROM
                    membersphotos
                GROUP BY
                    IdMember) mp
            ON
                mp.IdMember = m.id
            *WHERE*
                m.MaxGuest >= " . $vars['search-can-host'] . "
                AND m.status = 'Active'
                AND m.id = a.idmember
                " . $this->locationWhere($vars, $admin1, $country) . "
                AND g.country = gc.country
            ORDER BY
                " . $this->getOrderBy($vars['search-sort-order']) . "
            LIMIT
                " . $start . ", " . $limit;

        // Make sure only public profiles are found if no one's logged in
        if (!$this->getLoggedInMember()) {
            $str = str_replace('*FROM*', 'FROM memberspublicprofiles mpp,', $str);
            $str = str_replace('*WHERE*', 'WHERE m.id = mpp.id AND ', $str);
        }
        $str = str_replace('*FROM*', 'FROM', $str);
        $str = str_replace('*WHERE*', 'WHERE', $str);

        $rawMembers = $this->bulkLookup($str);

        $count = $this->dao->query("SELECT FOUND_ROWS() as cnt");
        $row = $count->fetch(PDB::FETCH_OBJ);
        $vars['count'] = $row->cnt;

        $loggedInMember = $this->getLoggedInMember();

        $members = array();

        foreach($rawMembers as $member) {
            $aboutMe = MOD_layoutbits::truncate_words($this->FindTrad($member->ProfileSummary,true), 70);
            $FirstName = $this->getNamePart($member->FirstName);
            $SecondName = $this->getNamePart($member->SecondName);
            $LastName = $this->getNamePart($member->LastName);
            $member->Name = trim($FirstName . " " . $SecondName . " " . $LastName);
            $member->ProfileSummary = $aboutMe;

            if ($member->HideBirthDate=="No") {
                $member->Age =floor($this->fage_value($member->BirthDate));
            } else {
                $member->Age = "";
            }
            if ($member->HideGender != "Yes") {
                $member->GenderString = MOD_layoutbits::getGenderTranslated($member->Gender, false, false);
            }
            $member->Occupation = MOD_layoutbits::truncate_words($this->FindTrad($member->Occupation), 10);

            if ($loggedInMember) {
                // get message count for found member with current member
                $query = "
                    SELECT
                        COUNT(*) cnt
                    FROM
                        `messages`
                    WHERE
                        (IdSender = " . $member->id . " OR IdReceiver = " . $member->id . ")
                        AND (IdSender = " . $loggedInMember->id . " OR IdReceiver = " . $loggedInMember->id . ")";
                $messageCount = $this->singleLookup($query);
                $member->MessageCount = $messageCount->cnt;
            } else {
                $member->MessageCount = 0;
            }
            $members[] = $member;
        }

        return $members;
    }

	/*
	 * Returns either a list of members for a selected location or
	 * a list of possible locations based on the input text
	 */
    public function getResultsForLocation(&$vars) {
        // first we need to check if someone click on one of the suggestions buttons
        $geonameid = 0;
        foreach(array_keys($vars) as $key) {
            if (strstr($key, 'geonameid-') !== false) {
                $geonameid = str_replace('geonameid-', '', $key);
            }
        }
        if ($geonameid != 0) {
            $vars['search-geoname-id'] = $geonameid;
            // We need longitude and latitude for the search so let's fetch that
            $query = "SELECT g.latitude AS lat, g.longitude AS lng FROM geonames g WHERE g.geonameid = " . $geonameid;
            $row = $this->singleLookup($query);
            $vars['search-latitude'] = $row->lat;
            $vars['search-longitude'] = $row->lng;
            // Additionally we need to set the admin1 unit and the country for the given geonameid
            $query = "SELECT g.name AS name, a.name AS admin1, c.name AS country FROM geonames g, geonamesadminunits a, geonamescountries c
                    WHERE g.geonameid = " . $geonameid . " AND g.admin1 = a.admin1 AND g.country = a.country AND a.fcode = 'ADM1' AND g.country = c.country";
            $row = $this->singleLookup($query);
            $vars['search-location'] = $row->name . ", " . $row->admin1 . ", " . $row->country;
        }
        $country = "";
        foreach(array_keys($vars) as $key) {
            if (strstr($key, 'country-') !== false) {
                $country= str_replace('country-', '', $key);
            }
        }
        if (!empty($country)) {
            $location = $vars['search-location'];
            $vars['search-location'] = $location . ", " . $country;
        }
        $admin1 = "";
        foreach(array_keys($vars) as $key) {
            if (strstr($key, 'admin1-') !== false) {
                $admin1= str_replace('admin1-', '', $key);
            }
        }
        if (!empty($admin1)) {
            $locationParts = explode(",", $vars['search-location']);
            $vars['search-location'] = $locationParts[0] . ", " . $admin1 . ", " . $locationParts[1];
        }
        $results = array();
        $geonameid=$vars['search-geoname-id'];
        if ($geonameid == 0) {
            if (empty($vars['search-location'])) {
                // Search all over the world
                $results['type'] = 'members';
                $results['values'] = $this->getMemberDetails($vars);
            } else {
                // User didn't select from the suggestion list (javascript might be disabled)
                // get suggestions directly from the database
                $res = $this->suggestLocationsFromDatabase($vars['search-location']);
                if ($res["status"] == "success") {
                    if (count($res["locations"]) == 1) {
                        // found exactly one location get members for this one and return them
                        // todo
                        return $res;
                    } else {
                        return $res;
                    }
                }
            }
        } else {
            // we have a geoname id.
            // Let's check if it is an admin unit
            $query = "SELECT * FROM geonames WHERE geonameid = " . $geonameid;
            $location = $this->singleLookup($query);
            if ($location->fclass == 'A') {
                // check if found unit is a country
                if (strstr($location->fcode, 'PCL') === false) {
                    $results['type'] = 'members';
                    $results['values'] = $this->getMemberDetails($vars,
                        $location->admin1, $location->country);
                } else {
                    // get all members of that country
                    $results['type'] = 'members';
                    $results['values'] = $this->getMemberDetails($vars,
                            false, $location->country);
                }
            } else {
                // just get all active members from that place
                $results['type'] = 'members';
                $results['values'] = $this->getMemberDetails($vars);
            }
        }
        $results['count'] = $vars['count'];
        return $results;
    }

    private function getPlacesFromDatabase($ids) {
        $query = "
            SELECT
                g.geonameid AS geonameid, g.name AS name, g.latitude AS latitude, g.longitude AS longitude,
                a.name AS admin1, c.name AS country, IF(m.id IS NULL, 0, COUNT(g.geonameid)) AS cnt, '"
                    . $this->getWords()->getSilent('SearchPlaces') . "' AS category
            FROM
                geonames g
            LEFT JOIN
                geonamescountries c
            ON
                g.country = c.country
            LEFT JOIN
                geonamesadminunits a
            ON
                g.country = a.country
                AND g.admin1 = a.admin1
                AND a.fclass = 'A'
                AND a.fcode = 'ADM1'
            LEFT JOIN
                members m
            ON
                g.geonameid = m.IdCity
                AND m.Status = 'Active'
                AND m.MaxGuest >= 1
            WHERE
                g.geonameid in ('" . implode("','", $ids) . "')
            GROUP BY
                g.geonameid
            ORDER BY
                cnt DESC, country, admin1";
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $rows = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $rows[] = $row;
        }
        return $rows;
    }

    private function getFromDataBase($ids, $category = "") {
        // get country names for found ids
        $query = "
            SELECT
                a.geonameid AS geonameid, a.latitude AS latitude, a.longitude AS longitude, a.name AS admin1, c.name AS country, 0 AS cnt, '"
                    . $this->dao->escape($category) . "' AS category
            FROM
                geonames a
            LEFT JOIN
                geonamescountries c
            ON
                a.country = c.country
            WHERE
                a.geonameid in ('" . implode("','", $ids) . "')
            ORDER BY
                a.population DESC";
        $sql = $this->dao->query($query);
        if (!$sql) {
            return array();
        }
        $rows = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $rows[] = $row;
        }
        return $rows;
    }

    private function sphinxSearch( $location, $places, $count = false ) {
        $sphinx = new MOD_sphinx();
        $sphinxClient = $sphinx->getSphinxGeoname();
        if ($places) {
            $sphinxClient->SetFilter("isplace", array( 1 ));
        } else {
            $sphinxClient->SetFilter("isadmin", array( 1 ));
        }
        if ($count) {
            $sphinxClient->SetLimits(0, $count);
        }
        return $sphinxClient->Query($sphinxClient->EscapeString("^" . $location . "*"));
    }

    private function getPlaces($place, $admin1, $country, $limit = false) {
        $query = "
            SELECT
                g.geonameid, g.name AS name, a.name AS admin1, c.name AS country, '" . $this->getWords()->getSilent('SearchPlaces') . "' AS category
            FROM
                geonames g,
                geonamesadminunits a,
                geonamescountries c
            WHERE
                g.name LIKE '" . $this->dao->escape($place);
            if (strlen($place) > 5) {
                $query .= "%";
            }
            $query .= "'
                AND g.fclass = 'P'
                AND g.admin1 = a.admin1
                AND g.country = a.country
                AND a.fcode = 'ADM1'";
        if (!empty($admin1)) {
            $query .= " AND a.name LIKE '%" . $this->dao->escape($admin1) . "%'";
        }
        $query .= " AND g.country = c.country ";
        if (!empty($country)) {
           $query .= "AND c.name LIKE '%" . $this->dao->escape($country) . "%'";
        }
        $query .= " ORDER BY g.population DESC";
        if ($limit) {
            $query .= " LIMIT 0, " . $limit;
        }
        $rawPlaces = $this->bulkLookup($query);
        // get members count for each place
        $geonameids = array();
        foreach($rawPlaces as $rawPlace) {
            $geonameids[] = $rawPlace->geonameid;
        }
        $query = "
            SELECT
                g.geonameid, IF(m.id IS NULL, 0, COUNT(m.id)) cnt
           FROM
                geonames g
           LEFT JOIN
                members m
            ON
                g.geonameid = m.IdCity
                AND m.Status = 'Active'
                AND m.MaxGuest >= 1
            WHERE
                g.geonameid IN ('" . implode("', '", $geonameids) . "')
            GROUP BY
                g.geonameid
            ORDER BY
                cnt DESC";
        $rawNumbers = $this->bulkLookup($query);
        $places = array();
        foreach($rawNumbers as $rawNumber) {
            $places[$rawNumber->geonameid] = $rawNumber->cnt;
        }
        foreach($rawPlaces as $rawPlace) {
            $data = $rawPlace;
            // dirty trick to get the info together (unfortunately the SQL query takes far too long)
            $data->cnt = $places[$rawPlace->geonameid];
            $places[$rawPlace->geonameid] = $data;
        }
        return array_values($places);
    }

    private function getAdmin1Units($place, $country) {
        $query = "
            SELECT DISTINCT
                a.name AS admin1, '" . $country . "' AS country
            FROM
                geonames g,
                geonamesadminunits a,
                geonamescountries c
            WHERE
                g.name LIKE '" . $this->dao->escape($place) . "'
                AND g.admin1 = a.admin1
                AND g.country = a.country
                AND a.fcode = 'ADM1'
                AND g.country = c.country
                AND c.name LIKE '%" . $this->dao->escape($country) . "%'
            ORDER BY
                admin1";
        return $this->bulkLookup($query);
    }

    private function getCountries($place) {
        $query = "
            SELECT DISTINCT
                c.name AS country
            FROM
                geonames g,
                geonamescountries c
            WHERE
                g.name LIKE '" . $this->dao->escape($place) . "'
                AND g.country = c.country
            ORDER BY
                country";
        return $this->bulkLookup($query);
    }

    /*
     * Used when the user either has JavaScript disabled or just typed something and hit enter
     *
     * Assume that the format is location[, [admin1, ]country]
     *
     * Returns only places (can therefore be used by setlocation as well).
     * The result will depend on the number of found places.
     *
     * If the number of results is higher than 30 instead of the places a list of countries for the matching places
     * is returned. From this the user should select one or type it into the search box.
     *
     * If the number of results with a country given is still higher than 30 a list of matching admin units is provided
     * in the same fashion.
     *
     * The function doesn't return members. It is up to the callee to deal with the results
     */
    public function suggestLocationsFromDatabase($location) {
        $result = array();
        // first split $location so that we know if we need to search in countries and/or adminunits as well
        $admin1 = $country = "";
        $locationParts = explode(',', $location);
        $place = trim($locationParts[0]);
        switch (count($locationParts)) {
        	case 3:
        	    $admin1 = trim($locationParts[1]);
                $country = trim($locationParts[2]);
                break;
        	case 2:
        	    $country = trim($locationParts[1]);
        	    break;
        }
        $result['status'] = 'failed';
        $query = "
            SELECT
                COUNT(*) cnt
            FROM
                geonames g";
        if (!empty($admin1)) {
           $query .= ", geonamesadminunits a";
        }
        if (!empty($country)) {
           $query .= ", geonamescountries c";
        }
        $query .= "
            WHERE
                g.name LIKE '" . $this->dao->escape($place);
        if (strlen($place) >= 3) {
            $query .= "%";
        }
        $query .= "'
                AND g.fclass = 'P'";
        if (!empty($admin1)) {
           $query .= " AND g.admin1 = a.admin1 AND g.country = a.country AND a.fcode = 'ADM1' AND a.name LIKE '%" . $this->dao->escape($admin1) . "%'";
        }
        if (!empty($country)) {
           $query .= " AND g.country = c.country AND c.name LIKE '%" . $this->dao->escape($country) . "%'";
        }
        $row = $this->singleLookup($query);
        $count = $row->cnt;
        if ($count > self::SUGGEST_MAX_ITEMS) {
            if (empty($country)) {
                // get countries for matching places
                $locations = $this->getCountries($place);
                $result['type'] = 'countries';
            } else {
                // get admin units for matching places in the given country
                $locations = $this->getAdmin1Units($place, $country);
                $result['type'] = 'admin1s';
            }
        } else {
            $locations = $this->getPlaces($place, $admin1, $country);
            $result['type'] = 'places';
        }
        $result['status'] = 'success';
        $result['locations'] = $locations;
        $result['count'] = count($locations);
        return $result;
    }

    /*
     * Used as AJAX source by the autosuggest on the search form
     */
    public function suggestLocations($location, $type) {
        $result = array();
        $locations = array();
        $result['status'] = 'failed';
        // First get places with BW members
        $resPlaces = $this->sphinxSearch( $location, true );
        if ($resPlaces !== false && $res['total'] != 0) {
            $ids = array();
            if (is_array($res['matches'])) {
                foreach ( $res['matches'] as $docinfo ) {
                    $ids[] = $docinfo['id'];
                }
            }
            $places = $this->getPlacesFromDataBase($ids);
            $locations = array_merge($locations, $places);
            $result['result'] = 'success';
            $result['places'] = 1;
        }
        if ($resPlaces !== false) {
            // Get administrative units only when places call actually worked
            $res = $this->sphinxSearch( $location, false );
            if ( $res !==false  && $res['total'] != 0) {
                $ids = array();
                if (is_array($res['matches'])) {
                    foreach ( $res['matches'] as $docinfo ) {
                        $ids[] = $docinfo['id'];
                    }
                }
                $adminunits= $this->getFromDataBase($ids, $this->getWords()->getSilent('SearchAdminUnits'));
                $locations = array_merge($locations, $adminunits);
                $result["status"] = "success";
                $result['adminunits'] = 1;
            }
        }

        // If nothing was found assume that search daemon isn't running and
        // try to get something from the database
        if (empty($locations)) {
            // assume format place[, [admin1,] country
            $admin1 = $country = "";
            $locationParts = explode(',', $location);
            $place = trim($locationParts[0]);
            switch (count($locationParts)) {
            	case 3:
            	    $admin1 = trim($locationParts[1]);
            	    $country = trim($locationParts[2]);
            	    break;
            	case 2:
            	    $country = trim($locationParts[1]);
            	    break;
            }
            $locations = $this->getPlaces($place, $admin1, $country, 10);
            $result['database'] = 1;
        }
        if (!empty($locations)) {
            $result['status'] = 'success';
        }
        $result["locations"] = $locations;
        return $result;
    }
}
?>