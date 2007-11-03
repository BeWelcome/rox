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
	echo "            <table class=\"admin\" width=\"60%\" border=\"0\" bgcolor=$bgcolor>\n";
	if ($title!="") echo "              <th colspan=2 align=center>",$title," (",$max,")</th>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		$m = $TData[$ii];
		$count++;
		echo "                <tr>\n";
		echo "                  <td colspan=1>", LinkWithUsername($m->Username), " (",fsince($m->created)," ",localdate($m->created),") </td>\n";
		echo "                  <td colspan=3>", $m->ProfileSummary, "</td>\n";
		echo "                </tr>\n";
		echo "                <tr style=\"color:#c0c0c0;\">\n";
		echo "                  <td>OldName: </td>\n";
		echo "                  <td colspan=3>",$m->OldFirstName," <i>",$m->OldSecondName,"</i> <b>",$m->OldLastName,"</b></td>\n";
		echo "                  <td rowspan=6 valign=center align=left>\n";
		echo "                    <a href=\"".bwlink("admin/adminmandatory.php?IdPending=". $m->id. "&action=done")."\">done</a><br>";
		echo "                    <a href=\"".bwlink("admin/adminmandatory.php?IdPending=". $m->id. "&action=reject")."\">cancel</a><br>";
		echo "                    <a href=\"".bwlink("admin/adminmandatory.php?IdPending=". $m->id. "&action=updatename")."\">update name</a><br>";
		echo "                    <a href=\"".bwlink("admin/adminmandatory.php?IdPending=". $m->id. "&action=updateaddress")."\">update address</a><br>";
		echo "                    <a href=\"".bwlink("updatemandatory.php?cid=". $m->IdMember )."\">update mandatory</a>";
		echo "                  </td>";
		echo "                </tr>\n";
		echo "                <tr style=\"color:#c0c0c0;\">\n";
		echo "                  <td>Old Address: </td>\n";
		echo "                  <td>", $m->OldHouseNumber, "</td>\n";
		echo "                  <td>", $m->OldStreetName, "</td>\n";
		echo "                  <td>", $m->OldZip, "</td>\n";
		echo "                </tr>\n";
		echo "                <tr style=\"color:#c0c0c0;\">\n";
		echo "                  <td>Old Area: </td>\n";
		echo "                  <td colspan=3><b>", $m->OldCountryName, " > ", $m->OldRegionName, " > ", $m->OldCityName, "</b></td>\n";
		echo "                </tr>\n";
//	echo "                <tr><td colspan=4><font color=green><b><i>", $m->FeedBack, "</i></b></font></td><td></td>\n";
// new values
		echo "                <tr>\n";
		echo "                  <td>New Name: </td>\n";
		echo "                  <td colspan=3",markcolor($m->FirstName.$m->SecondName.$m->LastName,$m->OldFirstName.$m->OldSecondName.$m->OldLastName),">";
		echo $m->FirstName," <i>",$m->SecondName,"</i> <b>",$m->LastName,"</b>";
		echo "</td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td>New Address: </td>\n";
		echo "                  <td",markcolor($m->HouseNumber,$m->OldHouseNumber),">", $m->HouseNumber, "</td>\n";
		echo "                  <td",markcolor($m->StreetName,$m->OldStreetName),">", $m->StreetName, "</td>\n";
		echo "                  <td",markcolor($m->Zip,$m->OldZip),">", $m->Zip, "</td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td>New Area: </td>\n";
		echo "                  <td colspan=3",markcolor($m->cityname,$m->OldCityName),">", $m->countryname, " > ", $m->regionname, " > ", $m->cityname, "</td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td colspan=5 color=#009900>$m->Comment</td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td colspan='5'><hr /></td>\n";
		echo "                </tr>\n";
	}
	echo "              <tr>\n";
	echo "                <td align=left colspan=2>Total</td><td align=left colspan=2>$count</td>\n";
	echo "              </tr>\n";
	echo "            </table><br>\n";
} // end of ShowList

function DisplayAdminMandatory($TData, $lastaction = "") {
	global $countmatch;
	global $title;
	$title = "Admin mandatory data";
	global $AccepterScope;

	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admin/adminmandatory.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title . " : " . $lastaction);

	echo "          <div class=\"info\">\n";
  echo "            <p>your Scope :", $AccepterScope, "</p>\n";

	// TODO: check the meaning of the next row. Tpending is not defined
	ShowList($Tpending,"#ffff66"," Members to update");

	echo "            <h3> Pending Mandatory</h3>\n";
	ShowList($TData);


	echo "        </div>";

	require_once "footer.php";
} // end of DisplayAdminMandatory