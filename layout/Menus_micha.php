<?php


// This menu is the top menu
function Menu1($link = "", $tt = "") {
	echo "\n<div id=\"header\">\n";
	echo "  <div id=\"logo\">\n";
	echo "    <div id=\"logo-placeholder\">\n";
	echo "      <img alt=\"logo\" src=\"images/logo.png\" />\n";
	echo "    </div>\n";
	echo "  </div>\n";
	echo "  <div id=\"navigation-functions\">\n";
	echo "    <ul>\n";
	if (isset($_SESSION['WhoIsOnlineCount'])) 	echo "      <li ", factive($link, "whoisonline.php"), "><a href=\"whoisonline.php\">", ww("NbMembersOnline", $_SESSION['WhoIsOnlineCount']), "</a></li>\n";
	echo "      <li ", factive($link, "faq.php"), "><a href=\"faq.php\">", ww('faq'), "</a></li>\n";
	echo "      <li ", factive($link, "feedback.php"), "><a href=\"feedback.php\">", ww('ContactUs'), "</a></li>\n";
	if (IsLoggedIn()) {
		echo "			<li", factive($link, "mypreferences.php?cid=" . $IdMember), "><a href=\"mypreferences.php\">", ww("MyPreferences"), "</a></li>\n";
		echo "			<li", factive($link, "aboutus.php"), "><a href=\"aboutus.php\">", ww("AboutUsPage"), "</a></li>\n";
		echo "      <li><a href=\"main.php?action=logout\" id=\"header-logout-link\">", ww("Logout"), "</a></li>\n";
	} else {
		echo "      <li", factive($link, "login.php"), "><a href=\"login.php\" >", ww("Login"), "</a></li>\n";
		echo "      <li", factive($link, "signup.php"), "><a href=\"signup.php\">", ww('Signup'), "</a></li>\n";
		echo "			<li", factive($link, "aboutus.php"), "><a href=\"aboutus.php\">", ww("AboutUsPage"), "</a></li>\n";
	}
	echo "    </ul>\n";
	echo "  </div>\n"; // navigation functions

	echo "  <br class=\"clear\"/>\n";
	echo "  <div id=\"navigation-access\">\n";
	echo "    <ul>\n";
	echo "    <li><a href=\"countries.php\">", ww('Countries'), "</a></li>\n";

	echo "    <li><a href=\"todo.php\">Map</a></li>\n";
	echo "    <li>\n";
	echo "      <form action=\"quicksearch.php\" id=\"form-quicksearch\">\n";
	echo "          <fieldset id=\"fieldset-quicksearch\">\n";
//	echo "          <a href=\"search.php\">", ww('SearchPage'), "</a>\n"; // no earch page for now
	echo "          ",ww('SearchPage'), "\n";
	echo "          <input type=\"text\" name=\"searchtext\" size=\"10\" maxlength=\"30\" id=\"text-field\" />\n";
	echo "          <input type=\"hidden\" name=\"action\" value=\"quicksearch\" />\n";
	echo "          <input type=\"image\" src=\"images/icon_go.png\" id=\"submit-button\" />\n";
	echo "        </fieldset>\n";

	echo "      </form>\n";
	echo "    </li>\n";
	echo "    </ul>\n";
	echo "  </div>\n "; // navigation access

} // end of Menu1

