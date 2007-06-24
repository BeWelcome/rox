<?php
require_once ("menus.php");
require_once ("profilepage_header.php");

function DisplayMyPhotos($m,$TData, $lastaction) {

	global $title, $_SYSHCVOL;
	$title = ww("MyPhotos");
	require_once "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php?cid=".$m->Username);

	// Header of the profile page	DisplayProfilePageHeader( $m );

	menumember("editmyprofile.php?cid=" . $m->id, $m);
	
	if ($m->photo == "") { // if the member has no picture propose to add one
		$MenuAction = "            <li><a href=\"myphotos.php?cid=" . $m->id . "\">" . ww("AddYourPhoto") . "</a></li>\n";
	} else {
		$MenuAction = "            <li><a href=\"myphotos.php?cid=" . $m->id . "\">" . ww("ModifyYourPhotos") . "</a></li>\n";
	}

	ShowActions($MenuAction); // Show the Actions
	ShowAds(); // Show the Ads

	// middle column
	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "          <div class=\"info\"> \n";
	
	// TODO: check the meaning of the next row. $profilewarning is not defined
	if ($profilewarning != "") {
		echo "<h2>", $profilewarning, "</h2>\n";
	}

	$max = count($TData);

	$rCurLang = LoadRow("select * from languages where id=" . $_SESSION['IdLanguage']);
	echo "            <p class=\"important\">", ww("WarningYouAreWorkingIn", $rCurLang->Name, $rCurLang->Name), "</p>\n";
	echo "            <table>\n";

	for ($ii = 0; $ii < $max; $ii++) {
		$rr = $TData[$ii];
		$text = FindTrad($rr->Comment);
		echo "              <tr>\n";
		echo "                <td valign=center align=center>\n";
		if ($ii > 0)
			echo "                <a href=\"", $_SERVER["PHP_SELF"], "?action=moveup&iPos=", $ii, "&IdPhoto=", $rr->id, "&cid=", $m->id, "\" title=\"move picture up \"><img border=0 height=10 src=\"images/up.gif\" alt=\"move picture up \"></a>\n";
		echo "                  <br>\n";
		echo "                  <img src=\"" . $rr->FilePath . "\" height=50 alt=\"", $text, "\">\n";
		echo "                  <br>\n";
		if (($ii +1) < $max)
			echo "                <a href=\"", $_SERVER["PHP_SELF"], "?action=movedown&iPos=", $ii, "&IdPhoto=", $rr->id, "&cid=", $m->id, "\" title=\"move picture down \"><img border=0 height=10 src=\"images/down.gif\" alt=\"move picture down \"></a>";
		echo "                </td>";
		echo "                <td valign=center>\n";
		echo "                  <form method=post style=\"display:inline\">\n";
		echo "                    <input type=hidden name=action value=updatecomment>\n";
		echo "                    <input type=hidden name=IdPhoto value=", $rr->id, ">\n";
		echo "                    <textarea name=Comment cols=50 row=6  style=\"display:inline\">";
		echo FindTrad($rr->Comment);
		echo "</textarea>\n";
		echo "                </td>\n";
		echo "                <td valign=center align=center>\n";
		echo "                  <input type=submit value=\"", ww("updatepicturecomment"), "\">\n";
		echo "                  <input type=hidden name=cid value=", $m->id, ">\n";
		echo "                  </form>\n";
		echo "                  <br>\n";
		echo "                  <form method=post style=\"display:inline\">\n";
		echo "                    <input type=hidden name=action value=deletephoto><input type=hidden name=IdPhoto value=", $rr->id, ">\n";
		echo "                    <input type=submit value=\"", ww("deletepicture"), "\" onclick=\"return confirm('", ww("confirmdeletepicture"), "');\">\n";
		echo "                  </form>\n";
		echo "                </td>\n";
	}

	echo "              <tr>\n";
	echo "                <td colspan=3 align=center>";
	echo "                <hr>\n";
	echo "                <p>", ww('uploadphotorules', ($_SYSHCVOL['UploadPictMaxSize'] / 1024)), "</p>\n";
	echo "                <FORM ENCTYPE=\"multipart/form-data\" action=" . $_SERVER["PHP_SELF"], " METHOD=POST>\n";
	echo "                  <INPUT TYPE=hidden name=MAX_FILE_SIZE value=", $_SYSHCVOL['UploadPictMaxSize'], ">\n"; // Test of file size is done later
	echo "                  <INPUT TYPE=hidden name=action value=UpLoadPicture>\n";
	echo "                  <input type=hidden name=cid value=", $m->id, ">";
	echo "                  <table  cellSpacing=2 cellPadding=3 width=100% valign=top border=0>\n";
	echo "                    <tr>\n";
	echo "                      <td>", ww("commentforthispicture"), "</td>\n";
	echo "                      <td>";
	echo "                        <textarea name=Comment cols=50 row=6  style=\"display:inline\">";
	echo "</textarea></td>\n";
	echo "                    <tr>\n";
	echo "                      <td align=center>", ww('uploadselectpicture'), "</td>\n";
	echo "                      <td><INPUT NAME=\"userfile\" TYPE=file style=font-size=12>\n";
	echo "                    <tr>\n";
	echo "                      <td colspan=2 align=center>";
	echo "                        <br><INPUT TYPE=\"submit\" VALUE=\"", ww('uploadsubmit'), "\" style=font-size=12><br>\n";
	echo "                      </td>\n";
	echo "                  </table>\n";
	echo "                </FORM>\n";
	echo "            </td>\n";
  echo "          </table>\n";
	echo "	      </div>\n";

	require_once "footer.php";
}

// This display only one picture
function DisplayPhoto($Photo) {
	global $title, $_SYSHCVOL;
	$title = ww("MyPhotos");
	require_once ("header.php");

	Menu1(); // Displays the top menu

	Menu2("member.php?cid=".$m->Username);

  // Header of the profile page	DisplayProfilePageHeader( $m );

	menumember("editmyprofile.php?cid=" . $Photo->IdMember, $Photo->IdMember, 0);

  if ($m->photo == "") { // if the member has no picture propose to add one
		$MenuAction = "            <li><a href=\"myphotos.php?cid=" . $m->id . "\">" . ww("AddYourPhoto") . "</a></li>\n";
	} else {
		$MenuAction = "            <li><a href=\"myphotos.php?cid=" . $m->id . "\">" . ww("ModifyYourPhotos") . "</a></li>\n";
	}
	
	ShowActions($MenuAction); // Show the Actions
	ShowAds(); // Show the Ads

	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "          <div class=\"info\"> \n";
	
	echo "<center>\n";
	echo "<table>";
	echo "<tr><td align=center>";
    echo LinkWithUsername($Photo->Username);
	echo "</td>\n";
	echo "<tr><td align=center>";
	echo "<img class=\"framed\" width=\"250px\" src=\"" . $Photo->FilePath . "\" />";
	echo "</td>\n";
	echo "<tr><td align=center>";
    echo $Photo->Comment;
	echo "</td>\n";
	echo "</table>";


	echo "</center>\n";
	echo "					</div>\n";

	require_once "footer.php";

}

?>
