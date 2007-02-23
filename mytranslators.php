<?php
require_once "lib/init.php";
require_once "layout/error.php";
require_once "prepare_profile_header.php";
include "layout/mytranslators.php";

MustLogIn() ;

// Find parameters
$IdMember = $_SESSION['IdMember'];
if (IsAdmin()) { // admin can alter other profiles
	$IdMember = GetParam("cid", $_SESSION['IdMember']);
}

$m = prepare_profile_header($IdMember,"",0) ; // This is the profile of the contact which is going to be used

switch (GetParam("action")) {
	case "del" :
		$str="delete from intermembertranslations where IdTranslator=".GetParam("IdTranslator")." and IdMember=".$IdMember ;
		sql_query($str) ;
		LogStr("Removing translator <b>".fUserName(GetParam("IdTranslator"))."</b>","mytranslators") ;
		break;
	case "add" : // todo
		$IdTranslator=IdMember(GetParam("Username"),0) ;
		$IdLanguage=Getparam("IdLanguage") ;
		$rr=LoadRow("select id from intermembertranslations where IdTranslator=".$IdTranslator." and IdMember=".$IdMember." and IdLanguage=".$IdLanguage) ;
		if (!isset($rr->id) and ($IdTranslator!=0)) { // if not allready exists
		   $str="insert into intermembertranslations(IdTranslator,IdMember,IdLanguage) values(".$IdTranslator.",".$IdMember.",".$IdLanguage.")" ;
		   sql_query($str) ;
		   LogStr("Adding translator <b>".fUserName(GetParam("IdTranslator"))."</b> for language","mytranslators") ;
		}
		break;
}

$TData = array ();
$str = "select intermembertranslations.*,members.Username,members.ProfileSummary,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment";
$str .= " from intermembertranslations,cities,countries,regions,recentvisits,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' and members.id=intermembertranslations.IdTranslator and intermembertranslations.IdMember=" . $IdMember . " and members.status='Active' GROUP BY members.id order by intermembertranslations.updated desc";
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	if ($rr->ProfileSummary > 0) {
		$rr->ProfileSummary = FindTrad($rr->ProfileSummary);
	} else {
		$rr->ProfileSummary = "";
	}
	array_push($TData, $rr);
}

// Load the language the member does'nt know
$m->TLanguages = array ();
$str = "select languages.Name as Name,languages.id as id from languages order by Name";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	array_push($m->TLanguages, $rr);
}
DisplayMyTranslators($TData,$m);
?>
