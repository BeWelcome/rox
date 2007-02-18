<?php
require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";
include "layout/mycontacts.php";
require_once "prepare_profile_header.php";

MustLogIn() ; // member must login

$IdContact = GetParam("IdContact", 0); // find the concerned member 
$Message = GetParam("Message", ""); // find the Message
$Title = GetParam("Title", ""); // find the Message
$iMes = GetParam("iMes", 0); // find Message number 
$IdMember = $_SESSION["IdMember"];

$m = prepare_profile_header(IdMember($IdContact),"",0) ; 


$str="select Category from mycontacts where mycontacts.IdMember=".$IdMember." group by Category" ;
$qry=sql_query($str) ;
$TContactCategory=array() ;
while ($rr = mysql_fetch_object($qry)) {
	array_push($TContactCategory, $rr);
}

switch (GetParam("action")) {

	case "" : // list all contacts for member $IdMember
		$TData=Array() ;
		$str="select SQL_CACHE mycontacts.*,Username,ProfileSummary,IdCity from mycontacts,members where mycontacts.IdMember=".$IdMember." and members.id=mycontacts.IdContact" ;
		$qry=sql_query($str) ;
		while ($rr = mysql_fetch_object($qry)) {
			$rr->ProfileSummary=FindTrad($rr->ProfileSummary) ;
   		$photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdContact . " and SortOrder=0");
			if (isset($photo->FilePath)) $rr->photo=$photo->FilePath ; 
			$where=LoadRow("select cities.Name as CityName,countries.id as IdCountry,regions.id as IdRegion,cities.id as IdCity,countries.Name as CountryName,regions.Name as RegionName from countries,regions,cities where cities.id=$rr->IdCity and regions.IdCountry=countries.id and regions.id=cities.IdRegion") ;
			$rr->CountryName=$where->CountryName ; 
			$rr->CityName=$where->CityName ; 
			$rr->RegionName=$where->RegionName ; 
			$rr->IdRegion=$where->IdRegion ; 
			$rr->IdCountry=$where->IdCountry ; 
			array_push($TData, $rr);
		}

		DisplayMyContactList($m,$IdMember,$TData) ;
		exit(0) ;
		break ;

	case "add" : // Add a contact
		DisplayOneMyContact($m,IdMember(Getparam("IdContact")),"",$TContactCategory) ;
		exit(0) ;
		break ;
	
	case "view" : // Add a contact
		$TData=LoadRow("select * from mycontacts where mycontacts.IdContact=".IdMember(Getparam("IdContact"))." and IdMember=".$_SESSION["IdMember"]) ;
		DisplayOneMyContact($m,IdMember(Getparam("IdContact")),$TData,$TContactCategory) ;
		exit(0) ;
		break ;
	
	case "doadd" : // Add a contact
		$str="insert into mycontacts(IdMember,IdContact,Category,Comment,created) values(".$_SESSION["IdMember"].",".IdMember(GetParam("IdContact")).",'".GetParam("Category")."','".GetParam("Comment")."',now())" ;
		sql_query($str) ;
		LogStr("Adding contact for ".fUsername(IdMember(GetParam("IdContact"))),"MyContacts") ;
		$TData=LoadRow("select * from mycontacts where IdContact=".IdMember(Getparam("IdContact"))." and IdMember=".$_SESSION["IdMember"]) ;
		DisplayOneMyContact($m,IdMember(Getparam("IdContact")),$TData,$TContactCategory) ;
		exit(0) ;
		break ;
	
	case "doupdate" : // Update a contact
		$str="update mycontacts set Comment='".GetParam("Comment")."',Category='".GetParam("Category")."' where IdMember=".$_SESSION["IdMember"]." and IdContact=".IdMember(GetParam("IdContact")) ;
		sql_query($str) ;
		LogStr("Updating contact for ".fUsername(IdMember(GetParam("IdContact"))),"MyContacts") ;
		$TData=LoadRow("select * from mycontacts where IdContact=".IdMember(Getparam("IdContact"))." and IdMember=".$_SESSION["IdMember"]) ;
		DisplayOneMyContact($m,IdMember(Getparam("IdContact")),$TData,$TContactCategory) ;
		exit(0) ;
		break ;
	
	case "dodel" : // delete a contact
		$str="delete from  mycontacts  where IdMember=".$_SESSION["IdMember"]." and IdContact=".IdMember(GetParam("IdContact")) ;
		sql_query($str) ;
		LogStr("Deleting contact for ".fUsername(IdMember(GetParam("IdContact"))),"MyContacts") ;
		break ;
}

DisplayContactGroup($IdGroup,"","", "",GetParam("JoinMemberPict"));
?>
