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
require_once "../lib/init.php";
require_once "../layout/error.php";
require_once "../layout/adminlogs.php";

$RightLevel = HasRight('Logs'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>Logs</b> rights<br />";
	exit (0);
}

$cid = IdMember(GetParam("Username", "0"));
if ($cid != 0) {
	$where .= " AND IdMember=" . $cid;
}

if ($RightLevel <= 1)
	$cid = $_SESSION["IdMember"]; // Member with level 1 can only see his own rights

$limitcount=GetParam("limitcount",100); // Number of records per page
$start_rec=GetParam("start_rec",0); // Number of records per page


$andS1 = GetStrParam("andS1", "");
if ($andS1 != "") {
	$where .= " AND Str LIKE '%" . $andS1 . "%'";
}

$andS2 = GetStrParam("andS2", "");
if ($andS2 != "") {
	$where .= " AND Str LIKE '%" . $andS2 . "%'";
}

$NotandS1 = GetStrParam("NotandS1", "");
if ($NotandS1 != "") {
	$where .= " AND Str NOT LIKE '%" . $NotandS1 . "%'";
}

$NotandS2 = GetStrParam("NotandS2", "");
if ($NotandS2 != "") {
	$where .= " AND Str NOT LIKE '%" . $NotandS2 . "%'";
}

$ip = GetStrParam("ip", "");
if ($ip != "") {
	$where .= " AND IpAddress=" . ip2long($ip) . "";
}

$type = GetStrParam("type", "");
if ($type != "") {
	$where .= " AND Type='" . $type . "'";
}

// If there is a Scope limit logs to the type in this Scope (unless it his own logs)
if (!HasRight('Logs', "\"All\"")) {
	$scope = RightScope("Logs");
	str_replace($scope, "\"", "'");
	$where .= " AND (Type IN (" . $scope . ") OR IdMember=" . $_SESSION["IdMember"] . ") ";
}

switch (GetParam("action")) {

	case "del" : // case a delete is requested
		break;
}

$TData = array ();

$str = "SELECT SQL_CALC_FOUND_ROWS logs.*,Username FROM ".$_SYSHCVOL['ARCH_DB'].".logs LEFT JOIN members ON members.id=logs.IdMember WHERE 1=1 " . $where . "  ORDER BY created DESC LIMIT $start_rec,".$limitcount;
$qry = sql_query($str);
$rCount=LoadRow("SELECT FOUND_ROWS() AS cnt") ;
while ($rr = mysql_fetch_object($qry)) {
	array_push($TData, $rr);
}

DisplayAdminLogs($TData,$rcount->cnt);
?>
