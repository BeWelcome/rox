<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/admingroups.php";
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
		Logout("main.php");
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
		$IdGroup = GetParam("IdGroup");
		if ($IdGroup == 0) {
			$str = "insert into groups(HasMembers,Type,Name) values('" . GetParam("HasMember") . "','" . GetParam("Type") . "','" . GetParam("Name") . "')";
			sql_query($str);
			$IdGroup = mysql_insert_id();
		} else {
			$str = "update groups set HasMembers='" . GetParam("HasMember") . "',Type='" . GetParam("Type") . "' where id=" . $IdGroup;
			sql_query($str);
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

		header("Location: " . "groups.php?action=ShowMembers&IdGroup=" . $IdGroup); // Sho the group immediately
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
		}
		sql_query("update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id)"); // update hierachy counters
		DisplayFormCreateGroups($IdGroup, $Name, $IdParent, $Type, $HasMember, $TGroupList);
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

// 			sql_query("update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id") ;
?>