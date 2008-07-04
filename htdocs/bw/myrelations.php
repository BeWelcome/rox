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
require_once "lib/init.php";
require_once "layout/error.php";
include "layout/myrelations.php";
require_once "lib/prepare_profile_header.php";

MustLogIn(); // member must login

// validate or unvalidate relation if symetrique
// return true if relation is confirmed
function IsConfirmed($id1,$id2) {
  $Confirmed="No";
  $r1=LoadRow("select SQL_CACHE * from specialrelations where IdOwner=$id1 and IdRelation=$id2");
  $r2=LoadRow("select SQL_CACHE * from specialrelations where IdOwner=$id2 and IdRelation=$id1");
  if ((isset($r1->IdOwner)) and (isset($r2->IdOwner))) {
  	  $Confirmed="Yes";
	  if ($r1->Confirmed!=$Confirmed) {
	  	 $str="update specialrelations set Confirmed='".$Confirmed."' where id=".$r1->id;
		 sql_query($str); 
	  }
	  if ($r2->Confirmed!=$Confirmed) {
	  	 $str="update specialrelations set Confirmed='".$Confirmed."' where id=".$r2->id;
		 sql_query($str); 
	  }
  }
  else {
  		if (isset($r1->id) and ($r1->Confirmed!=$Confirmed)) {
	  	   $str="update specialrelations set Confirmed='".$Confirmed."' where id=".$r1->id;
		   sql_query($str); 
		}
  		if (isset($r2->id) and ($r2->Confirmed!=$Confirmed)) {
	  	   $str="update specialrelations set Confirmed='".$Confirmed."' where id=".$r2->id;
		   sql_query($str); 
		}
  }
  return($Confirmed=="Yes");
  
} // end of Is Confirmed


function ShowWholeList($IdMember) {

	$TData=Array();
	$str="select SQL_CACHE specialrelations.*,Username,ProfileSummary,IdCity from specialrelations,members where specialrelations.IdOwner=".$IdMember." and members.id=specialrelations.IdRelation order by created";
	$qry=sql_query($str);
	while ($rr = mysql_fetch_object($qry)) {
	    $photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdRelation . " and SortOrder=0");
		if (isset($photo->FilePath)) $rr->photo=$photo->FilePath; 
		$where=LoadRow("select cities.Name as CityName,countries.id as IdCountry,regions.id as IdRegion,cities.id as IdCity,countries.Name as CountryName,regions.Name as RegionName from countries,regions,cities where cities.id=$rr->IdCity and cities.IdCountry=countries.id and regions.id=cities.IdRegion");
		$rr->CountryName=$where->CountryName; 
		$rr->CityName=$where->CityName; 
		$rr->RegionName=$where->RegionName; 
		$rr->IdRegion=$where->IdRegion; 
		$rr->IdCountry=$where->IdCountry; 
	    array_push($TData, $rr);
	}

	DisplayOneRelation($IdMember,$TData);
} // end of ShowWholeList


$IdMember = $_SESSION["IdMember"];



$IdRelation = IdMember(GetStrParam("IdRelation", 0)); // find the concerned member 

if (GetParam("action","")=="") {
	ShowWholeList($IdMember);
	exit(0);
}

$m = prepareProfileHeader(IdMember($IdRelation),"",0); // This is the profile of the Relation which is going to be used

