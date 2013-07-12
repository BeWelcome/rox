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
    /*
     * Depending on the number of results of a DB query returns
     * a list of countries, admin units or places for a given
     * location so that the user can choose from them.
     */
    private function getLocationsFromDatabase($location) {
        $query = "
            SELECT
                g.*
            FROM
                geonames g, alternatenames a
            WHERE
                a.alternatename LIKE '" . $location . "'
            UNION SELECT
                g.*
            FROM
                geonames g
            WHERE
                g.name LIKE '" . $location . "'";
        $querycnt = "SELECT COUNT(*) cnt FROM ( " . $query . " ) AS tmp";
        error_log($querycnt);
        $locCount = $this->singleLookup($querycnt);
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

    private function ellipsis($str, $len)
    {
        $length = strlen($str);
        if($length <= $len) return $str;
        return mb_substr($str, 0, $len, 'utf-8').'...';
    }

    /*
     *
     */
    private function getMemberDetails($rawIds, $order = false) {
        $ids = array();
        foreach($rawIds as $rawId) {
            $ids[] = $rawId->id;
        }
        $str = "
            SELECT
                m.*,
                date_format(m.LastLogin,'%Y-%m-%d') AS LastLogin,
                g.latitude AS Latitude,
                g.longitude AS Longitude,
                g.name AS CityName,
                gc.name AS CountryName
            FROM
                members m,
                addresses a,
                geonames g,
                geonamescountries gc
            WHERE
                m.id IN ('" . implode("', '", $ids) . "')
                AND m.id = a.idmember
                AND a.IdCity = g.geonameid
                AND g.country = gc.country";


        $rawMembers = $this->bulkLookup($str);
        $members = array();

        foreach($rawMembers as $member) {
            $aboutMe = $this->ellipsis($this->FindTrad($member->ProfileSummary,true), 200);

            $member->ProfileSummary = $aboutMe;

            $commentQuery ="
            SELECT
                COUNT(*) as CommentCount
            FROM
                comments c,
                members m
            WHERE
                c.IdToMember =" . $member->id . "
                AND m.id = c.IdFromMember
                AND m.status IN ('Active', 'ChoiceInactive')";
            $commentData = $this->singleLookup($commentQuery);
            $member->CommentCount=$commentData->CommentCount;

            if ($member->HideBirthDate=="No") {
                $member->Age =floor($this->fage_value($member->BirthDate));
            } else {
                $member->Age = "";
            }
            $members[] = $member;
        }

        return $members;
    }

	/*
	 * Returns either a list of members for a selected location or
	 * a list of possible locations based on the input text
	 */
    public function getResultsForLocation($vars) {
        $results = array();
        $geonameid=$vars['search-geoname-id'];
        if ($geonameid == 0) {
            // User didn't select from the suggestion list or the sphinx daemon died
            // get suggestions directly from the database
            $results['type'] = 'suggestions';
            $res = $this->getLocationsFromDatabase($vars['search-location']);
        } else {
            // we have a geoname id so we can just get all active members from that place
            $results['type'] = 'members';
            $query = "SELECT COUNT(*) cnt FROM members m WHERE m.status IN ('active', 'choiceinactive') AND m.IdCity = " . $geonameid;
            $cnt = $this->singleLookup($query);
            $results['count'] = $cnt->cnt;
            $query = "
                SELECT
                    m.id
                FROM
                    members m
                WHERE
                    m.status IN ('active', 'choiceinactive') AND m.IdCity = " . $geonameid . "
                LIMIT
                    0, 5";
            $memberIds = $this->bulkLookup( $query );
            $results['values'] = $this->getMemberDetails($memberIds);
        }
        return $results;
    }

    private function getPlacesFromDatabase($ids) {
        $time = time();
        $query = "
            SELECT
                g.geonameid AS geonameid, g.name AS name, a.name AS admin1, c.name AS country, IF (ad.idCity IS NULL, 0, COUNT(ad.IDCity)) cnt
            FROM
                members m, geonames g
            LEFT JOIN
                geonamescountries c
            ON
                g.country = c.country
            LEFT JOIN
                geonamesadminunits a
            ON
                g.country = a.country AND
                g.admin1 = a.admin1 AND
                a.fcode = 'ADM1'
            LEFT JOIN
                addresses ad
            ON
                ad.IdCity = g.geonameId
            WHERE
                g.geonameid in ('" . implode("','", $ids) . "')
                AND ad.IdMember = m.id AND m.Status IN ('active', 'choiceinactive')
            GROUP BY
                g.geonameid
            ORDER BY
                cnt DESC";
        $duration = time() - $time;
        error_log($duration . ":" . $query);
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $rows = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $row->category = "places";
            $rows[] = $row;
        }
        return $rows;
    }

    private function getFromDataBase($ids, $category = "") {
        // get country names for found ids
        $query = "
            SELECT
                a.geonameid AS geonameid, a.name AS admin1, c.name AS country
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
            return false;
        }
        $rows = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $row->category = $category;
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
        return $sphinxClient->Query($sphinxClient->EscapeString("^" . $location));
    }

    public function suggestLocations($location, $type) {
        $result = array();
        $locations = array();
        $result["result"] = "failed";
        // First get places with BW members
        $res = $this->sphinxSearch( $location, true );
        error_log(print_r($res, true));
        if ($res!==false && $res['total'] != 0) {
            $ids = array();
            if (is_array($res["matches"])) {
                foreach ( $res["matches"] as $docinfo ) {
                    $ids[] = $docinfo['id'];
                }
            }
            $places = $this->getPlacesFromDataBase($ids);
            $locations = array_merge($locations, $places);
            $result["result"] = "success";
        }
        // Get administrative units
        $res = $this->sphinxSearch( $location, false );
        if ( $res !==false  && $res['total'] != 0) {
            $ids = array();
            if (is_array($res["matches"])) {
                foreach ( $res["matches"] as $docinfo ) {
                    $ids[] = $docinfo['id'];
                }
            }
            $adminunits= $this->getFromDataBase($ids, "adminunits");
            $locations = array_merge($locations, $adminunits);
            $result["result"] = "success";
        }
        $result["locations"] = $locations;

        return $result;
    }
}
?>