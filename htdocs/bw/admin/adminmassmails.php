<?php
require_once "../lib/init.php";
require_once "../layout/error.php";
require_once "../layout/adminmassmails.php";

$RightLevel = HasRight('MassMail'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>MassMail</b> rights<br>";
	exit (0);
}


$IdBroadCast=GetParam("IdBroadCast",0) ;
$subject=GetParam("subject",0) ;
$body=GetParam("body",0) ;
$greetings=GetParam("greetings",0) ;
/*
This is the right wich allow to send MassMail to several members using the adminmassmails.php page

It require Level 1 to check the effect of a massmail (without sending it)
It require Level 5 to send it for true

Scope (todo) will allow specific massmails
*/




switch (GetParam("action")) {

	case "find" :
		 echo "Find" ;
		 break ;

	case "update" :
		 echo "Update" ;
		 break ;

	case "create" :
		 echo "Create" ;
		 break ;


	case "check" :
	case "send" : 
		break;
}

$TData = array ();

//$str = "select logs.*,Username from BW_ARCH.logs,members where members.id=logs.IdMember " . $where . "  order by created desc limit 0," . $limit;
$str = "select logs.*,Username from ".$_SYSHCVOL['ARCH_DB'].".logs left join members on members.id=logs.IdMember where 1=1 " . $where . "  order by created desc limit 0," . $limit;
if (!empty($IdBroadCast)) {
 	$rBroadCast=LoadRow("select * from broadcast where id=".$IdBroadCast) ; 
}

DisplayAdminMassMails($TData);
?>
