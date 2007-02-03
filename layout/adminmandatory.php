<?php
require_once ("Menus.php");

function ShowList($TData,$bgcolor="white",$title="") {
	$max = count($TData);
	$count = 0;
	echo "\n<table width=\"60%\" bgcolor=$bgcolor>\n";
	if ($title!="") echo "<th colspan=2 align=center>",$title," (",$max,")</th>\n" ;
	for ($ii = 0; $ii < $max; $ii++) {
		$m = $TData[$ii];
		$count++;
		echo "<tr><td colspan=1>", LinkWithUsername($m->Username), "</td><td colspan=3>", $m->ProfileSummary, "</td>\n";
		echo "<tr><td colspan=4>OldName: ",$m->OldFirstName," <i>",$m->OldSecondName,"</i> <b>",$m->OldLastName,"</b></td>\n";
		echo "<td rowspan=3>";
		if ($m->Status != "Active")
			echo "<a href=\"adminaccepter.php?cid=", $m->id, "&action=accept\">accept</a><br>";
		echo "<a href=\"adminaccepter.php?cid=", $m->id, "&action=reject\">reject</a><br>";
		if ($m->Status != "needmore")
			echo "<a href=\"adminaccepter.php?cid=", $m->id, "&action=needmore\">need more</a><br>";
		echo "<a href=\"contactmember.php?cid=", $m->id, "\">contact</a><br>";
		echo "<a href=\"updatemandatory.php?cid=", $m->id, "\">update mandatory</a>";
		echo "</td>";
		echo "<tr><td>Old Address: ", $m->OldHouseNumber, "</td><td colspan=2>", $m->OldStreetName, "</td><td>", $m->OldZip, "</td>\n";
		echo "<tr><td colspan=4><font color=gray><b>", $m->countryname, " > ", $m->regionname, " > ", $m->cityname, "</b></font></td>\n";
//		echo "<tr><td colspan=4><font color=green><b><i>", $m->FeedBack, "</i></b></font></td><td></td>\n";
// new values
		echo "<tr><td colspan=5>New Name: " ;
		echo $m->FirstName," <i>",$m->SecondName,"</i> <b>",$m->LastName,"</b>";
		echo "</td>\n";
		echo "<tr><td>New Address: ", $m->HouseNumber, "</td><td colspan=2>", $m->StreetName, "</td><td>", $m->Zip, "</td>\n";
		echo "<tr><td colspan=5>$m->Comment</td>\n";
		echo "<tr><td colspan=5><hr></td>\n";
	}
	echo "<tr><td align=left colspan=2>Total</td><td align=left colspan=2>$count</td>";
	echo "\n</table><br>\n";
} // end of ShowList

function DisplayAdminMandatory($TData, $lastaction = "") {
	global $countmatch;
	global $title;
	$title = "Admin mandatory data";
	global $AccepterScope;

	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("adminmandatory.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title . " : " . $lastaction);

	echo " your Scope :", $AccepterScope;

	echo "<center>";

	ShowList($Tpending,"#ffff66"," Members to update");

	echo "<hr><h3> Pending Mandatory</h3>";
	ShowList($TData);


	echo "</center>";

	include "footer.php";
} // end of DisplayAdminMandatory