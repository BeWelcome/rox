<?php
require_once ("Menus.php");
function DisplayMyContactList($IdMember,$TData) {
	global $title;
	$title = ww('MyContactsPage');
	include "header.php";

	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("mycontacts.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns(); // Display the header

	echo "<center>" ;
	echo ww("MyContactListFor",fUsername($IdMember)) ;

	$iiMax = count($TData);
//	echo "<table border=\"1\" rules=\"rows\">";
	echo "<table border=\"1\">";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=left>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
//			echo "<div id=\"topcontent-profile-photo\">\n";
            echo LinkWithPicture($m->Username,$m->photo) ;
//			echo "</div>";
		}
		echo "</td>";
		echo "<td valign=center>", LinkWithUsername($m->Username),"<br><br>";
		echo "<a href=\"regions.php?IdCountry=",$m->IdCountry,"\">",$m->CountryName,"</a><br>" ;
		echo "<a href=\"cities.php?IdRegion=",$m->IdRegion,"\">",$m->RegionName,"</a><br>" ;
		echo "<a href=\"membersbycities.php?IdCity=",$m->IdCity,"\">",$m->CityName,"</a><br>" ;
		echo "</td>";
		echo "<td valign=center align=left colspan=1>";
		echo $m->ProfileSummary;
		echo "</td>";
		echo "<td>" ;
		echo "</td>" ;
		echo "</tr>";
		echo "<tr align=left>";
		echo "<td colspan=3>" ;
		echo "<input type=text name=Category value=\"",$m->Category,"\"><br>" ;
		echo "<textarea type=text name=Comment cols=80 rows=5>",$m->Comment,"</textarea>" ;
		echo "</td>" ;
		echo "<td>" ;
		echo "</td>" ;
		echo "</tr>";
	}
	echo "</table>\n";
	echo "</center>" ;

	include "footer.php";

}

function DisplayOneMyContact($m,$IdContact,$TContact,$TContactCategory) {
	global $title;
	$title = ww('MyContactsPage');
	include "header.php";

	Menu1(); // Displays the top menu
	Menu2("mycontacts.php", ww('MainPage')); // Displays the second menu

	// Header of the profile page
	require_once ("profilepage_header.php");

	menumember("mycontacts.php?IdContact=" . $m->id, $m->id, $m->NbComment);
	echo "	\n<div id=\"columns\">\n";

	echo "		\n<div id=\"columns-low\">\n";
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	echo "\n    <!-- middlenav -->";

	echo "     <div id=\"columns-middle\">\n";
	echo "					<div id=\"content\">";
	echo "						<div class=\"info\">";

	echo "<center>" ;
	echo ww("MyContactX",$m->Username) ;
	echo "<br>",ww("MyContactListExplanation",$m->Username) ;

	echo "<form method=post action=mycontacts.php>" ;	
   echo "<input type=hidden name=IdContact value=",$m->id,">" ;
	echo "<table>" ;
	echo "<tr><td>",ww("ContactListCategory"),"</td><td><input type=text name=Category " ;
	if (isset($TContact->Category)) {
	   echo "value=\"$TContact->Category\"" ;
	}
	echo ">" ;
	if (isset($TContact->id)) {
	   echo "<input type=hidden name=ContactId value=",$TContact->id,">" ;
	   echo "<input type=hidden name=action value=doupdate>" ;
	}
	else {
	   echo "<input type=hidden name=action value=doadd>" ;
	}
	echo "</td>" ; 
	
	echo "<tr><td>",ww("ContactListText"),"</td><td><textarea rows=4 cols=60 name=Comment>" ;
	if (isset($TContact->Comment)) {
	   echo $TContact->Comment ;
	}
	echo "</textarea>" ;
	echo "</td>" ; 
	if (isset($TContact->id)) {
	   echo "<tr><td colspan=2 align=center><input type=submit value=\"",ww("UpdateContact"),"\"></td>\n" ;
	}
	else {
	   echo "<tr><td colspan=2 align=center><input type=submit value=\"",ww("AddContact"),"\"></td>\n" ;
	}
	echo "</table>\n</form>\n" ;

	echo "</center>" ;

	include "footer.php";

}

function DisplayResult($Group,$Title,$Message, $Result = "") {
	global $title;
	$title = ww('ContactGroupPage', $m->Username);
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("contactgroup.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	echo "<center>";
	echo "<H1>Contact ", LinkWithGroup($Group), "</H1>\n";

	echo "<br><br><table width=50%>" ;
	echo "<tr><td><i>",$Title,"</i></td>" ;
	echo "<tr><td>",$Message,"</td>" ;
	echo "<tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";

	echo "					</div>\n"; // info
	echo "				</div>\n"; // content
	echo "			</div>\n"; // middle
	echo "		</div>\n"; // columns

	include "footer.php";

} // end of display result
?>