function Menu2($link = "", $tt = "") {
	echo "\n";
	echo "  <div id=\"navigation-main\">\n";
	echo "    <ul>\n";
	echo "      <li ", factive($link, "main.php"), "><a href=\"main.php\"><span>", ww("Menu"), "</span></a></li>\n";

	echo "      <li ", factive($link, "member.php?cid=".$_SESSION["Username"]), "><a href=\"member.php?cid=".$_SESSION["Username"]."\"><span>", ww("MyProfile"), "</span></a></li>\n";
	if (isset ($_SESSION['MessageNotRead']) and ($_SESSION['MessageNotRead'] > 0)) {
		$MyMessageLinkText = ww('MyMessagesNotRead', $_SESSION['MessageNotRead']);
	} else {
		$MyMessageLinkText = ww('MyMessages');
	}
	echo "      <li ", factive($link, "mymessages.php"), "><a href=\"mymessages.php\"><span>", $MyMessageLinkText, "</span></a></li>\n";
	echo "      <li ", factive($link, "members.php"), "><a href=\"members.php\"><span>Members</span></a></li>\n";
	echo "      <li ", factive($link, "groups.php"), "><a href=\"groups.php\"><span>", ww('Groups'), "</span></a></li>\n";
	echo "      <li ", factive($link, "http://travelbook.bewelcome.org/newlayout/htdocs/forums"), "><a href=\"http://travelbook.bewelcome.org/newlayout/htdocs/forums\"><span>Forum</span></a></li>\n";
	if (IsLoggedIn()) {
			echo "      <li ", factive($link, "http://travelbook.bewelcome.org/newlayout/htdocs/blog/".$_SESSION["Username"]), "><a href=\"http://travelbook.bewelcome.org/newlayout/htdocs/blog/".$_SESSION["Username"]."\"><span>Blogs</span></a></li>\n";
		} else {
			echo "      <li ", factive($link, "http://travelbook.bewelcome.org/newlayout/htdocs/blog"), "><a href=\"http://travelbook.bewelcome.org/newlayout/htdocs/blog\"><span>Blogs</span></a></li>\n";
	} 
	echo "      <li ", factive($link, "http://travelbook.bewelcome.org/newlayout/htdocs/gallery/show"), "><a href=\"http://travelbook.bewelcome.org/newlayout/htdocs/gallery/show\"><span>Gallery</span></a></li>\n";
	echo "    </ul>\n";
	echo "  </div>\n";

	echo "  <div class=\"clear\" />\n";
	echo "</div>\n";
} // end of Menu2

// -----------------------------------------------------------------------------
// This is the Submenu displayed for  Messages menu
function menumessages($link = "", $tt = "") {

	//echo "\$link=".$link,"<br>" ;
	global $title;

	if ($tt != "")
		$title = $tt;

	echo "\n	<div id=\"columns-top\">\n";
	echo "			<ul id=\"navigation-content\">\n";

	if (IsLoggedIn()) {
		echo "				<li ", factive($link, "mymessages.php?action=NotRead"), "><a href=\"mymessages.php?action=NotRead", "\"><span>", ww('MyMessagesNotRead', $_SESSION['NbNotRead']), "</span></a></li>\n";
		echo "				<li ", factive($link, "mymessages.php?action=Received"), "><a href=\"mymessages.php?action=Received", "\"><span>", ww('MyMessagesReceived'), "</span></a></li>\n";
		echo "				<li ", factive($link, "mymessages.php?action=Sent"), "><a href=\"mymessages.php?action=Sent", "\"><span>", ww('MyMessagesSent'), "</span></a></li>\n";
		echo "				<li ", factive($link, "mymessages.php?action=Spam"), "><a href=\"mymessages.php?action=Spam", "\"><span>", ww('MyMessagesSpam'), "</span></a></li>\n";
		echo "				<li ", factive($link, "mymessages.php?action=Draft"), "><a href=\"mymessages.php?action=Draft", "\"><span>", ww('MyMessagesDraft'), "</span></a></li>\n";
	}

	echo "			</ul>\n";
	echo "	</div>\n"; // columns top

} // end of menumessages

// -----------------------------------------------------------------------------
// This is the Submenu displayed for member profile
function menumember($link = "", $IdMember = 0, $NbComment) {
	echo "\n";
	echo "	<div id=\"columns-top\">\n";
	echo "		<ul id=\"navigation-content\">\n";
	echo "			<li ", factive($link, "member.php?cid=" . $IdMember), "><a href=\"member.php?cid=" . $IdMember, "\"><span>", ww('MemberPage'), "</span></a></li>\n";
	if ($_SESSION["IdMember"] == $IdMember) { // if members own profile
		echo "		  <li", factive($link, "myvisitors.php"), "><a href=\"myvisitors.php\"><span>", ww("MyVisitors"), "</span></a></li>\n";
		echo "			<li", factive($link, "mypreferences.php?cid=" . $IdMember), "><a href=\"mypreferences.php?cid=" . $IdMember . "\"><span>", ww("MyPreferences"), "</span></a></li>\n";
		echo "			<li", factive($link, "editmyprofile.php"), "><a href=\"editmyprofile.php\"><span>", ww('EditMyProfile'), "</span></a></li>\n";
	} else {
		//  echo "				<li",factive($link,"contactmember.php?cid=".$IdMember),"><a href=\"","contactmember.php?cid=".$IdMember,"\">",ww('ContactMember'),"</a></li>" ;
	}
	echo "			<li", factive($link, "viewcomments.php?cid=" . $IdMember), "><a href=\"viewcomments.php?cid=" . $IdMember, "\"><span>", ww('ViewComments'), "(", $NbComment, ")</span></a></li>\n";
	echo "			<li", factive($link, "http://travelbook.bewelcome.org/newlayout/htdocs/blog"), "><a href=\"http://travelbook.bewelcome.org/newlayout/htdocs/blog\"><span>", ww("Blog"), "</span></a></li>\n";
	echo "			<li", factive($link, "map.php"), "><a href=\"todo.php\"><span>", ww("Map"), "</span></a></li>\n";
	echo "		</ul>\n";
	echo "	</div>\n"; // columns top
} // end of menumember

