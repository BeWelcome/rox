<?php

require_once("layouttools.php");


// This menu is the top menu
function Menu1($link = "", $tt = "") {
	
	if (isset($_SESSION['IdMember']))
		$IdMember = $_SESSION['IdMember'];
	else
		$IdMember = "";	
	
	echo "<div id=\"page_margins\">\n";
	echo "  <div id=\"page\" class=\"hold_floats\">\n";
	echo "    <div id=\"header\">\n";
	echo "      <div id=\"topnav\">\n";
	echo "        <div id=\"navigation-functions\">\n";
	echo "          <ul>\n";
	if (isset($_SESSION['WhoIsOnlineCount'])) 	echo "            <li", factive($link, "whoisonline.php"), "><a href=\"".bwlink("whoisonline.php")."\">", ww("NbMembersOnline", $_SESSION['WhoIsOnlineCount']), "</a></li>\n";
		echo "            <li", factive($link, "faq.php"), "><a href=\"".bwlink("faq.php")."\">", ww('faq'), "</a></li>\n";
		echo "            <li", factive($link, "feedback.php"), "><a href=\"".bwlink("feedback.php")."\">", ww('ContactUs'), "</a></li>\n";
	if (IsLoggedIn()) {
		echo "            <li", factive($link, "mypreferences.php?cid=" . $IdMember), "><a href=\"".bwlink("mypreferences.php")."\">", ww("MyPreferences"), "</a></li>\n";
		echo "            <li", factive($link, "aboutus.php"), "><a href=\"".bwlink("aboutus.php")."\">", ww("AboutUsPage"), "</a></li>\n";
		echo "            <li><a href=\"".bwlink("main.php?action=logout")."\" id=\"header-logout-link\">", ww("Logout"), "</a></li>\n";
	} else {
		echo "            <li", factive($link, "index.php"), "><a href=\"".bwlink("index.php")."\" >", ww("Login"), "</a></li>\n";
		echo "            <li", factive($link, "signup.php"), "><a href=\"".bwlink("signup.php")."\">", ww('Signup'), "</a></li>\n";
		echo "            <li", factive($link, "aboutus.php"), "><a href=\"".bwlink("aboutus.php")."\">", ww("AboutUsPage"), "</a></li>\n";
	}
	echo "          </ul>\n";
	echo "        </div>\n"; // end navigation functions
	echo "      </div>\n"; // end topnav
	echo "     <img  id=\"logo\" src=\"images/logo.gif\" alt=\"Be Welcome\" />\n";
	echo "    </div>\n"; // end header

} // end of Menu1

function Menu2($link = "", $tt = "") {
	
	if (isset($_SESSION['IdMember']))
		$IdMember = $_SESSION['IdMember'];
	else
		$IdMember = "";
		
	if (isset($_SESSION['Username']))
		$Username = $_SESSION['Username'];
	else
		$Username = "";

	// #nav: main navigation 
	echo "    <div id=\"nav\">\n";
	echo "      <div id=\"nav_main\">\n";
	echo "        <ul>\n";
	echo "          <li", factive($link, "main.php"), "><a href=\"".bwlink("main.php")."\"><span>", ww("Menu"), "</span></a></li>\n";

	if (IsLoggedIn()) {
	   echo "          <li", factive($link, "member.php?cid=".$Username), "><a href=\"".bwlink("member.php?cid=".$Username)."\"><span>", ww("MyProfile"), "</span></a></li>\n";
	}
	echo "          <li", factive($link, "findpeople.php"), "><a href=\"".bwlink("findpeople.php")."\"><span>", ww('FindMembers'), "</span></a></li>\n";
	echo "          <li", factive($link, "../forums"), "><a href=\"../forums\"><span>".ww("Community")."</span></a></li>\n";
	echo "          <li", factive($link, "groups.php"), "><a href=\"".bwlink("groups.php")."\"><span>", ww('Groups'), "</span></a></li>\n";
	if (IsLoggedIn()) {
	   if (isset ($_SESSION['NbNotRead']) and ($_SESSION['NbNotRead'] > 0)) {
		  $MyMessageLinkText = ww('MyMessagesNotRead', $_SESSION['NbNotRead']); //," ",FlagLanguage() youvegotmessage
 	   } else {
		  $MyMessageLinkText = ww('MyMessages');
	   }
	   echo "          <li", factive($link, "mymessages.php"), "><a href=\"".bwlink("mymessages.php")."\"><span>", $MyMessageLinkText, "</span></a></li>\n";
	} 


	// #nav_flowright: This part of the main navigation floats to the right. The items have to be listed in reversed order to float properly		
	echo "          <span id=\"nav_flowright\">\n";
	echo "          <li>\n";
	echo "      <form action=\"".bwlink("findpeople.php")."\" id=\"form-quicksearch\">\n";
	echo "		<input type=hidden name=OrUsername value=1>" ; // will be used by findpeople to also look for username matching TextToFind
	echo "          <fieldset id=\"fieldset-quicksearch\">\n";
//	echo "          <a href=\"search.php\">", ww('SearchPage'), "</a>\n"; // no earch page for now
	echo "          ",ww('SearchPage'), "\n";
	echo "          <input type=\"text\" name=\"TextToFind\" size=\"10\" maxlength=\"30\" id=\"text-field\" />\n";
	echo "          <input type=\"hidden\" name=\"action\" value=\"Find\" />\n";

	echo "              <input type=\"image\" src=\"".bwlink("images/icon_go.png")."\" id=\"submit-button\" />\n";
	echo "              </fieldset>\n";
	echo "            </form>\n";
	echo "          </li>\n";
	echo "          </span>\n";
	// #nav_flowright: end
	echo "        </ul>\n";
	echo "      </div>\n"; // end nav_main
	echo "    </div>\n"; // end nav
} // end of Menu2



