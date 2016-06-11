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
chdir("..") ;
require_once "lib/init.php";
require_once "layout/error.php";
require_once "layout/adminlogs.php";

$RightLevel = HasRight('Logs'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>Logs</b> rights<br />";
	exit (0);
}

$where = '';
$username = GetParam("Username", "0");
$cid = IdMember($username);
if ($cid != 0) {
	$where .= " AND IdMember=" . $cid;
}

if (HasRight('Logs','OwnLogsRestriction') and !(HasRight('Logs','"All"'))) {
	$cid = $this->_session->get("IdMember"); // Member with scope OwnLogsRestriction can only see his own rights
	$username=fUsername($cid) ;
}

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

$notAndS1 = GetStrParam("NotandS1", "");
if ($notAndS1 != "") {
	$where .= " AND Str NOT LIKE '%" . $notAndS1 . "%'";
}

$notAndS2 = GetStrParam("NotandS2", "");
if ($notAndS2 != "") {
	$where .= " AND Str NOT LIKE '%" . $notAndS2 . "%'";
}

$ip = GetStrParam("ip", "");
if ($ip != "") {
	$where .= " AND IpAddress=" . ip2long($ip) . "";
}

$type = GetStrParam("Type", "");
if ($type != "") {
	$where .= " AND Type='" . $type . "'";
}

// If there is a Scope limit logs to the type in this Scope (unless it his own logs)
if (!HasRight('Logs', "\"All\"")) {
	$scope = RightScope("Logs");
	str_replace($scope, "\"", "'");
	$where .= " AND (Type IN (" . $scope . ") OR IdMember=" . $this->_session->get("IdMember") . ") ";
}

switch (GetParam("action")) {

	case "del" : // case a delete is requested
		break;
}

$tData = array ();

if (empty($where) and $start_rec==0) { // In this case we will avoid the FOUND_ROW which is a performance killer
	$str = "SELECT logs.*,Username " .
        "FROM " .$_SYSHCVOL['ARCH_DB'] . ".logs LEFT JOIN members ON members.id=logs.IdMember " . 
        "ORDER BY " .$_SYSHCVOL['ARCH_DB'] . ".logs.id DESC LIMIT $start_rec,".$limitcount;
	$qry = sql_query($str);
	$rCount=LoadRow("SELECT count(*)  AS cnt from " .$_SYSHCVOL['ARCH_DB'] . ".logs") ;
}
else {
	$str = "SELECT SQL_CALC_FOUND_ROWS logs.*,Username " .
        "FROM " .$_SYSHCVOL['ARCH_DB'] . ".logs LEFT JOIN members ON members.id=logs.IdMember " . 
        "WHERE 1=1 " . $where . " " .
        "ORDER BY " .$_SYSHCVOL['ARCH_DB'] . ".logs.id DESC LIMIT $start_rec,".$limitcount;
	$qry = sql_query($str);
	$rCount=LoadRow("SELECT FOUND_ROWS() AS cnt") ;
}
while ($rr = mysql_fetch_object($qry)) {
	array_push($tData, $rr);
}

if ($username!="0") { // Usage of adminlog is logged
	 LogStr("Is using adminlog on profile <b>".$username."</b>","adminlog") ;
}
DisplayAdminLogs($tData, $username, $type, $ip, $andS1, $andS2, $notAndS1, $notAndS2, $rCount->cnt);
?>
