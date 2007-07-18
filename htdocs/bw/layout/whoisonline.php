<?php
require_once ("menus.php");

function DisplayWhoIsOnLine($TData,$TGuest) {
	global $title;
	$title = ww('WhoIsOnLinePage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], ww('WhoIsOnLinePage')); // Displays the second menu

	DisplayHeaderShortUserContent($title); // Display the header	
		
	echo "        <div class=\"info\">\n";

	$iiMax = count($TData);
	echo "          <table class=\"memberlist\">";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
		    echo LinkWithPicture($m->Username,$m->photo);
//			echo "<a href=\"", $m->photo, "\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">\n<img src=\"" . $m->photo . "\" height=\"100px\" ></a>\n<br>";
			echo "</div>";
		}
		echo "</td>";
		echo "<td valign=center>",LinkWithUsername($m->Username), "</td>";
		echo " <td valign=center>", $m->countryname, "</td> ";
		echo "<td valign=center>";
		//    echo $m->ProfileSummary;
		if (IsAdmin()) {
			echo $m->NbSec," sec";
		}

		echo "</td>";
		echo "<td valign=center>";
		//    echo $m->ProfileSummary;
		if (IsAdmin()) {
			echo $m->lastactivity;
		}

		echo "</td>";
		echo "</tr>";
	} // end of for ii
	echo "</table>";
	
	if (IsAdmin()) {
		 $iiMax = count($TGuest);
		 echo "          <br><table class=\"memberlist\">";
		 echo "<tr><th colspan=2>Guest activity in last ".$_SYSHCVOL['WhoIsOnlineDelayInMinutes']." minutes </th></tr>\n" ;
		 for ($ii = 0; $ii < $iiMax; $ii++) {
		 		 $m = $TGuest[$ii];
				 echo "<tr align=left>";
				 echo "<td valign=center>";
				 echo $m->NbSec;
				 echo " sec</td>";
				 echo "<td valign=center>";
				 echo "<a href=\"/admin/adminlogs.php?ip=".$m->appearance."\">".$m->appearance."</a>";
				 echo "</td>";
				 echo "<td valign=center>";
				 echo $m->lastactivity;
				 echo "</td>";
				 echo "</tr>";
			} // end of for ii
			echo "</table>";
	
	}

	if (!IsLoggedIn()) {
		 echo "<br>",ww("OnlinePrivateProfilesAreNotDisplayed") ;
	}
	echo "\n         </div>\n"; // Class info 

	require_once "footer.php";
	;
}
?>
