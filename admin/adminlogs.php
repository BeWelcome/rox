<?php
require_once "../lib/init.php";
require_once "../layout/error.php";
require_once "../layout/adminlogs.php";

$RightLevel = HasRight('Logs'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>Logs</b> rights<br>";
	exit (0);
}

$cid = IdMember(GetParam("Username", "0"));
if ($cid != 0) {
	$where .= " and IdMember=" . $cid;
}

if ($RightLevel <= 1)
	$cid = $_SESSION["IdMember"]; // Member with level 1 can only see his own rights

$limitcount=GetParam("limitcount",100); // Number of records per page
$start_rec=GetParam("start_rec",0); // Number of records per page


$andS1 = GetStrParam("andS1", "");
if ($andS1 != "") {
	$where .= " and Str like '%" . $andS1 . "%'";
}

$andS2 = GetStrParam("andS2", "");
if ($andS2 != "") {
	$where .= " and Str like '%" . $andS2 . "%'";
}

$NotandS1 = GetStrParam("NotandS1", "");
if ($NotandS1 != "") {
	$where .= " and Str not like '%" . $NotandS1 . "%'";
}

$NotandS2 = GetStrParam("NotandS2", "");
if ($NotandS2 != "") {
	$where .= " and Str not like '%" . $NotandS2 . "%'";
}

$ip = GetStrParam("ip", "");
if ($ip != "") {
	$where .= " and IpAddress=" . ip2long($ip) . "";
}

$type = GetStrParam("type", "");
if ($type != "") {
	$where .= " and Type='" . $type . "'";
}

// If there is a Scope limit logs to the type in this Scope (unless it his own logs)
if (!HasRight('Logs', "\"All\"")) {
	$scope = RightScope("Logs");
	str_replace($scope, "\"", "'");
	$where .= " and (Type in (" . $scope . ")or IdMember=" . $_SESSION["IdMember"] . ") ";
}

switch (GetParam("action")) {

	case "del" : // case a delete is requested
		break;
}

$TData = array ();

$str = "select SQL_CALC_FOUND_ROWS logs.*,Username from ".$_SYSHCVOL['ARCH_DB'].".logs left join members on members.id=logs.IdMember where 1=1 " . $where . "  order by created desc limit $start_rec,".$limitcount;
$qry = sql_query($str);
$rCount=LoadRow("SELECT FOUND_ROWS() as cnt") ;
while ($rr = mysql_fetch_object($qry)) {
	array_push($TData, $rr);
}

DisplayAdminLogs($TData,$rcount->cnt);
?>
