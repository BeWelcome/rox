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
    const ORDER_NAME = 2;
    const ORDER_AGE = 4;
    const ORDER_ACCOM = 6;
    const ORDER_LOGIN = 8;
    const ORDER_MEMBERSHIP = 10;
    const ORDER_COMMENTS = 12;

    private static $ORDERBY = array(
        self::ORDER_NAME => array('WordCode' => 'SearchOrderName', 'Column' => 'm.Username'),
        self::ORDER_ACCOM => array('WordCode' => 'SearchOrderAccommodation', 'Column' => 'm.Accomodation'),
        self::ORDER_LOGIN => array('WordCode' => 'SearchOrderLogin', 'Column' => 'LastLogin'),
        self::ORDER_MEMBERSHIP => array('WordCode' => 'SearchOrderMembership', 'Column' => 'm.created'),
        self::ORDER_COMMENTS => array('WordCode' => 'SearchOrderComments', 'Column' => 'CommentCount'));

    public static function getOrderByArray() {
        return self::$ORDERBY;
    }

    /*
     * Depending on the number of results of a DB query returns
     * a list of countries, admin units or places for a given
     * location so that the user can choose from them.
     */
    private function getLocationsFromDatabase($location) {
        $query = "
            SELECT
                g.geonameid AS geonameid, g.name AS name, a.name AS admin1, c.name AS country, COUNT(m.IdCity) AS cnt, 'SearchPlaces' AS category
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
                AND m.Status IN ('Active')
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
        	    $order .= ', HasProfileSummary DESC, HasProfilePhoto DESC';
        	    break;
        }
        return $order;
    }

    /*
     *
     */
    private function getMemberDetails(&$vars) {
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
        // *FROM* will be replaced later on (don't change)
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
                    IdToMember = m2.id
                    AND m2.Status IN ('Active', 'OutOfRemind')
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
            WHERE
                m.MaxGuest >= " . $vars['search-can-host'] . "
                AND m.IdCity = " . $vars['search-geoname-id'] . "
                AND m.status IN ('Active', 'OutOfRemind')
                AND m.id = a.idmember
                AND a.IdCity = g.geonameid
                AND g.country = gc.country
            ORDER BY
                " . $this->getOrderBy($vars['search-sort-order']) . "
            LIMIT
                " . $start . ", " . $limit;

        // Make sure only public profiles are found if no one's logged in
        if (!$this->getLoggedInMember()) {
            $str = str_replace('*FROM*', 'FROM memberspublicprofiles mpp,', $str);
            $str = str_replace('WHERE', 'WHERE m.id = mpp.id AND ', $str);
        }
        $str = str_replace('*FROM*', 'FROM', $str);

        error_log($str);
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
        error_log(print_r($vars, true));
        $results = array();
        $geonameid=$vars['search-geoname-id'];
        if ($geonameid == 0) {
            // User didn't select from the suggestion list or the sphinx daemon died
            // get suggestions directly from the database
            $results['type'] = 'suggestions';
            $results['values'] = array('boring', 'sucks');
            // $res = $this->getLocationsFromDatabase($vars['search-location']);
        } else {
            // we have a geoname id so we can just get all active members from that place
            $results['type'] = 'members';
            $results['values'] = $this->getMemberDetails($vars);
            $results['count'] = $vars['count'];
        }
        return $results;
    }

    private function getPlacesFromDatabase($ids) {
        $query = "
            SELECT
                g.geonameid AS geonameid, g.name AS name, a.name AS admin1, c.name AS country, IF(m.id IS NULL, 0, COUNT(g.geonameid)) AS cnt, '"
                    . $this->getWords()->getSilent('SearchPlaces') . "' AS category
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
                AND a.fclass = 'A'
                AND a.fcode = 'ADM1'
            LEFT JOIN
                members m
            ON
                g.geonameid = m.IdCity
                AND m.Status IN ('Active', 'OutOfRemind')
                AND m.MaxGuest >= 1
            WHERE
                g.geonameid in ('" . implode("','", $ids) . "')
            GROUP BY
                g.geonameid
            ORDER BY
                cnt DESC, country, admin1";
        error_log($query);
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
                a.geonameid AS geonameid, a.name AS admin1, c.name AS country, 0 AS cnt
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
        error_log($query);
        $sql = $this->dao->query($query);
        if (!$sql) {
            return array();
        }
        $rows = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $row->category = $this->getWords()->getSilent('SearchPlaces');
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

    public function suggestLocations($location, $type) {
        $result = array();
        $locations = array();
        $result['result'] = 'failed';
        // First get places with BW members
        $res = $this->sphinxSearch( $location, true );
        if ($res!==false && $res['total'] != 0) {
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
        // Get administrative units
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
            $result["result"] = "success";
            $result['adminunits'] = 1;
        }
        // If nothing was found assume that search daemon isn't running and
        // try to get something from the database
        if (empty($locations)) {
            $locations = $this->getLocationsFromDatabase($location);
            $result['database'] = 1;
        }
        if (!empty($locations)) {
            $result['result'] = 'success';
        }
        $result["locations"] = $locations;

        return $result;
    }
}
?>