<?php
require_once "../lib/init.php";
require_once "../layout/error.php";

$RightLevel = HasRight('MassMail'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>MassMail</b> rights<br>";
	exit (0);
}


/*
This is the right wich allow to send MassMail to several members using the adminmassmails.php page

It require Level 1 to check the effect of a massmail (without sending it)
It require Level 5 to send it for true

Scope (todo) will allow specific massmails
*/


$cid = GetParam("Username", "");
if ($cid != "") {
	if (!is_numeric($cid)) {
		$rr = LoadRow("select id as cid from members where Username='" . $cid . "'");
		if (isset ($rr->cid))
			$cid = $rr->cid;
		else
			$cid == 0;
	}
	$where .= " and IdMember=" . $cid;
}
if ($RightLevel <= 1)
	$cid = $_SESSION["IdMember"]; // Member with level 1 can only see his own rights

$limit = GetParam("limit", 50);

$andS1 = GetParam("andS1", "");
if ($andS1 != "") {
	$where .= " and Str like='%" . $andS1 . "'%";
}

$andS2 = GetParam("andS2", "");
if ($andS2 != "") {
	$where .= " and Str like='%" . $andS2 . "'%";
}

$NotandS1 = GetParam("NotandS1", "");
if ($NotandS1 != "") {
	$where .= " and Str not like='%" . $NotandS1 . "'%";
}

$NotandS2 = GetParam("NotandS2", "");
if ($NotandS2 != "") {
	$where .= " and Str not like='%" . $NotandS2 . "'%";
}

$ip = GetParam("ip", "");
if ($ip != "") {
	$where .= " and IpAddress=" . ip2long($ip) . "";
}

$type = GetParam("type", "");
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

	case "check" :
	case "send" : 
		break;
}

$TData = array ();

//$str = "select logs.*,Username from BW_ARCH.logs,members where members.id=logs.IdMember " . $where . "  order by created desc limit 0," . $limit;
$str = "select logs.*,Username from ".$_SYSHCVOL['ARCH_DB'].".logs left join members on members.id=logs.IdMember where 1=1 " . $where . "  order by created desc limit 0," . $limit;
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	array_push($TData, $rr);
}

include "../layout/adminmassmails.php";
DisplayAdminMassMails($TData);
?>