// -----------------------------------------------------------------------------
// This is the Submenu displayed for  Messages menu
function menumessages($link = "") {


	echo "      <div id=\"middle_nav\" class=\"clearfix\">\n";
	echo "        <div id=\"nav_sub\">\n";
	echo "          <ul>\n";

	if (IsLoggedIn()) {
		echo "            <li ", factive($link, "mymessages.php?action=Received"), "><a href=\"".bwlink("mymessages.php?action=Received")."", "\"><span>", ww('MyMessagesReceived'), "</span></a></li>\n";
		echo "            <li ", factive($link, "mymessages.php?action=Sent"), "><a href=\"".bwlink("mymessages.php?action=Sent")."", "\"><span>", ww('MyMessagesSent'), "</span></a></li>\n";
		echo "            <li ", factive($link, "mymessages.php?action=Spam"), "><a href=\"".bwlink("mymessages.php?action=Spam")."", "\"><span>", ww('MyMessagesSpam'), "</span></a></li>\n";
		if (GetPreference("PreferenceAdvanced")=="Yes")
		   echo "            <li ", factive($link, "mymessages.php?action=Draft"), "><a href=\"".bwlink("mymessages.php?action=Draft")."", "\"><span>", ww('MyMessagesDraft'), "</span></a></li>\n";
	}

	echo "          </ul>\n";
	echo "        </div>\n"; // nav_sub
	echo "      </div>\n"; // midde_nav

} // end of menumessages





// -----------------------------------------------------------------------------
// This is the Submenu displayed for  Find Members  menu
function menufindmembers($link = "") {


	echo "      <div id=\"middle_nav\" class=\"clearfix\">\n";
	echo "        <div id=\"nav_sub\">\n";
	echo "          <ul>\n";

	if (IsLoggedIn()) {
		echo "            <li ", factive($link, "findpeople.php"), "><a href=\"".bwlink("findpeople.php")."", "\"><span>", ww('FilteredSearch'), "</span></a></li>\n";
		echo "            <li ", factive($link, "countries.php"), "><a href=\"".bwlink("countries.php")."", "\"><span>", ww('BrowseCountries'), "</span></a></li>\n";
	}

	echo "          </ul>\n";
	echo "        </div>\n"; // nav_sub
	echo "      </div>\n"; // midde_nav

} // end of menumessages


