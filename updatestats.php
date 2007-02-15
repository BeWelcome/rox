<?php

// Mail bot is a php script used to send automatically the mail
require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";

if (IsLoggedIn()) {
	if (HasRight("Beta") <= 0) {
		echo "This need right <b>Beta</b> for using this alternatively";
		exit (0);
	}
	$IdTriggerer = $_SESSION['IdMember'];
} else { // case not logged
	// todo check if not logged that this script is effectively runned by the cron
	$IdTriggerer = 0; /// todo here need to set the Bot id
	$_SESSION['IdMember'] = 0;
} // not logged

$rr=LoadRow("select count(*) as cnt from members where Status='Active'") ;
$NbActiveMembers=$rr->cnt ;

$rr=LoadRow("select count(*) as cnt from members,comments where Status='Active' and members.id=comments.IdToMember and FIND_IN_SET('ITrusthim',Lenght)") ;
$NbMemberWithOneTrust=$rr->cnt ;

$d1=GetParam("d1",strftime("%Y-%m-%d 00:00:00",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")))) ;
$d2==GetParam("d2",strftime("%Y-%m-%d 00:00:00",mktime(0, 0, 0, date("m")  , date("d"), date("Y")))); 

$str="select count(*) as cnt from ".$_SYSHCVOL['ARCH_DB'].".logs,members where type='Login' and ".$_SYSHCVOL['ARCH_DB'].".logs.created between '$d1' and '$d2' and Str like 'Successful login%' and members.id=".$_SYSHCVOL['ARCH_DB'].".logs.IdMember" ;
echo "str=$str<br>" ;
$rr=LoadRow($str) ;
$NbMemberWhoLoggedToday=$rr->cnt ;

$rr=LoadRow("select count(*) as cnt from messages where DateSent between '$d1' and '$d2' ") ;
$NbMessageSent=$rr->cnt ;

$rr=LoadRow("select count(*) as cnt from messages where WhenFirstRead between '$d1' and '$d2' ") ;
$NbMessageRead=$rr->cnt ;


if (IsLoggedIn()) {
	echo "NbActiveMembers=",$NbActiveMembers,"<br>" ;
	echo "NbMemberWhoLoggedToday=",$NbMemberWhoLoggedToday,"<br>" ;
	echo "NbMessageRead=",$NbMessageRead,"<br>" ;
	echo "NbMemberWithOneTrust=",$NbMemberWithOneTrust,"<br>" ;
	echo "NbActiveMembers=",$NbActiveMembers,"<br>" ;
	echo "NbMessageSent=",$NbMessageSent,"<br>" ;
	echo "stat not updated";
}
else {
	$str="INSERT INTO stats ( id , created , NbActiveMembers , NbMessageSent , NbMessageRead , NbMemberWithOneTrust , NbMemberWhoLoggedToday )VALUES (NULL ,CURRENT_TIMESTAMP , $NbActiveMembers , $NbMessageSent , $NbMessageRead , $NbMemberWithOneTrust , $NbMemberWhoLoggedToday ))" ;
}
?>
