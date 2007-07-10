<?php
require_once ("menus.php");
function DisplayPannel($TData, $Message = "") {
	global $title;
	global $PannelScope;
	if ($title == "")
		$title = "Admin Pannel";
	require_once "header.php";
	Menu1("", "Admin pannel"); // Displays the top menu

	Menu2("adminpannel.php", $title); // Displays the second menu

	DisplayHeaderShortUserContent($Message);

	echo "Your Scope is for <b>", $PannelScope, "</b><br>";

	$max = count($TData);
	echo "<form method=post>\n";
	echo "<table>\n";
	echo "<tr><th colspan=2>key</th><th colspan=2>value</th><th>comment</th>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		$rr = $TData[$ii];
		//	  echo "<tr><td>",$ii,"</td><td>",$rr->SYSHCvol_key ,$rr->SYSHCvol_value,$rr->SYSHCvol_comment,"</td>\n";

		echo "<tr>\n";
		echo "<td><textarea name=SYSHCvol_key_" . $ii . " rows=1 cols=50>", $rr->SYSHCvol_key . "</textarea></td><td>=</td>\n";
		echo "<td><textarea name=SYSHCvol_value_" . $ii . " rows=1 cols=50>", $rr->SYSHCvol_value . "</textarea><td> //</td></td>\n";
		echo "<td><textarea name=SYSHCvol_comment_" . $ii . " rows=1 cols=50>", $rr->SYSHCvol_comment . "</textarea></td>\n";
		echo "<td></td>\n";

	}
	echo "</table>\n";
	echo "<input type=submit id=submit name=action value=\"SaveToDB\"> &nbsp;&nbsp;&nbsp;";
	echo "<input type=submit id=submit name=action value=\"LoadFromDB\"> &nbsp;&nbsp;&nbsp;";
	echo "<input type=submit id=submit name=action value=\"LoadFromFile\"> &nbsp;&nbsp;&nbsp;";
	echo "<input type=submit id=submit name=action value=\"Generate\"> &nbsp;&nbsp;&nbsp;";

	echo "</form>\n";
	echo "<hr>";
	for ($ii = 0; $ii < $max; $ii++) {
		$rr = $TData[$ii];
		echo "<div style=\"font-size=10px;color=green;\">";
		if ($rr->SYSHCvol_key != "")
			echo $rr->SYSHCvol_key, "=";
		echo $rr->SYSHCvol_value, " //", $rr->SYSHCvol_comment;
		echo "</div>\n";
	}

	require_once "footer.php";
} // end of DisplayPannel
?>