<?php

/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foobar is distributed in the hope that it will be useful,
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

function DisplayMyRelationsList($IdMember,$TData) {
	global $title;
	$title = ww('MyContactsPage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("myrelations.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns("myrelations.php","","<li><a href=\"mycontacts.php\">" . ww('DisplayAllContacts') . "</a></li>"); // Display the header

	echo "<center>";

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

function DisplayOneRelation($m,$IdRelation,$TRelation) {
	global $title;
	$title = ww('MyRelationsPage');
	require_once "header.php";

?>
<SCRIPT  TYPE="text/javascript">
function raz_Category(nameform) {
	document.forms[nameform].elements["type"].value="";
}		
</SCRIPT>

<?php	
	Menu1(); // Displays the top menu
	Menu2("myrelations.php", ww('MainPage')); // Displays the second menu

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("myrelations.php?IdRelation=" . $m->id, $m);
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	// open col3 (middle column)
	echo "    <div id=\"col3\"> \n"; 
	echo "      <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	// user content
	// About Me (Profile Summary)
	echo "        <div class=\"info\">\n";

	echo "<center>";

	echo "<form method=post action=myrelations.php name=choosecategory>\n";	
   echo "<input type=hidden name=IdRelation value=",$m->id,">\n";
	echo "<table>\n";
	echo "<tr><td colspan=3>";
	echo "<br>",ww("MyRelationListExplanation",$m->Username,$m->Username);
	echo "</td><tr>";
	echo "<tr><td>";
  	echo ww("RelationListCategory"),"</td><td>";

  	$tt=sql_get_set("specialrelations","Type");
	$max=count($tt);
	for ($ii = 0; $ii < $max; $ii++) {
		echo "<input type=checkbox name=\"Type_" . $tt[$ii] . "\"";
		if (strpos(" ".$TRelation->Type,$tt[$ii] )!=0)
		echo " checked ";
		echo "> ",ww("Relation_Type_" . $tt[$ii]),"<br>";
	}
	echo "</td>";
	if (isset($TRelation->id)) {
	   echo "<input type=hidden name=RelationId value=",$TRelation->id,">";
	   echo "<input type=hidden name=action value=doupdate>";
	}
	else {
	   echo "<input type=hidden name=action value=doadd>";
	}
	echo "</td>"; 
	
	echo "<tr><td>",ww("RelationText",$m->Username),"</td><td><textarea rows=4 cols=60 name=Comment>";
	if (isset($TRelation->Comment)) {
	   echo $TRelation->Comment;
	}
	echo "</textarea>";
	echo "</td>"; 
	if (isset($TRelation->id)) {
	   echo "<tr><td colspan=2 align=center><input type=submit id=submit value=\"",ww("UpdateRelation"),"\"></td>\n";
	}
	else {
	   echo "<tr><td colspan=2 align=center><input type=submit id=submit value=\"",ww("AddRelation"),"\"></td>\n";
	}
	echo "</table>\n</form>\n";
	echo "<br><br>";
	if ($TRelation->Confirmed) echo ww("RelationConfirmedByXX",LinkWithUsername($m->Username));
	else  echo ww("RelationNotConfirmedByXX",LinkWithUsername($m->Username));

	echo "</center>";
	
	echo "              </div>\n"; // end subcr
	echo "            </div>\n"; // end c50r
	echo "          </div>\n"; // end subcolumns
  echo "        </div>\n"; // end info highlight

	require_once "footer.php";
}
?>