function factive($link, $value) {
	if (strpos($link, $value) === 0) {
		return (" class=\"active\"");
	} else
		return ("");
} // end of factive

//------------------------------------------------------------------------------
// This build the specific menu for volunteers
function VolMenu($link = "", $tt = "") {

	$res = "";

	if (HasRight("Words")) {
		$res .= "\n<li><a";
		if ($link == "adminwords.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"adminwords.php\" method=post ";
		}
		$res .= " title=\"Words managment\">AdminWord</a></li>\n";
	}

	if (HasRight("Accepter")) {
		$res .= "<li><a";
		if ($link == "adminaccepter.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"adminaccepter.php\" method=post ";
		}
		$res .= " title=\"Accepting members\">AdminAccepter</a></li>\n";
	}

	if (HasRight("Grep")) {
		$res .= "<li><a";
		if ($link == "admingrep.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"admingrep.php\" method=post ";
		}
		$res .= " title=\"Greping files\">AdminGrep</a></li>\n";
	}

	if (HasRight("Group")) {
		$res .= "<li><a";
		if ($link == "admingroups.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"admingroups.php\" method=post ";
		}
		$res .= " title=\"Grepping file\">AdminGroups</a></li>\n";
	}

	if (HasRight("Rights")) {
		$res .= "<li><a";
		if ($link == "adminrights.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"adminrights.php\" method=post ";
		}
		$res .= " title=\"administration of members rights\">AdminRights</a></li>\n";
	}

	if (HasRight("Logs")) {
		$res .= "<li><a";
		if ($link == "adminlogs.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"adminlogs.php\" method=post ";
		}
		$res .= " title=\"logs of activity\">AdminLogs</a></li>\n";
	}

	if (HasRight("Comments")) {
		$res .= "<li><a";
		if ($link == "admincomments.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"admincomments.php\" method=post ";
		}
		$res .= " title=\"managing comments\">AdminComments</a></li>\n";
	}

	if (HasRight("Pannel")) {
		$res .= "<li><a";
		if ($link == "adminpanel.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"adminpanel.php\" method=post ";
		}
		$res .= " title=\"managing Panel\">AdminPanel</a></li>\n";
	}

	if (HasRight("AdminFlags")) {
		$res .= "<li><a";
		if ($link == "adminflags.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"adminflags.php\" method=post ";
		}
		$res .= " title=\"managing flags\">AdminFlags</a></li>\n";
	}

	if (HasRight("Checker")) {
		$res .= "<li><a";
		if ($link == "adminchecker.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"adminchecker.php\" method=post ";
		}
		$res .= " title=\"Mail Checking\">AdminChecker</a></li>\n";
	}

	if (HasRight("Debug")) {
		$res .= "<li><a";
		if ($link == "phplog.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"phplog.php?showerror=5\"";
		}
		$res .= " title=\"Show last 5 phps error in log\">php error log</a></li>\n";
	}

	return ($res);
} // end of VolMenu

//------------------------------------------------------------------------------
// This function display the Ads 
function ShowAds() {
	// right column 
 	echo "\n   <div id=\"col2\">\n";
	echo "     <div id=\"col2_content\" class=\"clearfix\">\n";
	echo "            <div id=\"content\"> \n"; 
	echo "              <div class=\"info\"> \n";
	echo "         <h3>", ww("Ads"), "</h3>\n";
	?>
<script type="text/javascript"><!--
google_ad_client = "pub-2715182874315259";
google_ad_width = 120;
google_ad_height = 600;
google_ad_format = "120x600_as";
google_ad_type = "text_image"; 
google_ad_channel = "";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js ">
</script>
 
 
// <script type="text/javascript"><!--
// google_ad_client = "pub-2715182874315259";
// google_ad_width = 180;
// google_ad_height = 150;
// google_ad_format = "180x150_as";
// google_ad_type = "text_image"; 
// google_ad_channel = "";
// --></script>

// <script type="text/javascript"
//   src="http://pagead2.googlesyndication.com/pagead/show_ads.js ">
// </script>

	<?php
	echo "     </div>\n";
	echo "   </div>\n";
	echo "     </div>\n";
	echo "   </div>\n";
} // end of ShowAds

