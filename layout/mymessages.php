<?php
require_once ("menus.php");

function DisplayMyMessages($TMess, $Title, $menutab, $FromTo = "") {
	global $title;
	$title = $Title;
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("mymessages.php?action=" . $menutab, ww("MyMessage")); // Displays the second menu

	echo "\n<div id=\"maincontent\">\n";
	echo "  <div id=\"topcontent\">";
	echo "&nbsp<h1>&nbsp;&nbsp;&nbsp;", $Title, " </h1>\n";
	
	echo "	<div id=\"main\">";
	echo "      <div id=\"col1\">\n"; 
	echo "        <div id=\"col1_content\" class=\"clearfix\"> \n"; 
	echo "        </div>\n"; 
	echo "      </div>\n";
	echo "      <div id=\"col3\">\n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "		<p></p>\n";
	echo "        </div>\n"; 
	echo "      </div>\n";
	
	echo "\n  </div>\n";
	echo "</div>\n";

	echo "	<div id=\"columns\">";
	menumessages("mymessages.php?action=" . $menutab, $Title);
	echo "		<div id=\"columns-low\">";
	// MAIN begin 3-column-part
	echo "    <div id=\"main\">";
	ShowActions(); // Show the Actions
	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "          <div id=\"content\"> \n";

	$max = count($TMess);
	if ($max > 0) {
		echo "	<div class=\"info\">\n";
		echo "<table>\n";
		echo "<tr><td colspan=3></td>\n";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "<tr>";
			echo "<td>";
			echo $TMess[$ii]->created;
			echo "</td>";
			echo "<td>";
			echo ww($FromTo, LinkWithUsername($TMess[$ii]->Username));
			echo "</td>";
			echo "<td>";

			if ($TMess[$ii]->WhenFirstRead == "0000-00-00 00:00:00") { // if message is not read propose link to read it
				$text = substr($TMess[$ii]->Message, 0, 15) . " ...";
				echo "<a href=\"" . $_SERVER["PHP_SELF"] . "?action=ShowMessage&IdMess=" . $TMess[$ii]->IdMess . "\">", $text, "</a>";
			} else {
				if (($TMess[$ii]->SpamInfo != 'NotSpam')and($TMess[$ii]->SpamInfo !="")) { // if message is suspected of beeing spam display a flag
					echo "<font color=red><b>SPAM ?</b></font> ";
				}
				echo str_replace("\n","<br>",$TMess[$ii]->Message);
			}
			echo "</td>";
			echo "<td>";
			echo "<a href=\"mymessages.php?action=del&menutab=".$menutab."&IdMess=".$TMess[$ii]->IdMess."\"  onclick=\"return confirm('", ww("confirmdeletemessage"), "');\">",ww("delmessage"),"</a><br>" ;
			// test if has spam mark and propose to remove it
			if ((($menutab == "NotRead") and ($TMess[$ii]->SpamInfo != 'NotSpam')) or ($menutab == "Spam")) {
				echo " <a href=\"mymessages.php?action=marknospam&menutab=".$menutab."&IdMess=".$TMess[$ii]->IdMess."\"  onclick=\"return confirm('", ww("confirmmarknospam"), "');\">",ww("marknospam"),"</a><br>" ;
			}

			// propose to mark as spam or to reply if it is a received message
			if (($menutab == "NotRead") or ($menutab == "Received")) {
			    echo " <a href=\"mymessages.php?action=markspam&menutab=".$menutab."&IdMess=".$TMess[$ii]->IdMess."\"  onclick=\"return confirm('", ww("confirmmarkspam"), "');\">",ww("markspam"),"</a><br>" ;
			    echo " <a href=\"contactmember.php?action=reply&cid=".$TMess[$ii]->Username."&IdMess=".$TMess[$ii]->IdMess."\" >",ww("replymessage"),"</a><br>" ;
			}
			if ($TMess[$ii]->Status=='Draft') {
			    echo " <a href=\"contactmember.php?action=edit&cid=".$TMess[$ii]->Username."&iMes=".$TMess[$ii]->IdMess."\" >",ww("continuemessage"),"</a><br>" ;
			}
			echo "</td>\n";
		}
	}

	echo "</table>\n";
	echo "<div class=\"clear\ />\n";
	echo "</div>\n";
	echo "	</div>";
	echo "				</div>";
	echo "				<div class=\"clear\" />";
	echo "			</div>	";
	echo "			<div class=\"clear\" />	";
	echo "		</div>	";
	echo "		</div>	";
	echo "	</div>	";

	include "footer.php";
}
?>