// -----------------------------------------------------------------------------
// This is the Submenu displayed for member profile
function menumember($link = "", $m) {
	$IdMember=$m->id;
	echo "\n";
	echo "      <div id=\"middle_nav\" class=\"clearfix\">\n";
	echo "        <div id=\"nav_sub\">\n";
	echo "          <ul>\n";
//	if ($m->CountTrad>1) { // if member has his profile translated
//	    for ($ii=0;$ii<$m->CountTrad;$ii++) { // display one tab per available translation
//			$Trad=$m->Trad[$ii];
//			echo "			  <li ", factive($link, "member.php?cid=" . $IdMember,$Trad->IdLanguage), "><a href=\"".bwlink("member.php?cid=" . $IdMember)."&lang=".$Trad->ShortCode."\"><span>", ww('MemberPage')," ",FlagLanguage($Trad->IdLanguage), "</span></a></li>\n";
//		}
//	}
//	else {
		echo "            <li ", factive($link, "member.php?cid=" . $IdMember), "><a href=\"".bwlink("member.php?cid=" . $IdMember)."\"><span>", ww('MemberPage'), "</span></a></li>\n";
//	}
	if ($_SESSION["IdMember"] == $IdMember) { // if members own profile
		echo "            <li", factive($link, "myvisitors.php"), "><a href=\"".bwlink("myvisitors.php")."\"><span>", ww("MyVisitors"), "</span></a></li>\n";
		echo "            <li", factive($link, "mypreferences.php?cid=" . $IdMember), "><a href=\"".bwlink("mypreferences.php?cid=" . $IdMember . "")."\"><span>", ww("MyPreferences"), "</span></a></li>\n";
		echo "            <li", factive($link, "editmyprofile.php"), "><a href=\"".bwlink("editmyprofile.php")."\"><span>", ww('EditMyProfile')," ",FlagLanguage(), "</span></a></li>\n";
	} else {
		//  echo "				<li",factive($link,"contactmember.php?cid=".$IdMember),"><a href=\"","contactmember.php?cid=".$IdMember,"\">",ww('ContactMember'),"</a></li>";
	}
	echo "            <li", factive($link, "viewcomments.php?cid=" . $IdMember), "><a href=\"".bwlink("viewcomments.php?cid=" . $IdMember, "")."\"><span>", ww('ViewComments'), "(", $m->NbComment, ")</span></a></li>\n";
	echo "            <li", factive($link, "../blog"), "><a href=\"../blog/".$_SESSION["Username"]."\"><span>", ww("Blog"), "</span></a></li>\n";
	echo "            <li", factive($link, "map.php"), "><a href=\"".bwlink("todo.php")."\"><span>", ww("Map"), "</span></a></li>\n";
	echo "          </ul>\n";
	echo "        </div>\n"; // nav_sub
	echo "      </div>\n"; // nav
} // end of menumember

