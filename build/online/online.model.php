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
class OnlineModel extends RoxModelBase {
    public function __construct() {
        parent::__construct();
    }

    public function GetMembers() {
        // TODO: Replace by config values
        global $_SYSHCVOL;
        $delay = $_SYSHCVOL['WhoIsOnlineDelayInMinutes'];

        // Test if member is logged in
        if ($User = APP_User::login("Pending,NeedMore")) {
            // All profiles
            // TODO: JY: I am not sure it is useful to look in membersphotos table here
            $query = "
                SELECT
                    NOW() - online.updated      AS NbSec,
                    members.*,
                    geonames_cache.name         AS cityname,
                    geonames_cache.parentAdm1Id AS IdRegion,
                    geonames_countries.name     AS countryname,
                    members.Status              AS MemberStatus,
                    membersphotos.FilePath      AS photo,
                    membersphotos.Comment,
                    online.updated              AS lastdateaction,
                    lastactivity
                FROM
                    geonames_cache,
                    geonames_countries,
                    online,
                    members
                        LEFT JOIN
                            membersphotos
                        ON
                            membersphotos.IdMember = members.id
                WHERE
                    geonames_countries.iso_alpha2 = geonames_cache.fk_countrycode
                    AND
                    geonames_cache.geonameid = members.IdCity
                    AND
                        members.Status IN ( " . Member::ACTIVE_ALL . ")
                    AND
                    online.IdMember = members.id
                    AND
                    online.updated > DATE_SUB(NOW(), INTERVAL $delay MINUTE)
                GROUP BY
                    members.id
                ORDER BY
                    members.LastLogin DESC
                ";
        } else {
            // Public profiles only
            // TODO: combine with query above
            $query = "
                SELECT
                    NOW() - online.updated      AS NbSec,
                    members.*,
                    geonames_cache.name         AS cityname,
                    geonames_cache.parentAdm1Id AS IdRegion,
                    geonames_countries.name     AS countryname,
                    members.Status              AS MemberStatus,
                    membersphotos.FilePath      AS photo,
                    membersphotos.Comment,
                    online.updated              AS lastdateaction,
                    lastactivity
                FROM
                    geonames_cache,
                    geonames_countries,
                    online,
                    memberspublicprofiles,
                    members
                        LEFT JOIN
                            membersphotos
                        ON
                            membersphotos.IdMember = members.id
                WHERE
                    geonames_countries.iso_alpha2 = geonames_cache.fk_countrycode
                    AND
                    geonames_cache.geonameid = members.IdCity
                    AND
                        members.Status IN ( " . Member::ACTIVE_ALL . ")
                    AND
                    online.IdMember = members.id
                    AND
                    online.updated > DATE_SUB(NOW(), INTERVAL $delay MINUTE)
                    AND
                    online.IdMember = members.id
                    AND
                    memberspublicprofiles.IdMember = members.id
                GROUP BY
                    members.id
                ORDER BY
                    members.LastLogin DESC
                ";
        }
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Failed to get online members!');
        }
        $TMembers = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $TMembers[] = $row;
        }
        return $TMembers;
    }

	 // The GetGuests function will return an array with guest online
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
		$query = "select SQL_CACHE count(*) as cnt from members where Status IN (" . Member::ACTIVE_ALL . ")";
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Failed to get Tot Members!');
		}
		$row = $s->fetch(PDB::FETCH_OBJ) ;
		return ($row->cnt) ;
	 } // end of GetTotMembers

}
?>
