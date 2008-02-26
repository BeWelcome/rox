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
require_once "layout/groups.php";
if (!empty($_SESSION['IdMember'])) {
	$IdMember = $_SESSION['IdMember'];
} else {
	$IdMember = null;
}

if (HasRight('Admin')) { // Admin will have access to any member right thru cid
	$IdMember = GetParam("cid", $_SESSION['IdMember']);
}

switch (GetParam("action")) {
	case "ShowJoinGroup" :
		$TGroup = LoadRow("select SQL_CACHE * from groups where id=" . GetParam("IdGroup"));
		DisplayDispSubscrForm($TGroup); // call the layout
		exit (0);
	case "LeaveGroup" :
		$TGroup = LoadRow("select SQL_CACHE * from groups where id=" . GetParam("IdGroup"));
		$rr = LoadRow("select SQL_CACHE * from membersgroups where IdMember=" . $IdMember . " and IdGroup=" . GetParam("IdGroup"));
		$str="delete from membersgroups where id=".$rr->id;
		sql_query($str);
		LogStr("Leaving  Group <b>", wwinlang("Group_" . $TGroup->Name, 0), "</b> previous comment='".addslashes(FindTrad($rr->Comment))."'", "Group");
		break;
	case "Add" : // this is for adding or updating membership
		if (GetStrParam("AcceptMessage")=="on") $AcceptMess="yes";
		else  $AcceptMess="no";
		$TGroup = LoadRow("select SQL_CACHE * from groups where id=" . GetParam("IdGroup"));
		$rr = LoadRow("select SQL_CACHE * from membersgroups where IdMember=" . $IdMember . " and IdGroup=" . GetParam("IdGroup"));
		if (isset($rr->id)) {
			$str = "update membersgroups set IacceptMassMailFromThisGroup='".$AcceptMess."',Comment=" . ReplaceInMTrad(GetStrParam('Comment'),$rr->Comment) . " where id=" . $rr->id;
		} else {
			if ($TGroup->Type == "NeedAcceptance") {
				$Status = "WantToBeIn"; // case this is a group with an admin
				// Notfiy the group accepter
				NotifyGroupAccepter($TGroup,$IdMember,GetStrParam('Comment')) ;
			}
			else {
				$Status = "In";
			}
			$str = "insert into membersgroups(IdGroup,IdMember,Comment,created,Status,IacceptMassMailFromThisGroup) values(" . GetParam("IdGroup") . "," . $IdMember . "," . InsertInMTrad(GetStrParam('Comment')) . ",now(),'" . $Status . "','".$AcceptMess."')";
		}
		//			echo "str=$str<br>";
		sql_query($str);
		LogStr("update profile in Group <b>", wwinlang("Group_" . $TGroup->Name, 0), "</b> with comment " . GetStrParam('Comment'), "Group");
		break;
	case "ShowMembers" :
		$TGroup = LoadRow("select * from groups where id=" . GetParam("IdGroup"));
		$Tlist = array ();
		if (IsLoggedIn()) {
		    $IdMemberShip=IdMemberShip($TGroup->id,$IdMember); // find the membership of the current member
			$str = "select SQL_CACHE Username,membersgroups.Comment as GroupComment,membersphotos.FilePath as photo from (members,membersgroups) left join membersphotos on (membersphotos.IdMember=membersgroups.IdMember and membersphotos.SortOrder=0) where members.id=membersgroups.IdMember and membersgroups.Status='In' and members.Status='Active' and membersgroups.IdGroup=" . GetParam("IdGroup");
		} else { // if not logged : only public profile
			$IdMemberShip = null;
			$str = "select SQL_CACHE Username,membersgroups.Comment as GroupComment,membersphotos.FilePath as photo from (members,membersgroups,memberspublicprofiles) left join membersphotos on (membersphotos.IdMember=membersgroups.IdMember and membersphotos.SortOrder=0) where memberspublicprofiles.IdMember=members.id and members.Status='Active' and members.id=membersgroups.IdMember and membersgroups.Status='In' and membersgroups.IdGroup=" . GetParam("IdGroup");
		}
		//			echo "str=$str<br>";
		$qry = sql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			array_push($Tlist, $rr);
		}
		DisplayGroupMembers($TGroup, $Tlist,$IdMemberShip); // call the layout
		exit (0);
	case "ListAll" :
		// Try to load the group list, prepare the layout data
		$str = "select SQL_CACHE * from groups";
		$qry = sql_query($str);
		$TGroup = array ();
		while ($rr = mysql_fetch_object($qry)) {
			array_push($TGroup, $rr);
		}

		DisplayGroupList($TGroup); // call the layout
		exit (0);
}

