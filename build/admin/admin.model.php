<?php
/*

Copyright (c) 2007-2009 BeVolunteer

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
     * @author Felix van Hove <fvanhove@gmx.de>
     * @author Fake51
     */

    /**
     * admin model
     *
     * @package Apps
     * @subpackage Admin
     */
class AdminModel extends RoxModelBase
{
    
    public function procActivitylogs($vars, $level = 0)
    {

		$where = '';
		$username = $vars["username"];

		$cid = $this->_idMember($username);
		if ($level <= 1) {
			$cid = $_SESSION["IdMember"]; // Member with level 1 can only see his own rights
		}
		if ($cid != 0) {
			$where .= " AND IdMember=" . $cid;
		}

		$R = MOD_right::get();
		$level = $R->hasRight('Logs');


		$limitcount=$vars["limitcount"]; // Number of records per page
		$start_rec=$vars["start_rec"]; // Number of records per page


		$andS1 = $vars["andS1"];
		if ($andS1 != "") {
			$where .= " AND Str LIKE '%" . $andS1 . "%'";
		}

		$andS2 = $vars["andS2"];
		if ($andS2 != "") {
			$where .= " AND Str LIKE '%" . $andS2 . "%'";
		}

		$notAndS1 = $vars["notAndS1"];
		if ($notAndS1 != "") {
			$where .= " AND Str NOT LIKE '%" . $notAndS1 . "%'";
		}

		$notAndS2 = $vars["notAndS2"];
		if ($notAndS2 != "") {
			$where .= " AND Str NOT LIKE '%" . $notAndS2 . "%'";
		}

		$ip = $vars["ip"];
		if ($ip != "") {
			$where .= " AND IpAddress=" . ip2long($ip) . "";
		}

		$type = $vars["type"];
		if ($type != "") {
			$where .= " AND Type='" . $type . "'";
		}

		// If there is a Scope limit logs to the type in this Scope (unless it his own logs)
		if (!$R->hasRight('Logs', "\"All\"")) {
			$scope = RightScope("Logs");
			str_replace($scope, "\"", "'");
			$where .= " AND (Type IN (" . $scope . ") OR IdMember=" . $_SESSION["IdMember"] . ") ";
		}

		$tData = array ();
		$db = "";
		if (!empty($_SYSHCVOL['ARCH_DB'])) {
		    $db = $_SYSHCVOL['ARCH_DB'] . ".";
		}

		// not using: SQL_CALC_FOUND_ROWS and FOUND_ROWS()
		$query = "SELECT logs.*, Username " .
		        "FROM " . $db . ".logs LEFT JOIN members ON members.id=logs.IdMember " .
		        "WHERE 1=1 " . $where . " " .
		        "ORDER BY created DESC LIMIT $start_rec," . $limitcount;
		$resultRecords = $this->dao->query($query);

		$query = "SELECT COUNT(*) AS n " .
		        "FROM " . $db . ".logs LEFT JOIN members ON members.id=logs.IdMember " .
		        "WHERE 1=1 " . $where;
		$result = $this->dao->query($query);
		$altogether = $result->fetch(PDB::FETCH_OBJ);

		return array($altogether->n => $resultRecords);
    }
}
