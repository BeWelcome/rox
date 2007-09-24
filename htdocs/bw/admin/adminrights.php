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
require_once "../layout/error.php";
require_once "../layout/adminrights.php";

// trick to manage either "rights" table or "flags" table depending if adminflags or adminrights
if (!isset ($title))
	$title = "Admin Rights";
if (!isset ($thetable))
	$thetable = "rights";
if (!isset ($rightneeded))
	$rightneeded = "Rights";
if (!isset ($IdItem))
	$IdItem = "IdRight";
if ($thetable == "rights") {
	$thememberstable = "rightsvolunteers";
}

MustLogIn(); // need to be logged

$username = GetParam("username");
$Name = GetParam("Name");

$RightLevel = HasRight($rightneeded); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>$rightneeded</b> rights<br>";
	exit (0);
}

$AdminRightScope = RightScope($rightneeded);

$lastaction = "";
switch (GetParam("action")) {
	case "logout" :
		Logout("main.php");
		exit (0);
		break;
	case "helplist" :
		$TDatas = array ();
		$str = "select * from " . $thetable . " order by Name asc";
		$qry = sql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
		   array_push($TDatas, $rr);
		}
		DisplayHelpRights($TDatas,$AdminRightScope);
		break;
		
		 
	case "add" :
		if (HasRight($rightneeded, $Name) <= 0) {
			echo "You miss $rightneeded on <b>", $Name, "</b> for this";
			exit (0);
		}
		$str = "select id from " . $thetable . " where Name='" . $Name . "'";
		$rprevious = LoadRow($str);
		if (IdMember(GetParam("username"))!=0) {
		   $str = "insert into " . $thememberstable . "(Comment,Scope,Level,IdMember,created," . $IdItem . ") values('" . GetParam("Comment") . "','" . GetParam("Scope") . "','" . GetParam("Level") . "','" . IdMember(GetParam("username")) . "',now()," . $rprevious->id . ")";
		   //			echo "str=",$str,"<br>";
		   $qry = sql_query($str);
	   		$lastaction = "Adding " . $thetable . " <i>" . $Name . "</i> for <b>" . GetParam('username') . "</b>";
			LogStr($lastaction, "Admin" . $thetable . "");
		}
		else {
			$lastaction="nothing done";
		}
		break;
	case "update" :
		$IdItemVolunteer = GetParam("IdItemVolunteer");
		$rbefore = LoadRow("select * from " . $thememberstable . " where id=" . $IdItemVolunteer);
		$rCheck = LoadRow("select " . $thetable . ".Name as Name from " . $thetable . "," . $thememberstable . " where " . $thememberstable . "." . $IdItem . "=" . $thetable . ".id and " . $thememberstable . ".id=" . $IdItemVolunteer);
		if ((HasRight($rightneeded, $Name) <= 0) or ($rCheck->Name != $Name)) {
			echo "You miss Rights on <b>", $Name, "</b> for this";
			exit (0);
		}
		$str = "update " . $thememberstable . " set Comment='" . GetParam("Comment") . "',Scope='" . GetParam("Scope") . "',Level=" . GetParam("Level") . " where id=$IdItemVolunteer";
		$qry = sql_query($str);
		$lastaction = "Updating " . $thetable . " <i>" . $Name . "</i> for <b>" . fUsername($rbefore->IdMember) . "</b>";
		LogStr($lastaction, "Admin" . $thetable . "");
		break;
	case "del" :
		$IdItemVolunteer = GetParam("IdItemVolunteer");
		$rbefore = LoadRow("select * from " . $thememberstable . " where id=" . $IdItemVolunteer);
		$rCheck = LoadRow("select " . $thetable . ".Name as Name from " . $thetable . "," . $thememberstable . " where " . $thememberstable . "." . $IdItem . "=" . $thetable . ".id and " . $thememberstable . ".id=" . $IdItemVolunteer);
		if ((HasRight($rightneeded, $Name) < 10) or ($rCheck->Name != $Name)) {
			echo "You miss Rights on <b>", $Name, "</b> for this";
			exit (0);
		}
		$str = "delete from  " . $thememberstable . "  where id=$IdItemVolunteer";
		$qry = sql_query($str);
		$lastaction = "Deleting " . $thetable . " <i>" . $Name . "</i> for <b>" . fUsername($rbefore->IdMember) . "</b>";
		LogStr($lastaction, "Admin" . $thetable . "");
		break;
}

$TDatas = array ();
$TDatasVol = array ();

// Load the values for this member list

$str = "select 0";
if (($username != "") or ($Name != "")) { // if at least one parameter is select try to load corresponding rights
	$str = "select " . $thememberstable . ".*," . $thetable . ".Name as Name,Username from " . $thememberstable . "," . $thetable . ",members where members.id=" . $thememberstable . ".IdMember and " . $thetable . ".id=" . $thememberstable . "." . $IdItem . "";

	// add username filter if any
	if ($username != "") {
		$rwho = LoadRow("select id from members where username='" . $username . "'");
		if (isset ($rwho->id)) {
			$cid = $rwho->id;
		} else {
			$cid = 0;
			$username=""; // reset username if none was found
		}
		$str .= " and " . $thememberstable . ".IdMember=" . $cid;
		//			$groupby=" group by members.id"; 
	} else {
	}

	// Add Name filter if any
	if ($Name != "") {
		$rprevious = LoadRow("select id,Description from " . $thetable . " where Name='" . $Name . "'");
		if (isset ($rprevious->id)) {
			$iid = $rprevious->id;
		} else {
			$iid = 0;
		}
		$str .= " and " . $IdItem . "=" . $iid;
	}
	//		$str=.$groupby;
	$qry = sql_query($str);
	//		echo "$str","<br>";
	while ($rr = mysql_fetch_object($qry)) {
		array_push($TDatasVol, $rr);
	}
}
// end of load list

// Load the right list
$str = "select * from " . $thetable . " order by Name asc";
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	if (!HasRight($rightneeded, $rr->Name))
		continue; // Skip not allowed rights in scope of $rightneeded
	if ($username != "") {
		if (HasRight($rr->Name, "", $rwho->id))
			continue; // Skip already given rights if the user is named
	}
	array_push($TDatas, $rr);
}
// end of Load the right list

DisplayAdminView($username, $Name, $rprevious->Description, $TDatas, $TDatasVol, $rprevious, $lastaction); // call the layout
?>