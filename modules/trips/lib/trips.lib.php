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
		//retrieve City for $IdMember
        $query = '
			SELECT SQL_CACHE `members`.`IdCity` 
			FROM 	`members`
			WHERE `members`.`id`= '.$IdMember
		;
    		$s = $this->dao->query($query);
				if (!$s) {
			 		 throw new PException('Cannot retrieve last member with photo!');
				}
		$result = $s->fetch(PDB::FETCH_OBJ);
		
		$TTrips=array() ;

        // retrieve the visiting members handle and trip data
        $cityId = $result->IdCity;
        $query = "
            SELECT SQL_CACHE
                bd.blog_id           AS tripId,
                bd.blog_start        AS tripDate,
                members.Username,
                members.IdCity,
                geonames_cache.name  AS city,
                geonames_cache2.name AS country
            FROM
                blog            AS b,
                blog_data       AS bd,
                members,
                geonames_cache,
                geonames_cache  AS geonames_cache2
            WHERE
                b.blog_id                 = bd.blog_id 
                AND
                b.trip_id_foreign         IS NOT NULL
                AND
                bd.blog_geonameid         = $cityId
                AND
                b.IdMember                = members.id
                AND
                geonames_cache.geonameId  = members.IdCity
                AND
                geonames_cache2.geonameId = geonames_cache.parentCountryId
                AND
                bd.blog_start             >= CURDATE()
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
	  					array_push($TTrips, $row);
				} // end of while on visits
				return($TTrips) ;				
			
		} // end of	RetrieveLastAcceptedProfileInCityWithAPicture		
} // end of MOD_Trips
