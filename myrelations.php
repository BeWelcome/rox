<?php
require_once "lib/init.php";
require_once "layout/error.php";
include "layout/myrelations.php";
require_once "prepare_profile_header.php";

MustLogIn() ; // member must login

function LoadTCategory($IdMember) {
	$str="select Category from specialrelations where IdMember=".$IdMember." group by Category" ;
	$qry=sql_query($str) ;
	$TRelationsCategory=array() ;
	while ($rr = mysql_fetch_object($qry)) {
		array_push($TRelationsCategory, $rr);
	}
	return ($TRelationsCategory) ;
} // end of LoadTCategory

function ShowWholeList($IdMember) {

	$TData=Array() ;
	$str="select SQL_CACHE specialrelations.*,Username,ProfileSummary,IdCity from specialrelations,members where specialrelations.IdOwner=".$IdMember." and members.id=specialrelations.IdRelation order by created" ;
	$qry=sql_query($str) ;
	while ($rr = mysql_fetch_object($qry)) {
	    $photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdRelation . " and SortOrder=0");
		if (isset($photo->FilePath)) $rr->photo=$photo->FilePath ; 
		$where=LoadRow("select cities.Name as CityName,countries.id as IdCountry,regions.id as IdRegion,cities.id as IdCity,countries.Name as CountryName,regions.Name as RegionName from countries,regions,cities where cities.id=$rr->IdCity and regions.IdCountry=countries.id and regions.id=cities.IdRegion") ;
		$rr->CountryName=$where->CountryName ; 
		$rr->CityName=$where->CityName ; 
		$rr->RegionName=$where->RegionName ; 
		$rr->IdRegion=$where->IdRegion ; 
		$rr->IdCountry=$where->IdCountry ; 
	    array_push($TData, $rr);
	}

	DisplayOneRelation($IdMember,$TData) ;
} // end of ShowWholeList


$IdMember = $_SESSION["IdMember"];
$IdRelation = GetParam("IdRelation", 0); // find the concerned member 

if (GetParam("action","")=="") {
	ShowWholeList($IdMember) ;
	exit(0) ;
}

$m = prepare_profile_header(IdMember($IdRelation),"",0) ; // This is the profile of the Relation which is going to be used

switch (GetParam("action")) {

	case "add" : // Add a Relation
		DisplayOneRelation($m,IdMember(Getparam("IdRelation")),"") ;
		exit(0) ;
		break ;
	
	case "view" : // view or update
	case "update" : // view or update
		$TData=LoadRow("select * from specialrelations where specialrelations.IdRelation=".IdMember(Getparam("IdRelation"))." and IdMember=".$_SESSION["IdMember"]) ;
		DisplayOneRelation($m,IdMember(Getparam("IdRelation")),$TData) ;
		exit(0) ;
		break ;
	
	case "doadd" : // Add a contact
		$type=GetParam("type") ; // Find the category, first the text field, ther try dropdown if any 
		
		$str="insert into specialrelations(IdOwner,IdRelation,Type,Comment,created) values(".$_SESSION["IdMember"].",".IdMember(GetParam("IdRelation")).",'".stripslashes($type)."',".InsertInMTrad(GetParam("Comment")).",now())" ;
		sql_query($str) ;
		LogStr("Adding contact for ".fUsername(IdMember(GetParam("IdRelation"))),"MyRelations") ;
		$TData=LoadRow("select * from specialrelations where IdRelation=".IdMember(Getparam("IdRelation"))." and IdMember=".$_SESSION["IdMember"]) ;
		DisplayOneRelation($m,IdMember(Getparam("IdRelation")),$TData) ;
		exit(0) ;
		break ;
	
	case "doupdate" : // Update a contact
		$category=GetParam("Category") ; // Find the category, first the text field, ther try dropdown if any 
		if (($category=="") and ($iCategory>0)) $category=$TContactCategory[$iCategory]->Category ;
		$str="update specialrelations set Comment='".GetParam("Comment")."',Category='".stripslashes($category)."' where IdMember=".$_SESSION["IdMember"]." and IdRelation=".IdMember(GetParam("IdRelation")) ;
		sql_query($str) ;
		LogStr("Updating contact for ".fUsername(IdMember(GetParam("IdRelation"))),"MyRelations") ;
		$TData=LoadRow("select * from specialrelations where IdRelation=".IdMember(Getparam("IdRelation"))." and IdMember=".$_SESSION["IdMember"]) ;
		$TContactCategory=LoadTCategory($IdMember) ; // in case a category was updated
		DisplayOneRelation($m,IdMember(Getparam("IdRelation")),$TData) ;
		exit(0) ;
		break ;
	
	case "delete" : // delete a contact
		$str="delete from  specialrelations  where IdMember=".$_SESSION["IdMember"]." and IdRelation=".IdMember(GetParam("IdRelation")) ;
		sql_query($str) ;
		LogStr("Deleting contact for ".fUsername(IdMember(GetParam("IdRelation"))),"MyRelations") ;
		break ;
}


ShowWholeList($IdMember) ;

?>