// update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id)

$TGroup = array (); // Will receive the results
AddGroups($IdMember,1); // Add groups starting with first group
DisplayGroupHierarchyList($TGroup); // call the layout

function AddGroups($IdMember,$IdGroup, $depht = 0) {
	global $TGroup;
	// Try to load the available groups according to group hierarchy
	$str = "select SQL_CACHE groups.id as IdGroup,NbChilds,groups.HasMembers as HasMembers,groups.Name as Name," . $depht . " as Depht,0 as NbMembers from groups,groupshierarchy where groups.id=groupshierarchy.IdGroupChild and IdGroupParent=" . $IdGroup;
	//		echo "str=$str<br>";
	$qry = sql_query($str);
	while ($rr = mysql_fetch_object($qry)) {
		$rnb = LoadRow("select count(*) as cnt from membersgroups,members where IdGroup=" . $rr->IdGroup . " and membersgroups.Status='In' and members.Status='Active' and members.id=membersgroups.IdMember");
		$rr->NbMembers = $rnb->cnt;
		$rr->IdMemberShip=IdMemberShip($rr->IdGroup,$IdMember); // find the membership of the current member
		array_push($TGroup, $rr);
		if ($rr->NbChilds > 0)
			AddGroups($IdMember,$rr->IdGroup, $depht +1);
	}
	return;
}

// This function notify immediately by mail the accepter in charge of a group $TGroup
// than there is one more pending member to accept 
function NotifyGroupAccepter($TGroup,$IdMember,$Comment) {
		$rMember=LoadRow("Select members.*,cities.Name as CityName,countries.Name as CountryName from members,cities,countries where cities.id=members.IdCity and countries.id=cities.IdCountry and members.id=".$IdMember) ;
		$text="" ;
		$subj="New Member ".$rMember->Username." to accept in group ".wwinlang("Group_".$TGroup->Name,0) ;
				
		$query = "SELECT `rightsvolunteers`.`IdMember`,`members`.`Username` from `members`,`rightsvolunteers`,`rights` WHERE `rightsvolunteers`.`IdRight`=`rights`.`id` and `rights`.`Name`='Group' and (`rightsvolunteers`.`Scope` like  '%\"All\"%' or `rightsvolunteers`.`Scope` like '%\"".$TGroup->Name."\"%') and Level>0 and `rightsvolunteers`.`IdMember`=`members`.`id` and (`members`.`Status`='Active' or `members`.`Status`='ActiveHidden')" ;
		$qry = sql_query($query);
		while ($rr = mysql_fetch_object($qry)) {
					$text=" hello, ".$rr->Username." member ".LinkWithUsername($rMember->Username)." from (".$rMember->CountryName."/".$rMember->CityName.") wants to join group <b>".wwinlang("Group_".$TGroup->Name,0)."</b></br>" ;
					$text=$text." he wrote :<p>".$Comment."</p><br /> to accept this membership click on <a href=\"http://www.bewelcome.org/bw/admin/admingroups.php\">admingroup</a>" ;
//					echo $subj,"<br>" ;
//					echo $text,"<br>" ;
//					echo GetEmail($rr->IdMember),"<br>" ;
					bw_mail(GetEmail($rr->IdMember), $subj, $text, "", "noreply@bewelcome.org", 0, "html", "", "");
		}
} // end of NotifyGroupAccepter
?>