function factive($link, $value,$IdLanguage=-1) {
	if ((strpos($link, $value) === 0)and(($IdLanguage==-1)or($IdLanguage==$_SESSION["IdLanguage"]))) {
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
		if ($link == "admin/adminwords.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminwords.php")."\" method=post ";
		}
		$res .= " title=\"Words management\">AdminWord</a></li>\n";
	}

	if (HasRight("Accepter")) {
		$res .= "<li><a";

		if ($link == "admin/adminaccepter.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminaccepter.php")."\" method=post ";
		}

		$AccepterScope= RightScope('Accepter');
		if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
		   $InScope = " /* All countries */";
		} else {
		  $InScope = "and countries.id in (" . $AccepterScope . ")";
		}
	 	

		$rr=LoadRow("select SQL_CACHE count(*) as cnt from members,countries,regions,cities where members.Status='Pending' and cities.id=members.IdCity and countries.id=cities.IdCountry and cities.IdRegion=regions.id ".$InScope);
		$res .= " title=\"Accepting members (scope=".addslashes($InScope).")\">AdminAccepter(".$rr->cnt.")</a></li>\n";

		$res .= "<li><a";

		if ($link == "admin/adminmandatory.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminmandatory.php")."\" method=post ";
		}
		$AccepterScope= RightScope('Accepter');
		if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
		   $InScope = " /* All countries */";
		} else {
		  $InScope = "and countries.id in (" . $AccepterScope . ")";
		}
	 	

		$rr=LoadRow("select SQL_CACHE count(*) as cnt from pendingmandatory,countries,regions,cities where pendingmandatory.Status='Pending' and cities.id=pendingmandatory.IdCity and countries.id=cities.IdCountry and cities.IdRegion=regions.id ".$InScope);
		$res .= " title=\"update mandatory data(scope=".addslashes($InScope).")\">AdminMandatory(".$rr->cnt.")</a></li>\n";


	}

	if (HasRight("Grep")) {
		$res .= "<li><a";
		if ($link == "admin/admingrep.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/admingrep.php")."\" method=post ";
		}
		$res .= " title=\"Greping files\">AdminGrep</a></li>\n";
	}

	if (HasRight("Group")) {
		$res .= "<li><a";
		if ($link == "admin/admingroups.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/admingroups.php")."\" method=post ";
		}
		$res .= " title=\"Grepping file\">AdminGroups</a></li>\n";
	}

	if (HasRight("Flags")) {
		$res .= "<li><a";
		if ($link == "admin/adminflags.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminflags.php")."\" method=post ";
		}
		$res .= " title=\"administration of members flags\">AdminFlags</a></li>\n";
	}

	if (HasRight("Rights")) {
		$res .= "<li><a";
		if ($link == "admin/adminrights.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminrights.php")."\" method=post ";
		}
		$res .= " title=\"administration of members rights\">AdminRights</a></li>\n";
	}

	if (HasRight("Logs")) {
		$res .= "<li><a";
		if ($link == "admin/adminlogs.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminlogs.php")."\" method=post ";
		}
		$res .= " title=\"logs of activity\">AdminLogs</a></li>\n";
	}

	if (HasRight("Comments")) {
		$res .= "<li><a";
		if ($link == "admin/admincomments.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/admincomments.php")."\" method=post ";
		}
		$res .= " title=\"managing comments\">AdminComments</a></li>\n";
	}

	if (HasRight("Pannel")) {
		$res .= "<li><a";
		if ($link == "admin/adminpanel.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminpanel.php")."\" method=post ";
		}
		$res .= " title=\"managing Panel\">AdminPanel</a></li>\n";
	}

	if (HasRight("AdminFlags")) {
		$res .= "<li><a";
		if ($link == "admin/adminflags.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminflags.php")."\" method=post ";
		}
		$res .= " title=\"managing flags\">AdminFlags</a></li>\n";
	}

	if (HasRight("Checker")) {
	    $rr=LoadRow("select count(*) as cnt from messages where Status='ToCheck' and messages.WhenFirstRead='0000-00-00 00:00:00'");
		$res .= "<li><a";
		if ($link == "admin/adminchecker.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("admin/adminchecker.php")."\" method=post ";
		}
		$res .= " title=\"Mail Checking\">AdminChecker";
	    $res .=  "(".$rr->cnt.")";
		$res .=  "</a></li>\n";
	}

	if (HasRight("Debug")) {
		$res .= "<li><a";
		if ($link == "phplog.php") {
			$res .= " id=current ";
		} else {
			$res .= " href=\"".bwlink("phplog.php?showerror=10")."\"";
		}
		$res .= " title=\"Show last 10 phps error in log\">php error log</a></li>\n";
	}

	return ($res);
} // end of VolMenu

//------------------------------------------------------------------------------
// This function display the Ads 
function ShowAds() {
	// right column 
 	echo "\n";
  echo "      <div id=\"col2\">\n";
	echo "        <div id=\"col2_content\" class=\"clearfix\">\n";
	echo "          <h3>", ww("Ads"), "</h3>\n";
//	if (IsAdmin()) echo "          <p>ADMIN - no ads</p>" ;

	 echo str_replace("<br />","",ww(21607)); // Google Ads entry
	/*
?>
<script type="text/javascript"><!--
google_ad_client = "pub-2715182874315259";
google_ad_width = 120;
google_ad_height = 240;
google_ad_format = "120x240_as";
google_ad_type = "text_image";
google_ad_channel = "";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

<?php
	*/
	echo "\n";
	echo "        </div>\n"; //col2_content
	echo "      </div>\n"; //col2
} // end of ShowAds

//------------------------------------------------------------------------------
// This function display the Actions
// THis function is here for historical reason, it call in fact  ShowLeftColumn
function ShowActions($Action = "", $VolMenu = false) {
  if ($VolMenu) ShowLeftColumn($Action,VolMenu()) ;
  else ShowLeftColumn($Action) ;
} // end of Show Actions

//------------------------------------------------------------------------------
// This function display the Actions in column left
// $MemberAction stand for the possible action for a member (leave empty if none) 
// $VolunteerAction stand for the possible action for a volunteer (leave empty if none) 
// $MyRelations stand for the possible relations to display in this area (typically from the profile page) (leave empty if none) 
function ShowLeftColumn($MemberAction = "",$VolunteerAction ="", $MyRelations="") {
	// MAIN left column
  echo "\n";
  echo "      <div id=\"col1\"> \n"; 
	echo "        <div id=\"col1_content\" class=\"clearfix\"> \n"; 
	if ($MemberAction != "")  {
		echo "          <h3>", ww("Actions"), "</h3>\n";
		echo "          <ul class=\"linklist\">\n";
		echo $MemberAction;
    	echo "          </ul>\n";
	}

	if ($MyRelations != "")  {
		echo "          <h3>", ww("MyRelations"), "</h3>\n";
		echo "          <ul class=\"linklist\">\n";
		echo $MyRelations;
    	echo "          </ul>\n";
	}

	if ($VolunteerAction != "")  {
		echo "          <h3>", ww("VolunteerAction"), "</h3>\n";
		echo "          <ul class=\"linklist\">\n";
		echo $VolunteerAction;
    	echo "          </ul>\n";
	}
	echo "        </div>\n"; // col1_content
	echo "      </div>\n"; // col1
} // end of ShowLeftColumn



