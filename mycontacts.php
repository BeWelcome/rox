<?php
require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";
require_once "layout/mycontacts.php";
require_once "lib/prepare_profile_header.php";

MustLogIn(); // member must login

function LoadTCategory($IdMember) {
	$str="select Category from mycontacts where IdMember=".$IdMember." group by Category";
	$qry=sql_query($str);
	$TContactCategory=array();
	while ($rr = mysql_fetch_object($qry)) {
		array_push($TContactCategory, $rr);
	}
	return ($TContactCategory);
} // end of LoadTCategory

function ShowWholeList($IdMember) {

	$TData=Array();
	$str="select SQL_CACHE mycontacts.*,Username,ProfileSummary,IdCity from mycontacts,members where mycontacts.IdMember=".$IdMember." and members.id=mycontacts.IdContact order by Category";
	$qry=sql_query($str);
	while ($rr = mysql_fetch_object($qry)) {
	    $photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdContact . " and SortOrder=0");
		if (isset($photo->FilePath)) $rr->photo=$photo->FilePath; 
		$where=LoadRow("select cities.Name as CityName,countries.id as IdCountry,regions.id as IdRegion,cities.id as IdCity,countries.Name as CountryName,regions.Name as RegionName from countries,regions,cities where cities.id=$rr->IdCity and cities.IdCountry=countries.id and regions.id=cities.IdRegion");
		$rr->CountryName=$where->CountryName; 
		$rr->CityName=$where->CityName; 
		$rr->RegionName=$where->RegionName; 
		$rr->IdRegion=$where->IdRegion; 
		$rr->IdCountry=$where->IdCountry; 
	    array_push($TData, $rr);
	}

	DisplayMyContactList($IdMember,$TData);
} // end of ShowWholeList


$IdMember = $_SESSION["IdMember"];
$IdContact = GetParam("IdContact", 0); // find the concerned member 
$iCategory=GetParam("iCategory",0);

if (GetParam("action","")=="") {
	ShowWholeList($IdMember);
	exit(0);
}

$m = prepareProfileHeader(IdMember($IdContact),"",0); // This is the profile of the contact which is going to be used


$TContactCategory=LoadTCategory($IdMember);

switch (GetParam("action")) {

	case "add" : // Add a contact
		DisplayOneMyContact($m,IdMember(Getparam("IdContact")),"",$TContactCategory);
		exit(0);
		break;
	
	case "view" : // view or update
	case "update" : // view or update
		$TData=LoadRow("select * from mycontacts where mycontacts.IdContact=".IdMember(Getparam("IdContact"))." and IdMember=".$_SESSION["IdMember"]);
		DisplayOneMyContact($m,IdMember(Getparam("IdContact")),$TData,$TContactCategory);
		exit(0);
		break;
	
	case "doadd" : // Add a contact
		$category=GetParam("Category"); // Find the category, first the text field, ther try dropdown if any 
		if (($category=="") and ($iCategory>0)) $category=$TContactCategory[$iCategory]->Category;
		
		$str="insert into mycontacts(IdMember,IdContact,Category,Comment,created) values(".$_SESSION["IdMember"].",".IdMember(GetParam("IdContact")).",'".stripslashes($category)."','".GetParam("Comment")."',now())";
		sql_query($str);
		LogStr("Adding contact for ".fUsername(IdMember(GetParam("IdContact"))),"MyContacts");
		$TData=LoadRow("select * from mycontacts where IdContact=".IdMember(Getparam("IdContact"))." and IdMember=".$_SESSION["IdMember"]);
		$TContactCategory=LoadTCategory($IdMember); // in case a category was updated
		DisplayOneMyContact($m,IdMember(Getparam("IdContact")),$TData,$TContactCategory);
		exit(0);
		break;
	
	case "doupdate" : // Update a contact
		$category=GetParam("Category"); // Find the category, first the text field, ther try dropdown if any 
		if (($category=="") and ($iCategory>0)) $category=$TContactCategory[$iCategory]->Category;
		$str="update mycontacts set Comment='".GetParam("Comment")."',Category='".stripslashes($category)."' where IdMember=".$_SESSION["IdMember"]." and IdContact=".IdMember(GetParam("IdContact"));
		sql_query($str);
		LogStr("Updating contact for ".fUsername(IdMember(GetParam("IdContact"))),"MyContacts");
		$TData=LoadRow("select * from mycontacts where IdContact=".IdMember(Getparam("IdContact"))." and IdMember=".$_SESSION["IdMember"]);
		$TContactCategory=LoadTCategory($IdMember); // in case a category was updated
		DisplayOneMyContact($m,IdMember(Getparam("IdContact")),$TData,$TContactCategory);
		exit(0);
		break;
	
	case "delete" : // delete a contact
		$str="delete from  mycontacts  where IdMember=".$_SESSION["IdMember"]." and IdContact=".IdMember(GetParam("IdContact"));
		sql_query($str);
		LogStr("Deleting contact for ".fUsername(IdMember(GetParam("IdContact"))),"MyContacts");
		break;
}


ShowWholeList($IdMember);

?>
