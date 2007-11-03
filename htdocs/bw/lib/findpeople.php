<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
// Tis function build the result according to params
function buildresult() {
	global $rCount ; // will be use to find the total of possibilities
	$TMember=array() ;
	
	$limitcount=GetParam("limitcount",10); // Number of records per page
	$start_rec=GetParam("start_rec",0); // Number of records per page

	if (GetParam("OrderBy",0)==0) $OrderBy=" order by members.created desc" ; // by default find the last created members
	elseif (GetParam("OrderBy",0)==2)  $OrderBy=" order by LastLogin desc" ;
	elseif (GetParam("OrderBy",0)==3)  $OrderBy=" order by LastLogin asc" ;
	elseif (GetParam("OrderBy",0)==4)  $OrderBy=" order by Accomodation desc" ;
	elseif (GetParam("OrderBy",0)==5)  $OrderBy=" order by Accomodation asc" ;
	elseif (GetParam("OrderBy",0)==6)  $OrderBy=" order by HideBirthDate,BirthDate desc" ;
	elseif (GetParam("OrderBy",0)==7)  $OrderBy=" order by HideBirthDate,BirthDate asc" ;
	elseif (GetParam("OrderBy",0)==8)  $OrderBy=" order by NbComment desc" ;
	elseif (GetParam("OrderBy",0)==9)  $OrderBy=" order by NbComment asc" ;
	elseif (GetParam("OrderBy",0)==10)  $OrderBy=" order by countries.Name desc" ;
	elseif (GetParam("OrderBy",0)==11)  $OrderBy=" order by countries.Name asc" ;
	elseif (GetParam("OrderBy",0)==12)  $OrderBy=" order by cities.Name desc" ;
	elseif (GetParam("OrderBy",0)==13)  $OrderBy=" order by cities.Name asc" ;
	
	$nocriteria=true ;
	$dblink="" ; // This will be used one day to query on another replicated database
	if(GetParam("MapSearch") == "on") $tablelist=$dblink."members,".$dblink."cities" ;
	else $tablelist=$dblink."members,".$dblink."cities,".$dblink."countries" ;
	
	if (GetStrParam("IncludeInactive"=="on")) {
		 $where=" where (members.Status='Active' or members.Status='ChoiceInActive' or members.Status='OutOfRemind')" ; // only active and inactive members
	}
	else {
		 $where=" where members.Status='Active'" ; // only active members
	}
	
// Process typic Offer
/*
	 $TypicOffer = (isset($_POST['TypicOffer']))?$_POST['TypicOffer']:null;
	 if (!empty($TypicOffer)) {
    	foreach($TypicOffer as $key => $value) {
				$where.=" and  FIND_IN_SET('".$value."',TypicOffer)" ;
      }
	} 	
*/
// Process Username parameter if any
	if (GetStrParam("Username","")!="") {
	   $Username=GetStrParam("Username") ; // 
		 if (strpos($Username,"*")!==false) {
		 	$Username=str_replace("*","%",$Username) ;
		 	$where.=" and Username like '".addslashes($Username)."'" ;
		 }
		 else {
	     $Username=fUserName(IdMember(GetStrParam("Username"))) ; // in case username was renamed, we do it only here to avoid problems with renamed people
		 	 $where.=" and Username ='".addslashes($Username)."'" ;
		 }
	   	 $nocriteria=false ;
	}

// Process TextToFind parameter if any
	if (GetStrParam("TextToFind","")!="") {
	   	 $TextToFind=GetStrParam("TextToFind") ;
		 // Special case where from the quicksearch the user is looking for a username
		 // in this case, if there is a username corresponding to TextToFind, we force to retrieve it
		 if ((GetParam("OrUsername",0)==1)and(IdMember($TextToFind)!=0)) { // in
		 	$where=$where." and Username='".addslashes($TextToFind)."'" ; 
		 }
		 else {
		 	$tablelist=$tablelist.",".$dblink."memberstrads";
	 	 	$where=$where." and memberstrads.Sentence like '%".addslashes($TextToFind)."%' and memberstrads.IdOwner=members.id" ;
		 }
	   	 $nocriteria=false ;
	}

// Process IdRegion parameter if any
	if (GetParam("IdRegion","")!="") {
	   $IdRegion=GetParam("IdRegion") ;
	 	 $where=$where." and cities.IdRegion=".$IdRegion ;
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

	if (!IsLoggedIn()) { // case user is not logged in
	   $where.=" and  memberspublicprofiles.IdMember=members.id" ; // muts be in the public profile list
	   $tablelist=$tablelist.",".$dblink."memberspublicprofiles" ;
	}
	if(GetParam("MapSearch") == "on") {
		$where.=" and cities.id=members.IdCity" ;
		if(GetParam("bounds_sw_lat") and GetParam("bounds_sw_lng") and GetParam("bounds_ne_lat") and GetParam("bounds_ne_lng")) {
		  if(GetParam("bounds_sw_lng") > GetParam("bounds_ne_lng")) {
			  $where .= " and ((cities.longitude >= ".GetParam("bounds_sw_lng")." and cities.longitude <= 180) or (cities.longitude >= -180 and cities.longitude <= ".GetParam("bounds_ne_lng")."))";
			}
			else {
			  $where .= " and (cities.longitude > ".GetParam("bounds_sw_lng")." and cities.longitude < ".GetParam("bounds_ne_lng").")";
			}
		  if(GetParam("bounds_sw_lat") > GetParam("bounds_ne_lat")) {
			  $where .= " and ((cities.latitude >= ".GetParam("bounds_sw_lat")." and cities.latitude <= 90) or (cities.latitude >= -90 and cities.latitude <= ".GetParam("bounds_ne_lat")."))";
			}
			else {
			  $where .= " and (cities.latitude > ".GetParam("bounds_sw_lat")." and cities.latitude < ".GetParam("bounds_ne_lat").")";
			}
		}
	}
	else {
		$where.=" and cities.id=members.IdCity and countries.id=cities.IdCountry" ;
//	   $where.=" and cities.id=".GetParam("IdCity") ;
	   $nocriteria=false ;

		if (GetParam("IdCountry",0)!= '0') {
	  	   $where.=" and countries.isoalpha2='".GetParam("IdCountry")."'" ;
	   	   $nocriteria=false ;
		}

		if (GetParam("IdCity",0)!=0) {
		   $where.=" and cities.id=".GetParam("IdCity") ;
		   $nocriteria=false ;
		}

		if (GetStrParam("CityName","")!="") { // Case where a text field for CityName is provided
		   $where.=" and (cities.Name='".GetStrParam("CityName")."' or cities.OtherNames like '%".GetStrParam("CityName")."%')" ;
		   $nocriteria=false ;
		}
	}

/*
	if (GetParam("IdRegion",0)!=0) {
	   $where.=" and regions.id=".GetParam("IdRegion") ;
	   $nocriteria=false ;
	}
	*/
	
// if a group is chosen
	if (GetParam("IdGroup",0)!=0) {
	   $tablelist=$tablelist.",".$dblink."membersgroups" ;
	   $where.=" and membersgroups.IdGroup=".GetParam("IdGroup")." and membersgroups.Status='In' and membersgroups.IdMember=members.id" ;
	   $nocriteria=false ;
	}
	

	if ($nocriteria) {
	   $rCount->cnt=-2 ; // it mean no criteria
	}
//	$str="select count(distinct members.id) as cnt from ".$tablelist.$where ;
//	$rCount=LoadRow($str) ;
	
//	if (HasRight("Admin")) echo "For counting page limit: <b>",$str,"</b> cnt=",$rCount->cnt,"<br />\n" ;
	$str="select SQL_CALC_FOUND_ROWS count(comments.id) as NbComment,members.id as IdMember,members.BirthDate,members.HideBirthDate,members.Accomodation,members.Username as Username,members.LastLogin as LastLogin,cities.latitude as Latitude,cities.longitude as Longitude,cities.Name as CityName";
	if(GetParam("MapSearch") != "on") $str .= ",countries.Name as CountryName";
	$str .= ",ProfileSummary,Gender,HideGender from (".$tablelist.") left join ".$dblink."comments on (members.id=comments.IdToMember) ".$where." group by members.id ".$OrderBy." limit ".$start_rec.",".$limitcount." /* Find people */";

	//echo $str;

//	if (HasRight("Debug")) echo " (because of right Debug)<b>$str</b><br />" ;

	$qry = sql_query($str);
	$rCount=LoadRow("SELECT FOUND_ROWS() as cnt") ;

	while ($rr = mysql_fetch_object($qry)) {

	  $rr->ProfileSummary=FindTrad($rr->ProfileSummary,true);
     $photo=LoadRow("select SQL_CACHE * from ".$dblink."membersphotos where IdMember=" . $rr->IdMember . " and SortOrder=0");
//	  echo "photo=",$photo->FilePath,"<br />" ;
	  if (isset($photo->FilePath)) $rr->photo=$photo->FilePath;
	  else {
	  	   $rr->photo=DummyPict($rr->Gender,$rr->HideGender) ;
	  }
	  
	  if ($rr->HideBirthDate=="No") {
	  	 $rr->Age=floor(fage_value($rr->BirthDate)+1) ;
	  }
	  else {
	  	 $rr->Age=ww("Hidden") ;
	  }

	  array_push($TMember, $rr);
	}

	return($TMember) ;
} // end of buildresult

?>