// Function DisplayHeaderWithColumns allow to display a Header With columns
// $TitleTopContent is the content to be display in the TopOfContent
// $MessageBeforeColumnLow is the message to be display before the column area
// $ActionList is the list of eventual action
function DisplayHeaderWithColumns($TitleTopContent = "", $MessageBeforeColumnLow = "", $ActionList = "") {
	global $DisplayHeaderWithColumnsIsSet;
	
	// Teaser (coloured bar)
  echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $TitleTopContent, "</h1>\n"; // title in the Teaser (coloured bar)
	echo "      </div>\n"; //end teaser

	if ($MessageBeforeColumnLow != "")
		echo $MessageBeforeColumnLow;

	ShowLeftColumn($ActionList,VolMenu())  ; // Show the Actions
	ShowAds(); // Show the Ads

	echo "      <div id=\"col3\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";
	
	$DisplayHeaderWithColumnsIsSet = true; // set this for footer function which will be in charge of calling the closing /div
	
} // end of DisplayHeaderWithColumns



// Function DisplayHeaderShortUserContent allow to display short header
function DisplayHeaderShortUserContent($TitleTopContent = "") {
	global $DisplayHeaderShortUserContentIsSet;

	echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $TitleTopContent, "</h1>\n";
	echo "      </div>\n"; // end teaser
	// no tabs >>	
	echo "      <div id=\"middle_nav\" class=\"clearfix\">\n";
	echo "        <div id=\"nav_sub\" class=\"notabs\">\n";
	echo "          <ul></ul>\n";
	echo "        </div>\n";
	echo "      </div>\n";
	
//	ShowLeftColumn($ActionList,VolMenu())  ; // Show the Actions
	ShowAds(); // Show the Ads

  echo "\n";
	echo "      <div id=\"col3\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";

	$DisplayHeaderShortUserContentIsSet = true; // set this for footer function which will be in charge of calling the closing /div

} // end of DisplayHeaderShortUserContent



// Function DisplayHeaderIndexPage allow to display a special header for the index page
function DisplayHeaderIndexPage($TitleTopContent = "") {
	global $DisplayHeaderIndexPageIsSet;

	echo "    <div id=\"main\">\n"; 
	echo "      <div id=\"teaser\" class=\"index clearfix\">\n";
	echo "        <div id=\"teaser_index\">\n"; 


	// Random teaser content generation
	$chKey = rand(1,3);
	
	switch ($chKey) {
		case 1:
			echo "		<div class=\"subcolumns\">\n"; 
			// Display the last created members with a picture
			$m=$mlastpublic ;
			echo "			  <div class=\"c75l\">\n"; 
				echo "<h1>", ww("IndexPageWord2a"),"</h1>\n";
				echo "			  <div class=\"c50l\">\n"; 
				echo "			    <div class=\"subl\">\n"; 
				echo "<h2>", ww("IndexPageWord1a"),"</h2>\n";
				echo "			    </div>\n"; 
				echo "			  </div>\n"; 
				echo "			  <div class=\"c50l\">\n"; 
				echo "			  <div class=\"c50l\">\n"; 
					echo "			    <div class=\"subl\">\n"; 
					echo "				<p class=\"floatbox UserpicFloated\">";
					echo LinkWithPicture($m->Username,$m->photo), LinkWithUsername($m->Username),"<br />",$m->countryname ;
					echo "				</p>\n"; 
					echo "			    </div>\n"; 
				echo "			    </div>\n"; 
				echo "			  <div class=\"c50r\">\n"; 
					echo "			    <div class=\"subr\">\n"; 
					echo "				<p class=\"floatbox UserpicFloated\">";
					echo LinkWithPicture($m->Username,$m->photo), LinkWithUsername($m->Username),"<br />",$m->countryname ;
					echo "				</p>\n"; 
					echo "			    </div>\n"; 
				echo "			    </div>\n";  
				echo "			  </div>\n"; 
			echo "			  </div>\n"; 
			
			echo "			  <div class=\"c25l\">\n"; 
			echo "			    <div class=\"subl\">\n"; 
			echo "				<p class=\"floatbox\">";
			echo "				</p>\n"; 
			echo "			    </div>\n"; 
			echo "			  </div>\n"; 
			echo "		</div>\n"; 
			
			break;
		case 2:
			echo "<h2>", ww("IndexPageWord1"),"</h2>\n";
			echo "<h1>", ww("IndexPageWord2"),"</h1>\n";

			break;
		case 3:
			echo "<h2>", ww("IndexPageWord1b"),"</h2>\n";
			echo "<h1>", ww("IndexPageWord2b"),"</h1>\n";
			break;
	}

	echo "        </div>\n";
	echo "      </div>\n";
	// no tabs >>
	echo "		<hr class=\"hr_divide\" />";

	$DisplayHeaderIndexPageIsSet = true; // set this for footer function which will be in charge of calling the closing /div

} // end of DisplayHeaderIndexPage


