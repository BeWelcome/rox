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
 * @author JeanYves 
 */
class MOD_visits {
    

    /**
     * Singleton instance
     * 
     * @var MOD_visits
     * @access public
     */
    public static $_instance;
    
    private function __construct()
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
    
    /**
     * Retrieve the last visits on a profile. 
		 * only last visits of members with a picture are retrieved
     * 
     * @param int $IdMember the the member we want to find the visit 
     * @quantity int number of last visits to fetch 
     * 				 method to be called
     */
    public function BuildLastVisits($pIdMember = 0, $quantiy = 3)
    {
        $TVisits=array() ;
        
        $idMember = $pIdMember;
				if ($pIdMember==0) { // if no pIdMember specified then trt with current member
        	 if (isset($_SESSION['IdMember'])) {
            	$idMember = $_SESSION['IdMember'];
        	 }
				}

				if ($idMember==0) return($TVisits) ; // Return empty array if no valid id member
        

        $query = '
SELECT `profilesvisits`.`updated` as datevisite,`members`.`Username`,`members`.`ProfileSummary`,`cities`.`Name` as cityname,`regions`.`Name` as regionname,`countries`.`Name` as countryname,`membersphotos`.`FilePath` as photo,`membersphotos`.`Comment`
FROM 	`cities`,`countries`,`regions`,`profilesvisits`,`members` left join `membersphotos` on `membersphotos`.`IdMember`=`members`.`id` and `membersphotos`.`SortOrder`=0 
WHERE `cities`.`IdRegion`=`regions`.`id` and `countries`.`id`=`cities`.`IdCountry` and `cities`.`id`=`members`.`IdCity` and `status`=\'Active\' and `members`.`id`=`profilesvisits`.`IdVisitor` and `profilesvisits`.`IdMember`=' . $idMember . ' and `members`.`Status`=\'Active\' 
ORDER BY `profilesvisits`.`updated` desc limit '.$quantity; 
;
    		$s = $this->dao->query($query);
				if (!$s) {
			 		 throw new PException('Cannot retrieve last visits!');
				}

				while ($row = $s->fetch(PDB::FETCH_OBJ)) {
/*
	  					if ($rr->Comment > 0) {
					  		 $rr->phototext = FindTrad($rr->Comment);
	  					} else {
					  		$rr->phototext = "no comment";
     					}
	  					if ($rr->ProfileSummary > 0) {
					  		 $rr->ProfileSummary = FindTrad($rr->ProfileSummary);
	  					} else {
					  		$rr->ProfileSummary = "";
	  				  }
*/
	  					array_push($TVisits, $row);
				} // end of while on visits
				return($TVisits) ;
		} // end of	BuildLastVisits
} // end of MOD_Visits
?>