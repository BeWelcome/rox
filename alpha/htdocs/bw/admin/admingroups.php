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
require_once "../lib/init.php";
require_once "../lib/FunctionsLogin.php";
require_once "../layout/error.php";
require_once "../layout/admingroups.php";
$IdMember = GetParam("cid");

$countmatch = 0;

$RightLevel = HasRight('Group'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the suffcient <b>Group</b> rights<br>";
	exit (0);
}

$GroupeScope = RightScope('Group');

$lastaction = "";
switch (GetParam("action")) {
	case "logout" :
		Logout();
		exit (0);
		break;
	case "accept" :
		$str = "update membersgroups set Status='In' where id=" . GetParam("IdMembership");
		$qry = sql_query($str);
		$rr = LoadRow("select Username from members,membersgroups where members.id=membersgroups.IdMember and membersgroups.id=" . GetParam("IdMembership"));
		$Message = $rr->Username . " Accepted";
		break;

	case "Kicked" :
		$str = "update membersgroups set Status='Kicked' where id=" . GetParam("IdMembership");
		$qry = sql_query($str);
		$rr = LoadRow("select Username from members,membersgroups where members.id=membersgroups.IdMember and membersgroups.id=" . GetParam("IdMembership"));
		$Message = $rr->Username . " Kicked";
		break;

	case "creategroup" :
		$IdGroup = GetParam("IdGroup",0);
		if ($IdGroup == 0) { // case insert
			 $rr=LoadRow("select * from groups where Name='".GetStrParam("Name")."'") ;
			 if (!empty($rr->id)) {
		   		echo "group ",GetStrParam("Name"), " allready exist" ;
		   		break ;
			}
			$str = "insert into groups(Picture,MoreInfo,HasMembers,Type,Name) values('" . GetStrParam("Picture") . "','". GetStrParam("MoreInfo") . "','" . GetParam("HasMember") . "','" . GetParam("Type") . "','" . GetParam("Name") . "')";
			sql_query($str);
			$IdGroup = mysql_insert_id();
			$str = "insert into words(code,ShortCode,IdLanguage,Sentence,updated,IdMember) values('Group_" . GetStrParam("Name"). "','en',0,'" . addslashes(GetStrParam("Group_")) . "',now(),".$_SESSION['IdMember'].")";
			sql_query($str);
			$str = "insert into words(code,ShortCode,IdLanguage,Sentence,updated,IdMember) values('GroupDesc_" . GetStrParam("Name"). "','en',0,'" . addslashes(GetStrParam("GroupDesc_")) . "',now(),".$_SESSION['IdMember'].")";
			sql_query($str);
			LogStr("Creating group <b>".GetStrParam(Name)."</b>","admingroup") ;
		} else { // case update
			$str = "update groups set HasMembers='" . GetParam("HasMember") . "',Type='" . GetParam("Type") . "',Picture='".GetStrParam("Picture")."',MoreInfo='".GetStrParam("MoreInfo")."' where id=" . $IdGroup;
			sql_query($str);
			$str = "update words set Sentence='".GetStrParam("Group_")."',updated=now(),IdMember=".$_SESSION['IdMember']." where code='Group_" . GetStrParam("Name"). "' and IdLanguage=0";
			sql_query($str);
			$str = "update words set Sentence='".GetStrParam("GroupDesc_")."',updated=now(),IdMember=".$_SESSION['IdMember']." where code='GroupDesc_" . GetStrParam("Name"). "' and IdLanguage=0";
			sql_query($str);
			LogStr("Updating group <b>".GetStrParam("Name")."</b>","admingroup") ;
		}
		$IdParent = GetParam("IdParent");
		if ($IdParent != 0) {
			$rr = LoadRow("select * from groupshierarchy where IdGroupParent=" . $IdParent . " and IdGroupChild=" . $IdGroup);
			if (!isset ($rr->id)) { // test if hierachy already exist
				$str = "insert into groupshierarchy(created,IdGroupParent,IdGroupChild) values(now()," . $IdParent . "," . $IdGroup . ") ";
				sql_query($str);
			}
		}

		sql_query("update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id)");

		header("Location: " . "../groups.php?action=ShowMembers&IdGroup=" . $IdGroup); // Sho the group immediately
		exit (0);
		break;

	case "formcreategroup" :
		$TGroupList = array ();
		$str = "select id,Name from groups order by Name";
		$qry = sql_query($str);
		while ($rr = mysql_fetch_object($qry)) { // building the possible parents groups
			array_push($TGroupList, $rr);
		}
		if ($IdGroup == 0)
			$IdGroup = GetParam("IdGroup", 0);
		if ($IdGroup != 0) {
			$rr = LoadRow("select * from groups where id=" . $IdGroup);
			$Name = $rr->Name;
			$HasMember = $rr->HasMember;
			$Type = $rr->Type;
			$Group_=ww("Group_".$Name);
			$GroupDesc_=ww("GroupDesc_".$Name) ;
			$Picture=$rr->Picture;
			$MoreInfo=$rr->MoreInfo ;
		}
		sql_query("update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id)"); // update hierachy counters
		DisplayFormCreateGroups($IdGroup, $Name, $IdParent, $Type, $HasMember, $TGroupList,$Group_,$GroupDesc_,$MoreInfo,$Picture);
		exit (0);

	case "updategroupscounter" :
		sql_query("update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id)");
		$Message = "Counters updated";
		break;

}

$TPending = array ();

$str = "select Username,groups.Name as GroupName,membersgroups.created as created,membersgroups.id as IdMembership,membersgroups.Comment as Comment from members,membersgroups,groups where members.id=membersgroups.IdMember and membersgroups.Status='WantToBeIn' and membersgroups.IdGroup=groups.id order by IdGroup";
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	if (HasRight("Group", $rr->GroupName)) {
		array_push($TPending, $rr);
	}
}

DisplayAdminGroups($TPending, $Message); // call the layout

// 			sql_query("update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id");
?>
