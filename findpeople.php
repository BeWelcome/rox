<?php
require_once "lib/init.php";
require_once "layout/findpeople.php";



// Tis function build the result according to params
function buildresult() {
	global $rCount ; // will be use to find the total of possibilities
	$TMember=array() ;
	
	$limitcount=GetParam("limitcount",10); // Number of records per page
	$start_rec=GetParam("start_rec",0); // Number of records per page

	if (GetParam("OrderBy",0)==0) $OrderBy=" order by Accomodation desc" ;
	
	$nocriteria=true ;
	$dblink="" ; // This will be used one day to query on another replicated database
	$tablelist=$dblink."members,".$dblink."cities,".$dblink."countries" ;
	
	if (GetStrParam("IncludeInactive"=="on")) {
		 $where=" where (Status='Active' or Status='ChoiceInActive' or Status='OutOfRemind')" ; // only active and inactive members
	}
	else {
		 $where=" where Status='Active'" ; // only active members
	}
	
// Process Username parameter if any
	if (GetStrParam("Username","")!="") {
	   	 $Username=GetStrParam("Username") ;
		 if (strpos($Username,"*")!==false) {
		 	$Username=str_replace("*","%",$Username) ;
		 	$where.=" and Username like '".addslashes($Username)."'" ;
		 }
		 else {
		 	$where.=" and Username ='".addslashes($Username)."'" ;
		 }
	   	 $nocriteria=false ;
	}

// Process TextToFind parameter if any
	if (GetStrParam("TextToFind","")!="") {
	   	 $TextToFind=GetStrParam("TextToFind") ;
		 $tablelist=$tablelist.",".$dblink."memberstrads";
	 	 $where=$where." and memberstrads.Sentence like '%".addslashes($TextToFind)."%'" ;
	   	 $nocriteria=false ;
	}

// Process Gender parameter if any
	if (GetStrParam("Gender","0")!="0") {
	   	 $Gender=GetStrParam("Gender") ;
	 	 $where=$where." and Gender='".addslashes($Gender)."' and HideGender='No'" ;
	   	 $nocriteria=false ;
	}

// Process Age parameter if any
	if (GetStrParam("Age","")!="") {
	   	 $Age=GetStrParam("Age") ;
		 if ($Age{0}==">") {
		 	$Age=substr($Age,1) ;
		 	$operation="BirthDate<(NOW() - INTERVAL ".$Age." YEAR)" ;
		 }
		 elseif ($Age{0}=="<") {
		 	$Age=substr($Age,1) ;
		 	$operation="BirthDate>(NOW() - INTERVAL ".$Age." YEAR)" ;
		 }
		 else {
			$Age1=$Age-1 ;
		 	$operation="BirthDate>(NOW()- INTERVAL ".$Age." YEAR) and BirthDate<(NOW() - INTERVAL ".$Age1." YEAR) " ;
		 }
		 
		 
	 	 $where=$where." and ".$operation." and HideBirthDate='No'" ;
	   	 $nocriteria=false ;
	}

	$where.=" and cities.id=members.IdCity and countries.id=cities.IdCountry" ;

	if (!IsLoggedIn()) { // case user is not logged in
	   $where.=" and  memberspublicprofiles.IdMember=members.id" ; // muts be in the public profile list
	   $tablelist=$tablelist.",".$dblink."memberspublicprofiles" ;
	}
	
	if (GetParam("IdCountry",0)!=0) {
	   $where.=" and countries.id=".GetParam("IdCountry") ;
	   $nocriteria=false ;
	}
	

	if ($nocriteria) {
	   die("You must specify at least one criteria\n") ;
	}

	$rCount=LoadRow("select count(*) as cnt from ".$tablelist.$where) ;
	$str="select members.id as IdMember,members.BirthDate,members.Accomodation,members.Username as Username,members.LastLogin as LastLogin,cities.Name as CityName,countries.Name as CountryName,ProfileSummary,Gender,BirthDate from ".$tablelist.$where." ".$OrderBy." limit ".$start_rec.",".$limitcount; ;
	echo "<b>$str</b><br>" ;
	$qry = sql_query($str);
	while ($rr = mysql_fetch_object($qry)) {

	  $rr->ProfileSummary=FindTrad($rr->ProfileSummary,true,$rCount->cnt);
     $photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdMember . " and SortOrder=0");
//	  echo "photo=",$photo->FilePath,"<br>" ;
	  if (isset($photo->FilePath)) $rr->photo=$photo->FilePath;
	  else $rr->photo="" ;
	  
	  $com=LoadRow("select SQL_CACHE count(*) as cnt from comments where IdToMember=".$rr->IdMember) ;

	  $rr->NbComment=$com->cnt ;
	  array_push($TMember, $rr);
	}
	
	
	return($TMember) ;
} // end of buildresult

/*
if (strlen(rtrim(ltrim(GetStrParam("searchtext"))))<=1) { // if void search don't search !
	DisplayResults($TList, GetStrParam("searchtext")); // call the layout with no results
	exit(0) ;
} 
*/

// rebuild the group list
$str = "select SQL_CACHE * from groups";
$qry = sql_query($str);
$TGroup = array ();
while ($rr = mysql_fetch_object($qry)) {
	array_push($TGroup, $rr);
}

$TList=array() ;

switch (GetParam("action")) {


	case "" : // initial form displayed
		 DisplayFindPeopleForm(false,$TGroup,$TList,0) ;
		 break ;

	case ww("FindPeopleAddGroup") : // add groups
		 DisplayFindPeopleForm(true,$TGroup,$TList,0) ;
		 break ;
		// prepare the result list (build the $TList array)

		// search for username or organization  
		$str = "select id,Username,Organizations as result,ProfileSummary from members where Status=\"Active\" and (Username like '%" . addslashes(GetStrParam("searchtext")) . "%' or Organizations like '%" . addslashes(GetStrParam("searchtext")) . "%')";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			$cc=LoadRow ("select countries.Name as CountryName,cities.Name as CityName  from countries,members,cities where members.IdCity=cities.id and countries.id=cities.IdCountry and members.id=".$rr->id);
			$rr->CountryName=$cc->CountryName ;
			array_push($TList, $rr);
		}

		// search in MembersTrads  
		$str = "select members.id as id,Username,memberstrads.Sentence as sresult,ProfileSummary from members,memberstrads where memberstrads.IdOwner=members.id and Status=\"Active\" and memberstrads.Sentence like '%" . addslashes(GetStrParam("searchtext")) . "%' order by Username";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			$cc=LoadRow ("select countries.Name as CountryName,cities.Name as CityName  from countries,members,cities where members.IdCity=cities.id and countries.id=cities.IdCountry and members.id=".$rr->id);
			$rr->CountryName=$cc->CountryName ;
			array_push($TList, $rr);
		}
	case "Find" : // Compute and Show the results 
		 $TList=buildresult() ;
		 DisplayFindPeopleForm(GetParam("ProposeGroup",0),$TGroup,$TList,$rCount->cnt) ;
		 break ;
}

?>