//------------------------------------------------------------------------------
// This function display the Actions 
function ShowActions($Action = "", $VolMenu = false) {
	// MAIN left column
   echo "\n      <div id=\"col1\"> \n"; 
	echo "          <div id=\"col1_content\" class=\"clearfix\"> \n"; 
	echo "            <div id=\"content\"> \n"; 
	echo "              <div class=\"info\"> \n";
	if (($Action != "") or ($VolMenu)) {
		echo "           <h3>", ww("Actions"), "</h3>\n";

		echo "           <ul>\n";
		echo $Action;
		if ($VolMenu)
			echo VolMenu();
		echo "\n           </ul>\n";
	}
	echo "         </div>\n"; // Class info 
	echo "       </div>\n"; // content
	echo "     </div>\n"; // col1_content
	echo "    </div>\n"; // col1
} // end of Show Actions

// Function DisplayHeaderWithColumns allow to display a Header With columns
// $TitleTopContent is the content to be display in the TopOfContent
// $MessageBeforeColumnLow is the message to be display before the column area
// $ActionList is the list of eventual action
function DisplayHeaderWithColumns($TitleTopContent = "", $MessageBeforeColumnLow = "", $ActionList = "") {
	global $DisplayHeaderWithColumnsIsSet;
	echo "\n<div id=\"maincontent\">\n";
	echo "  <div id=\"topcontent\">";

	// BLUE 3-columns-part
	echo "	<div id=\"main\">";
	echo "      <div id=\"col1\">\n"; 
	echo "        <div id=\"col1_content\" class=\"clearfix\"> \n"; 
	echo "		<h1>", $TitleTopContent, "</h1>\n";
	echo "        </div>\n"; 
	echo "      </div>\n";
	echo "      <div id=\"col3\">\n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "		<p></p>\n";
	echo "        </div>\n"; 
	echo "      </div>\n";
	// IE Column Clearing 
echo "        <div id=\"ie_clearing\">&nbsp;</div>\n"; 
	// End: IE Column Clearing 

echo "      </div>\n"; // end main
	// End: BLUE 3-columns-part
	echo "        </div>\n"; 
	echo "<div id=\"columns-top\" class=\"notabs\">";
	echo "	</div>";
	echo "      </div>\n";
	echo "\n";
	echo "  <div id=\"columns\">\n";
	if ($MessageBeforeColumnLow != "")
		echo $MessageBeforeColumnLow;

	echo "    <div id=\"columns-low\">\n";

	ShowActions($ActionList); // Show the Actions
	ShowAds(); // Show the Ads

	echo "		<div id=\"columns-middle\">\n";
	echo "			<div id=\"content\">\n";
	echo "				<div class=\"info\">\n";

	$DisplayHeaderWithColumnsIsSet = true; // set this for footer function which will be in charge of calling the closing /div

} // end of DisplayHeaderWithColumns

// Function DisplayHeaderShortUserContent allow to display short header
function DisplayHeaderShortUserContent($TitleTopContent = "") {
	global $DisplayHeaderShortUserContentIsSet;

	echo "\n<div id=\"maincontent\">\n";
	echo "  <div id=\"topcontent\">";
	echo "					<h1>", $TitleTopContent, "<br /></h1>\n";
	echo "\n  </div>\n";
	echo "<div id=\"columns-top\" class=\"notabs\">";
	echo "	</div>";
	echo "</div>\n";

	echo "<div class=\"user-content\">\n";

	$DisplayHeaderShortUserContentIsSet = true; // set this for footer function which will be in charge of calling the closing /div

} // end of DisplayHeaderShortUserContent

// Function DisplayHeaderIndexPage allow to display a special header for the index page
function DisplayHeaderIndexPage($TitleTopContent = "") {
	global $DisplayHeaderIndexPageIsSet;

	echo "\n<div id=\"maincontent\">\n";
	echo "  <div id=\"topcontent\"><div id=\"col1_content\">";
	echo "					<h1>", $TitleTopContent, "<br /></h1>\n";
	echo "\n  </div></div>\n";
	echo "<div id=\"columns-top\" class=\"notabs\">";
	echo "	</div>";
	echo "</div>\n";

	echo "<div class=\"user-content\">\n";

	$DisplayHeaderIndexPageIsSet = true; // set this for footer function which will be in charge of calling the closing /div

} // end of DisplayHeaderIndexPage

?>
