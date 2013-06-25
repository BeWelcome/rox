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
    public function getMembersForLocation($vars) {
        $geonameid=$vars['search-geoname-id'];
        if ($geonameid == 0) {
            // Todo: Try to get id from search location
        }
        // now geoname id should be set
        $query = "SELECT m.* FROM members m WHERE m.status = 'active' AND m.IdCity = " . $geonameid;
        error_log($query);
        $members = $this->bulkLookup( $query );
        return $members;
    }

    private function getBiggestCities($ids, $count = 3) {
        $query = "
            SELECT
                a.IdCity AS geonameid, COUNT(a.IdCity) AS cnt
            FROM
                geonames g, addresses a
            WHERE
                g.geonameid in ('" . implode("','", $ids) . "') AND g.fclass='P' AND a.IdCity = g.geonameid
            GROUP BY
                a.IdCity
            ORDER BY
                cnt DESC
            LIMIT 0, " . $count;
        error_log($query);
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $rows = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $rows[] = $row->geonameid;
        }
        return $rows;
    }

    private function getFromDataBase($ids, $category = "") {
        // get current UI language
        $language = $_SESSION['lang'];
        error_log($language);
        // get country names for found ids
        $query = "
            SELECT DISTINCT
                g.geonameid AS geonameid, g.name AS name, a.name AS admin1, c.name AS country
            FROM
                geonames g
            LEFT JOIN
                geonames c
            ON
               c.country = g.country AND g.country = c.country
               AND c.fcode = 'PCLI'
           LEFT JOIN
               geonames a
           ON
               g.country = a.country AND g.admin1 = a.admin1 AND a.fcode = 'ADM1'
               AND g.geonameid <> a.geonameid
           WHERE
               g.geonameid in ('" . implode("','", $ids) . "')";
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

    public function suggestLocations($location, $type) {
        $result = array();
        $result["result"] = "failed";
        $sphinx = new MOD_sphinx();
        $sphinxClient = $sphinx->getSphinxGeoname();
        $res = $sphinxClient->Query ($sphinxClient->EscapeString($location), 'welen_geoname' );
        if ( $res===false )
        {

            return $result;
        }
        if ($res['total'] == 0) {
            return $result;
        }
        $ids = array();
        if (is_array($res["matches"])) {
            foreach ( $res["matches"] as $docinfo ) {
                $ids[] = $docinfo['id'];
            }
        }
        $biggestids = $this->getBiggestCities($ids);
        $locations = array();
        $result["result"] = "success";
        $biggest = $this->getFromDataBase($biggestids, "biggest");
        $locations = array_merge($locations, $biggest);
        $places = $this->getFromDataBase($ids, "places");
        $locations = array_merge($locations, $places);
        $result["locations"] = $locations;

        return $result;
    }
}
?>