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
require_once ("profilepage_header.php");

function DisplayMyContactList($IdMember,$TData) {
	global $title;
	$title = ww('MyContactsPage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("mycontacts.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns("mycontacts.php","","<li><a href=\"mycontacts.php\">" . ww('DisplayAllContacts') . "</a></li>"); // Display the header

	echo "        <div class=\"info\">\n";

	$iiMax = count($TData);
	$CurrentCategory="";
	echo "<table border=\"1\" rules=\"rows\" cellspacing=4>";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		if ($m->Category!=$CurrentCategory) {
		   echo "<tr><td colspan=3 align=left>",$m->Category,"</td></tr>\n";
		   $CurrentCategory=$m->Category;
		}
		echo "<tr align=left>";
		echo "<td valign=center align=left>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
//			echo "<div id=\"topcontent-profile-photo\">\n";
            echo LinkWithPicture($m->Username,$m->photo),"<br>";
//			echo "</div>";
		}
		echo LinkWithUsername($m->Username),"<br><br>";
		echo "</td>";
		echo "<td valign=center align=left>";
		echo $m->Comment;
		echo "</td>";
		echo "<td>";
		echo "<a href=\"mycontacts.php?action=update&IdContact=$m->Username\">",ww("UpdateContact"),"</a><br>";
		echo "<a href=\"mycontacts.php?action=delete&IdContact=$m->Username\" onclick=\"return confirm('Confirm delete ?');\">",ww("DeleteContact"),"</a><br>";
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</center>";

	require_once "footer.php";

}

function DisplayOneMyContact($m,$IdContact,$TContact,$TContactCategory) {
	global $title;
	$title = ww('MyContactsPage');
	require_once "header.php";

?>
<SCRIPT  TYPE="text/javascript">
function raz_Category(nameform) {
	document.forms[nameform].elements["Category"].value="";
}		
</SCRIPT>

<?php	
	Menu1(); // Displays the top menu
	Menu2("mycontacts.php", ww('MainPage')); // Displays the second menu

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("mycontacts.php?IdContact=" . $m->id, $m);
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	echo "      <div id=\"col3\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";
	echo "				  <div class=\"info\">";
	echo "<form method=post action=mycontacts.php name=choosecategory>\n";	
  echo "<input type=hidden name=IdContact value=",$m->id,">\n";
	echo "<table>\n";
	echo "<tr><td colspan=3>";
	echo "<br>",ww("MyContactListExplanation",$m->Username);
	echo "</td><tr>";
	echo "<tr><td>";
	$iiMax=count($TContactCategory);
	if ($iiMax>0) {
	   echo ww("ContactListCategoryChooseOrAdd"),"</td><td>";
	   echo "<select name=iCategory OnChange=\"raz_Category('choosecategory');\">\n<option value=-1>",ww("MakeAChoice"),"</option>\n";
	   for ($ii=0;$ii<$iiMax;$ii++) {
	   	   echo "<option value=$ii";
		   if ($TContactCategory[$ii]->Category==$TContact->Category) echo " selected ";
		   echo ">",$TContactCategory[$ii]->Category,"</option> ";
	   }
	   echo" </select>\n";
	   echo " <input type=text name=Category ";
	   if (isset($TContact->Category)) {
	   	  echo "value=\"$TContact->Category\"";
	   }
	   echo ">";
	}
	else {
	  echo ww("ContactListCategory"),"</td><td><input type=text name=Category ";
	   if (isset($TContact->Category)) {
	   	  echo "value=\"$TContact->Category\"";
	   }
	   echo ">";
	}
	if (isset($TContact->id)) {
	   echo "<input type=hidden name=ContactId value=",$TContact->id,">";
	   echo "<input type=hidden name=action value=doupdate>";
	}
	else {
	   echo "<input type=hidden name=action value=doadd>";
	}
	echo "</td>"; 
	
	echo "<tr><td>",ww("ContactListText"),"</td><td><textarea rows=4 cols=60 name=Comment>";
	if (isset($TContact->Comment)) {
	   echo $TContact->Comment;
	}
	echo "</textarea>";
	echo "</td>"; 
	if (isset($TContact->id)) {
	   echo "<tr><td colspan=2 align=center><input type=submit id=submit value=\"",ww("UpdateContact"),"\"></td>\n";
	}
	else {
	   echo "<tr><td colspan=2 align=center><input type=submit id=submit value=\"",ww("AddContact"),"\"></td>\n";
	}
	echo "</table>\n</form>\n";

	echo "</div>";

	require_once "footer.php";

}

function DisplayResult($Group,$Title,$Message, $Result = "") {
	global $title;
	$title = ww('ContactGroupPage', $m->Username);
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("contactgroup.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	echo "<center>";
	echo "<H1>Contact ", LinkWithGroup($Group), "</H1>\n";

	echo "<br><br><table width=50%>";
	echo "<tr><td><i>",$Title,"</i></td>";
	echo "<tr><td>",$Message,"</td>";
	echo "<tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";

	echo "					</div>\n"; // info


	require_once "footer.php";

} // end of display result
?>
