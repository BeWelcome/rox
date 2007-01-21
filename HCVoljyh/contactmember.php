<?php
include "lib/dbaccess.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";
include "layout/contactmember.php";

$IdMember = IdMember(GetParam("cid", 0)); // find the concerned member 
$Message = GetParam("Message", ""); // find the Message
$iMes = GetParam("iMes", 0); // find Message number 
$IdSender = $_SESSION["IdMember"];
$photorank = 0; // Alway use picture 0 on contact member 

$m = LoadRow("select * from members where id=" . $IdMember . " and Status='Active'");
if (!isset ($m->id)) {
	$errcode = "ErrorNoSuchMember";
	DisplayError(ww($errcode, $IdMember));
	//		die("ErrorMessage=".$ErrorMessage) ;
	exit (0);
}

// Load photo data
$photo = "";
$phototext = "";
$str = "select * from membersphotos where IdMember=" . $IdMember . " and SortOrder=" . $photorank;
$rr = LoadRow($str);
if (!isset ($rr->FilePath) and ($photorank > 0)) {
	$rr = LoadRow("select * from membersphotos where IdMember=" . $IdMember . " and SortOrder=0");
}
if (isset ($rr->FilePath)) {
	$photo = $rr->FilePath;
	$phototext = FindTrad($rr->Comment);
	$photorank = $rr->SortOrder;
}
$m->photo = $photo;
$m->photorank = $photorank;
$m->phototext = $phototext;

// Load geography
if ($m->IdCity > 0) {
	$rWhere = LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=" . $m->IdCity);
	$m->cityname = $rWhere->cityname;
	$m->regionname = $rWhere->regionname;
	$m->countryname = $rWhere->countryname;
}

// Load nbcomments nbtrust
$m->NbTrust = 0;
$m->NbComment = 0;
$rr = LoadRow("select count(*) as cnt from comments where IdToMember=" . $m->id . " and Quality='Good'");
if (isset ($rr->cnt))
	$m->NbTrust = $rr->cnt;
$rr = LoadRow("select count(*) as cnt from comments where IdToMember=" . $m->id);
if (isset ($rr->cnt))
	$m->NbComment = $rr->cnt;

if ($m->LastLogin == "11/30/99 00:00:00")
	$m->LastLogin = ww("NeverLog");
else
	$m->LastLogin = localdate($m->LastLogin);

// Load Age
$m->age = fage($m->BirthDate, $m->HideBirthDate);

// Load full name
$m->FullName = fFullName($m);

$JoinMemberPictRes="no" ;
if (GetParam("JoinMemberPict")=="on") {
  $JoinMemberPictRes="yes" ;
}

switch (GetParam("action")) {

	case "edit" :
		$rm=LoadRow("select * from messages where id=".GetParam("iMes")." and Status='Draft'") ;
		$iMes=$rm->id ;
		$Message=$rm->Message ;
		$Warning="" ;
		$m=LoadRow("select * from members where id=".$rm->IdReceiver) ; 
	
		DisplayContactMember($m, $Message, $iMes, $Warning,GetParam("JoinMemberPict"));
		exit(0) ;
	case "sendmessage" :
		if (GetParam("IamAwareOfSpamCheckingRules") != "on") { // check if has accepted the vondition of sending
			$Warning = ww("MustAcceptConditionForSending");
			DisplayContactMember($m, $Message, $iMes, $Warning,GetParam("JoinMemberPict"));
			exit(0) ;
		}
		$Status = "ToSend"; // todo compute a real status
		
		if ($iMes != 0) {
			$str = "update messages set Messages='" . addslashes($Message) . "',IdReceiver=" . $IdMember . ",IdSender=" . $IdSender . "InFolder='Normal',Status='" . $Status . "',JoinMemberPict='".$JoinMemberPictRes."' where id=".$iMes;
			sql_query($str);
		} else {
			$str = "insert into messages(created,Message,IdReceiver,IdSender,Status,InFolder,JoinMemberPict) values(now(),'" . addslashes($Message) . "'," . $IdMember . "," . $IdSender.",'".$Status."','Normal','".$JoinMemberPictRes."') ";
			sql_query($str);
			$iMes = mysql_insert_id();
		}
		
		$result = ww("YourMessageWillBeProcessed", $iMes);
		DisplayResult($m, $Message, $result);
		exit (0);
	case ww("SaveAsDraft") :
		if ($iMes != 0) {
			$str = "update messages set Messages='" . addslashes($Message) . "',IdReceiver=" . $IdMember . ",IdSender=" . $IdSender . "InFolder='Draft',Status='Draft'";
			sql_query($str);
		} else {
			$str = "insert into messages(created,Message,IdReceiver,IdSender,Status,InFolder) values(now(),'" . addslashes($Message) . "'," . $IdMember . "," . $IdSender . ",'Draft','Draft') ";
			sql_query($str);
			$iMes = mysql_insert_id();
		}
		$result = ww("YourMessageIsSavedAsDraft", $iMes);
		DisplayResult($m, $Message, $result);
		exit (0);

}

DisplayContactMember($m, $Message, $iMes, "",GetParam("JoinMemberPict"));
?>