switch (GetParam("action")) {

	case "add" : // Add a Relation
	   if ($IdRelation==$_SESSION['IdMember']) {
	   	 $errcode = "ErrorNoRelationOnYourSelf";
	   	 DisplayError(ww($errcode, $IdMember));
	   	 exit (0);
		}


		DisplayOneRelation($m,$IdRelation,"");
		exit(0);
		break;
	
	case "view" : // view or update
	case "update" : // view or update

		$TData=LoadRow("select * from specialrelations where specialrelations.IdRelation=".IdMember(Getparam("IdRelation"))." and IdOwner=".$_SESSION["IdMember"]);
		$TData->Comment=FindTrad($TData->Comment);
		$TData->Confirmed=IsConfirmed($IdMember,IdMember(GetParam("IdRelation")));
		DisplayOneRelation($m,IdMember(Getparam("IdRelation")),$TData);
		exit(0);
		break;
	
	case "doadd" : // Add a relation
	   if ($IdRelation==$_SESSION['IdMember']) {
	   	 $errcode = "ErrorNoRelationOnYourSelf";
	   	 DisplayError(ww($errcode, $IdMember));
	   	 exit (0);
		}

		$stype=""; 
  		$tt=sql_get_set("specialrelations","Type");
		$max=count($tt);
		for ($ii = 0; $ii < $max; $ii++) {
			if (GetStrParam("Type_" . $tt[$ii])=="on") {
			  if ($stype!="") $stype.=",";
			  $stype.=$tt[$ii];
			}
		}
		
		$str="";
		$str="insert into specialrelations(IdOwner,IdRelation,Type,Comment,created) values(".$IdMember.",".IdMember(GetParam("IdRelation")).",'".stripslashes($stype)."',".NewInsertInMTrad(GetStrParam("Comment"),"specialrelations.Comment",0).",now())";  
		sql_query($str);
		LogStr("Adding relation for ".fUsername(IdMember(GetParam("IdRelation"))),"MyRelations");
		$TData=LoadRow("select * from specialrelations where IdRelation=".IdMember(Getparam("IdRelation"))." and IdOwner=".$_SESSION["IdMember"]);
		$TData->Comment=FindTrad($TData->Comment);
		$TData->Confirmed=IsConfirmed($IdMember,IdMember(GetParam("IdRelation")));

		$defaultlanguage=GetDefaultLanguage($m->id); 
		$textofrelation=$TData->Comment;
	    $Email = AdminReadCrypted($m->Email);
		$urltoconfirm="http://".$_SYSHCVOL['SiteName'] . $_SYSHCVOL['MainDir'] ."myrelations.php?IdRelation=".$_SESSION['Username']."&action=view";
		$subj = wwinlang("MailMyRelationTitle",$defaultlanguage,$_SESSION['Username']);
		$text = wwinlang("MailMyRelationText",$defaultlanguage,$m->Username,$_SESSION['Username'],$textofrelation,$urltoconfirm);
		bw_mail($Email,$subj, $text, "", "",0, "yes", "", "");
		
		DisplayOneRelation($m,IdMember(Getparam("IdRelation")),$TData);

		exit(0);
		break;
	
	case "doupdate" : // Update a contact
	   if ($IdRelation==$_SESSION['IdMember']) {
	   	 $errcode = "ErrorNoRelationOnYourSelf";
	   	 DisplayError(ww($errcode, $IdMember));
	   	 exit (0);
		}

		$stype=""; 
  		$tt=sql_get_set("specialrelations","Type");
		$max=count($tt);
		for ($ii = 0; $ii < $max; $ii++) {
			if (GetStrParam("Type_" . $tt[$ii])=="on") {
			  if ($stype!="") $stype.=",";
			  $stype.=$tt[$ii];
			}
		}

		$rr=LoadRow("select * from specialrelations where IdRelation=".IdMember(Getparam("IdRelation"))." and IdOwner=".$_SESSION["IdMember"]);
		$str="update specialrelations set Comment=".NewReplaceInMTrad(GetStrParam(Comment),"specialrelations.Comment",$rr->id, $rr->Comment, $IdMember).",Type='".$stype."' where IdOwner=".$_SESSION["IdMember"]." and IdRelation=".IdMember(GetParam("IdRelation"));
		sql_query($str);
		LogStr("Updating relation for ".fUsername(IdMember(GetParam("IdRelation"))),"MyRelations");
		$TData=LoadRow("select * from specialrelations where IdRelation=".IdMember(Getparam("IdRelation"))." and IdOwner=".$_SESSION["IdMember"]);
		$TData->Comment=FindTrad($TData->Comment);
		$TData->Confirmed=IsConfirmed($IdMember,IdMember(GetParam("IdRelation")));
		DisplayOneRelation($m,IdMember(Getparam("IdRelation")),$TData);
		exit(0);
		break;
	
	case "delete" : // delete a contact
		$str="delete from  specialrelations  where IdOwner=".$_SESSION["IdMember"]." and IdRelation=".IdMember(GetParam("IdRelation"));
		sql_query($str);
		IsConfirmed($IdMember,IdMember(GetParam("IdRelation"))); // removing the confirmation
		LogStr("Deleting relation for ".fUsername(IdMember(GetParam("IdRelation"))),"MyRelations");
		break;
}


ShowWholeList($IdMember);

?>