// Function DisplayHeaderMainPage allow to display a special header for the index page
function DisplayHeaderMainPage($TitleTopContent = "", $MessageBeforeColumnLow = "", $ActionList = "") {
	global $DisplayHeaderMainPageIsSet;

	echo "    <div id=\"main\">\n"; 
	echo "      <div id=\"teaser\" class=\"clearfix teaser_main\">\n";
	if (IsLoggedIn()) echo "        <h2>", ww("HelloUsername",LinkWithUsername($_SESSION["Username"])),"</h2>\n";
	else 	 echo "        <h2>", ww("YourAreNotLogged"),"</h2>\n";
	
	echo "        <div id=\"teaser_l\">\n"; 
	echo "				<img src=\"" . MyPict() . "\" id=\"MainUserpic\" alt=\"ProfilePicture\"/>\n";	
	echo "        </div>\n"; 
	
	echo "        <div id=\"teaser_r\">\n"; 
	
	echo "			<div class=\"subcolumns\">\n";
	echo "				<div class=\"c38l\">\n";
	echo "    				<div class=\"subcl\">\n";
	echo "          	<p><img src=\"images/icons1616/icon_contactmember.png\" alt=\"Messages\"/>", ww("MainPageNewMessages"),"</p>\n";
	echo "          	<p><img src=\"images/icons1616/icon_addcomments.png\" alt=\"Comments\"/>", ww("MainPageNewComments"),"</p>\n";
	echo "          	<p><img src=\"images/icons1616/icon_myvisitors.png\" alt=\"Visitors\"/>", ww("MainPageNewVisitors"),"</p>\n";	
	echo "        			</div>\n";
	echo "      		</div>\n";
	echo "				<div class=\"c62r\">\n";
	echo "					<div class=\"subcr\">\n";		
	echo "						<div id=\"mapsearch\">\n";
	echo "						<form>\n";
	echo "					          <fieldset> \n";
	echo "					          <input type=\"text\" name=\"searchtext\" size=\"10\" maxlength=\"30\" id=\"text-field\" />\n";
	echo "					          <input type=\"hidden\" name=\"action\" value=\"mapsearch\" />\n";
	echo "					          <input type=\"image\" src=\"".bwlink("images/icon_go.png")."\" id=\"submit-button\" /><br />\n";
	echo "							  Search the map\n";
	echo "					        </fieldset>\n";
	echo "						</form>\n";
	echo "						</div>\n";					
	echo "					</div>\n";
	echo "				</div>\n";
	echo "			</div>\n";
	
	echo "        </div>\n";
	echo "      </div>\n";
	
	// no tabs >>
	echo "	<div id=\"middle_nav\" class=\"clearfix\">\n";
	echo "		<div id=\"nav_sub\" class=\"notabs\">\n";
	echo "			<ul>\n";			
	echo "			</ul>\n";
	echo "		</div>\n";
	echo "	</div>\n";

	ShowLeftColumn($ActionList,VolMenu())  ; // Show the Actions
	ShowAds(); // Show the Ads	

	// middle column
	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	
	$DisplayHeaderMainPageIsSet = true; // set this for footer function which will be in charge of calling the closing /div

} // end of DisplayHeaderMainPage

?>
