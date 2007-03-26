<?php
require_once ("menus.php");

function markcolor($s1,$s2) 
{
	if ($s1==$s2)
		return("");
	else
		return (" bgcolor=#ff00ff");
} // end of markcolor


function ShowList($TData,$bgcolor="white",$title="") {

	$max = count($TData);
	$count = 0;
	echo "\n<table width=\"60%\" bgcolor=$bgcolor>\n";
	if ($title!="") echo "<th colspan=2 align=center>",$title," (",$max,")</th>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		$m = $TData[$ii];
		$count++;
		echo "<tr><td colspan=1>", LinkWithUsername($m->Username), " (",fsince($m->created)," ",localdate($m->created),") </td><td colspan=3>", $m->ProfileSummary, "</td>\n";
		echo "<tr style=\"color:#c0c0c0;\"><td>OldName: </td><td colspan=3>",$m->OldFirstName," <i>",$m->OldSecondName,"</i> <b>",$m->OldLastName,"</b></td>\n";
		echo "<td rowspan=6 valign=center align=left>";
		echo "<a href=\"".bwlink("admin/adminmandatory.php?IdPending=". $m->id. "&action=done")."\">done</a><br>";
		echo "<a href=\"".bwlink("admin/adminmandatory.php?IdPending=". $m->id. "&action=reject")."\">cancel</a><br>";
		echo "<a href=\"".bwlink("admin/adminmandatory.php?IdPending=". $m->id. "&action=updatename")."\">update name</a><br>";
		echo "<a href=\"".bwlink("admin/adminmandatory.php?IdPending=". $m->id. "&action=updateaddress")."\">update address</a><br>";
		echo "<a href=\"".bwlink("updatemandatory.php?cid=". $m->IdMember )."\">update mandatory</a>";
		echo "</td>";
		echo "<tr style=\"color:#c0c0c0;\"><td>Old Address: </td><td>", $m->OldHouseNumber, "</td><td>", $m->OldStreetName, "</td><td>", $m->OldZip, "</td>\n";
		echo "<tr style=\"color:#c0c0c0;\"><td>Old Area: </td><td colspan=3><b>", $m->OldCountryName, " > ", $m->OldRegionName, " > ", $m->OldCityName, "</b></td>\n";
//		echo "<tr><td colspan=4><font color=green><b><i>", $m->FeedBack, "</i></b></font></td><td></td>\n";
// new values
		echo "<tr><td>New Name: </td><td colspan=3",markcolor($m->FirstName.$m->SecondName.$m->LastName,$m->OldFirstName.$m->OldSecondName.$m->OldLastName),">";
		echo $m->FirstName," <i>",$m->SecondName,"</i> <b>",$m->LastName,"</b>";
		echo "</td>\n";
		echo "<tr><td>New Address: </td><td",markcolor($m->HouseNumber,$m->OldHouseNumber),">", $m->HouseNumber, "</td><td",markcolor($m->StreetName,$m->OldStreetName),">", $m->StreetName, "</td><td",markcolor($m->Zip,$m->OldZip),">", $m->Zip, "</td>\n";
		echo "<tr><td>New Area: </td><td colspan=3",markcolor($m->cityname,$m->OldCityName),">", $m->countryname, " > ", $m->regionname, " > ", $m->cityname, "</td>\n";
		echo "<tr><td colspan=5 color=#009900>$m->Comment</td>\n";
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

	Menu2("admin/adminmandatory.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title . " : " . $lastaction);

	echo " your Scope :", $AccepterScope;

	echo "<center>";

	ShowList($Tpending,"#ffff66"," Members to update");

	echo "<hr><h3> Pending Mandatory</h3>";
	ShowList($TData);


	echo "</center>";

	include "footer.php";
} // end of DisplayAdminMandatory