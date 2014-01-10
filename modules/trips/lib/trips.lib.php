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
 * Build an array with the last visits on the member IdMember
  * assumed it is the currently logged member if no IdMember is provided
 * @author Philipp Lange
 */
class MOD_trips {


    /**
     * Singleton instance
     *
     * @var MOD_trips
     * @access private
     */
    private static $_instance;

    public function __construct()
    {

        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
    }

    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

     /** Retrieve the last accepted profile in the city of the member with a picture

     */
    public function RetrieveVisitorsInCityWithAPicture($IdMember, $limit = 3)
    {
        // Reuse activities nearme or add new preference
        $distance = 50;

        // get all locations in a certain area
        $member = new Member($IdMember);
        $query = "SELECT latitude, longitude FROM geonames WHERE geonameid = " . $member->IdCity;
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $row = $sql->fetch(PDB::FETCH_OBJ);

        // calculate rectangle around place with given distance
        $lat = deg2rad(doubleval($row->latitude));
        $long = deg2rad(doubleval($row->longitude));

        $longne = rad2deg(($distance + 6378 * $long) / 6378);
        $longsw = rad2deg((6378 * $long - $distance) / 6378);

        $radiusAtLatitude = 6378 * cos($lat);
        $latne = rad2deg(($distance + $radiusAtLatitude * $lat) / $radiusAtLatitude);
        $latsw = rad2deg(($radiusAtLatitude * $lat - $distance) / $radiusAtLatitude);
        if ($latne < $latsw) {
            $tmp = $latne;
            $latne = $latsw;
            $latsw = $tmp;
        }
        if ($longne < $longsw) {
            $tmp = $longne;
            $longne = $longsw;
            $longsw = $tmp;
        }

        $TTrips = array();

        // $query .= "FROM activities AS a, geonames AS g WHERE a.locationId = g.geonameid ";
        $rectangle = 'AND geonames.latitude < ' . $latne . '
            AND geonames.latitude > ' . $latsw . '
            AND geonames.longitude < ' . $longne . '
            AND geonames.longitude > ' . $longsw;

        // retrieve the visiting members handle and trip data
        $query = "
            SELECT SQL_CACHE
                bd.blog_id           AS tripId,
                bd.blog_start        AS tripDate,
                members.Username,
                members.IdCity,
                geonames.name  AS city
            FROM
                blog            AS b,
                blog_data       AS bd,
                members,
                geonames
            WHERE
                b.blog_id = bd.blog_id
                AND b.trip_id_foreign IS NOT NULL
                AND b.IdMember = members.id
                AND bd.blog_geonameid = geonames.geonameid " .
                $rectangle . "
                AND bd.blog_start >= CURDATE() AND bd.blog_start <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
            ORDER BY
                bd.blog_start ASC
            LIMIT
                $limit
            ";
    		$s = $this->dao->query($query);
				if (!$s) {
			 		 throw new PException('Cannot retrieve last member with photo!');
				}
				while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				    $geo = new Geo($row->IdCity);
				    $country = "SELECT * from geonamescountries WHERE country = '" . $this->dao->escape($geo->country) . "'";
				    $countrySql = $this->dao->query($country);
				    $countryRow = $countrySql->fetch(PDB::FETCH_OBJ);
				    if (isset($countryRow->name)) {
				        $row->country = $countryRow->name;
				    } else {
				        $row->country = "";
				    }
	  			    array_push($TTrips, $row);
				} // end of while on visits
				return($TTrips) ;

		} // end of	RetrieveLastAcceptedProfileInCityWithAPicture
} // end of MOD_Trips
