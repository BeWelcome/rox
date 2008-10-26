<?php
/**
 * Gallery model
 * 
 * @package gallery
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class OnlineModel extends PAppModel {
    public function __construct() {
        parent::__construct();
    }

    public function GetMembers() {
	 	global $_SYSHCVOL ;
		// TODO : I am not sure it is useful to look in membersphotos table here 
       if ($User = APP_User::login("Pending,NeedMore")) { // if the use is logged we will not reduce to only public profile 
		 	$query = "select now()-online.updated as NbSec ,members.*,cities.Name as cityname,members.Status as MemberStatus,cities.IdRegion as IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment,online.updated as lastdateaction,lastactivity from cities,countries,online,members left join membersphotos on membersphotos.IdMember=members.id where countries.id=cities.IdCountry and cities.id=members.IdCity and (members.Status='Active' or members.Status='Pending' or members.Status='NeedMore') and online.IdMember=members.id and online.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) GROUP BY members.id order by members.LastLogin desc";
		} 
		else { // User is not log search only public profile
		   	$query = "select members.*,cities.Name as cityname,cities.IdRegion as IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment,online.updated as lastdateaction,lastactivity from cities,countries,online,memberspublicprofiles,members left join membersphotos on membersphotos.IdMember=members.id where countries.id=cities.IdCountry and cities.id=members.IdCity and (members.Status='Active' or members.Status='Pending' or members.Status='NeedMore') and online.IdMember=members.id and online.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) and online.IdMember=members.id and memberspublicprofiles.IdMember=members.id GROUP BY members.id order by members.LastLogin desc";
		}
       $s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Failed to get online members!');
		}
 		$TMembers=array() ;
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$TMembers[] = $row;
		}
		return ($TMembers) ;
		 
	 } // end of GetMembers
	 
	 // The GetGuests unction will return an array with guest online
	 // Not a only people with righ ShowAdctivity or Admin will be able to see them
    public function GetGuests() {
	 	global $_SYSHCVOL ; 
        $Rights = MOD_right::get();
	 	$TGuest=array() ;
		// Case of members who can see additional information about members last activity
		if ($Rights->hasRight("Debug","ShowLastActivity")) {
			$query = "select appearance,lastactivity,now()-updated as NbSec from guestsonline where guestsonline.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) order by guestsonline.updated  desc";
       	$s = $this->dao->query($query);
			if (!$s) {
				throw new PException('Failed to get online guests!');
			}
			while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				array_push($TGuest, $row);
			}
		}
	 	return($TGuest) ;
	} // end of GetGuests

	 
	 // The GetTotMembers function will return the total number of members who can potentially fully use BW
    public function GetTotMembers() {
			$query = "select SQL_CACHE count(*) as cnt from members where (Status='Active' or Status='InActive')";
       	$s = $this->dao->query($query);
			if (!$s) {
				throw new PException('Failed to get Tot Members!');
			}
			$row = $s->fetch(PDB::FETCH_OBJ) ;
			return ($row->cnt) ;
	 } // end of GetTotMembers

}
?>
