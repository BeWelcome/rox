<?php

// This menu is the top menu
function Menu1($link="",$tt="") {
echo "<div id=\"header\">\n";
echo "  <div id=\"logo\">\n";
echo "    <div id=\"logo-placeholder\"><img alt=\"logo\" height=\"27px\" src=\"images/logo.png\" /> </div>\n";
echo "  </div>\n";
echo "  <div id=\"navigation-functions\">\n";
echo "    <ul>\n";
echo "      <li ",factive($link,"whoisonline.php"),"><a href=\"whoisonline.php\">",ww("NbMembersOnline",$_SESSION['WhoIsOnlineCount']),"</a></li>" ;
echo "      <li ",factive($link,"faq.php"),"><a href=\"faq.php\">",ww('faq'),"</a></li>\n";
echo "      <li ",factive($link,"feedback.php"),"><a href=\"feedback.php\">",ww('ContactUs'),"</a></li>\n";
if (IsLogged()) {
  echo "			<li",factive($link,"mypreferences.php?cid=".$IdMember),"><a href=\"mypreferences.php\">",ww("MyPreferences"),"</a></li>\n" ;
  echo "      <li><a href=\"main.php?action=logout\" id=\"header-logout-link\">",ww("Logout"),"</a></li>\n";
}
else {
  echo "      <li",factive($link,"login.php"),"><a href=\"login.php\" >",ww("Login"),"</a></li>\n";
  echo "      <li",factive($link,"signup.php"),"><a href=\"signup.php\">",ww('Signup'),"</a></li>\n";
}
echo "    </ul>\n";
echo "  </div>\n"; // navigation functions

	
echo "    <br class=\"clear\"/>\n" ;
echo "    <div id=\"navigation-access\">\n";
echo "    <ul>\n";
echo "    <li><a href=\"membersbycountries.php\">",ww('MembersByCountries'),"</a></li>\n";

echo "    <li><a href=\"todo.php\">Map</a></li>\n";
echo "    <li>\n";
echo "      <form action=\"quicksearch.php\" id=\"form-quicksearch\">\n";
echo "          <fieldset id=\"fieldset-quicksearch\">\n";
echo "          <a href=\"search.php\">",ww('SearchPage'),"</a>\n";
echo "          <input type=\"text\" name=\"searchtext\" size=\"10\" maxlength=\"30\" id=\"text-field\" />\n";
echo "          <input type=\"hidden\" name=\"action\" value=\"quicksearch\" />\n";
echo "          <input type=\"image\" src=\"images/icon_go.png\" id=\"submit-button\" />\n";
echo "        </fieldset>\n";

echo "      </form>\n";
echo "    </li>\n";
echo "    </ul>\n";
echo "    </div>\n ";  // navigation access

} // end of Menu1


function Menu2($link="",$tt="") {
echo "<div id=\"navigation-main\">";
echo "    <ul>";
echo "      <li ",factive($link,"main.php"),"><a href=\"main.php\">",ww("Menu"),"</a></li>";

if (isset($_SESSION['MessageNotRead']) and ($_SESSION['MessageNotRead']>0)) {
  $MyMessageLinkText=ww('MyMessagesNotRead',$_SESSION['MessageNotRead']) ;
}
else {
  $MyMessageLinkText=ww('MyMessages') ;
}

echo "			<li ",factive($link,"mymessages.php"),"><a href=\"mymessages.php\">",$MyMessageLinkText,"</a></li>" ;
echo "      <li ",factive($link,"members.php"),"><a href=\"members.php\">Members</a></li>";
echo "      <li ",factive($link,"groups.php"),"><a href=\"groups.php\">",ww('Groups'),"</a></li>";
echo "      <li ",factive($link,"forum.php"),"><a href=\"todo.php\">Forum</a></li>";
echo "      <li ",factive($link,"blogs.php"),"><a href=\"todo.php\">Blogs</a></li>";
echo "      <li ",factive($link,"gallery.php"),"><a href=\"todo.php\">Gallery</a></li>";
echo "    </ul>";
echo "  </div>";
  
echo "  <div class=\"clear\" />" ;
echo "</div>" ;
} // end of Menu2


// -----------------------------------------------------------------------------
// This is the Submenu displayed for  Messages menu
function menumessages($link="",$tt="") {

//echo "\$link=".$link,"<br>" ;
  global $title ;

	if ($tt!="") $title=$tt ;

  echo "	<div id=\"columns-top\">" ;
  echo "			<ul id=\"navigation-content\">" ;

  if (IsLogged()) {	
    echo "				<li ",factive($link,"mymessages.php?action=NotRead"),"><a href=\"mymessages.php?action=NotRead","\">",ww('MyMessagesNotRead',$_SESSION['NbNotRead']),"</a></li>\n" ;
    echo "				<li ",factive($link,"mymessages.php?action=Received"),"><a href=\"mymessages.php?action=Received","\">",ww('MyMessagesReceived'),"</a></li>\n" ;
    echo "				<li ",factive($link,"mymessages.php?action=Sent"),"><a href=\"mymessages.php?action=Sent","\">",ww('MyMessagesSent'),"</a></li>\n" ;
    echo "				<li ",factive($link,"mymessages.php?action=Spam"),"><a href=\"mymessages.php?action=Spam","\">",ww('MyMessagesSpam'),"</a></li>\n" ;
    echo "				<li ",factive($link,"mymessages.php?action=Draft"),"><a href=\"mymessages.php?action=Draft","\">",ww('MyMessagesDraft'),"</a></li>\n" ;
	}

  echo "			</ul>\n" ;
  echo "	</div>\n" ; // columns top
	
} // end of menumessages

// -----------------------------------------------------------------------------
// This is the Submenu displayed for member profile
function menumember($link="",$IdMember=0,$NbComment) {
echo "	<div id=\"columns-top\">\n" ;
echo "			<ul id=\"navigation-content\">\n" ;
echo "				<li ",factive($link,"member.php?cid=".$IdMember),"><a href=\"member.php?cid=".$IdMember,"\">",ww('MemberPage'),"</a></li>\n" ;
if ($_SESSION["IdMember"]==$IdMember) { // if members own profile
  echo "				<li",factive($link,"myvisitors.php"),"><a href=\"myvisitors.php\">",ww("MyVisitors"),"</a></li>\n" ;
  echo "				<li",factive($link,"mypreferences.php?cid=".$IdMember),"><a href=\"mypreferences.php?cid=".$IdMember."\">",ww("MyPreferences"),"</a></li>\n" ;
  echo "				<li",factive($link,"editmyprofile.php"),"><a href=\"editmyprofile.php\">",ww('EditMyProfile'),"</a></li>\n" ;
}
else {
//  echo "				<li",factive($link,"contactmember.php?cid=".$IdMember),"><a href=\"","contactmember.php?cid=".$IdMember,"\">",ww('ContactMember'),"</a></li>" ;
}
echo "				<li",factive($link,"viewcomments.php?cid=".$IdMember),"><a href=\"viewcomments.php?cid=".$IdMember,"\">",ww('ViewComments'),"(",$NbComment,")</a></li>\n" ;
echo "				<li",factive($link,"blog.php"),"><a href=\"todo.php\">",ww("Blog"),"</a></li>\n" ;
echo "				<li",factive($link,"map.php"),"><a href=\"todo.php\">",ww("Map"),"</a></li>\n" ;
echo "			</ul>\n" ;
echo "	</div>\n" ; // columns top
} // end of menumember


function factive($link,$value) {
  if (strpos($link,$value)===0) {
	  return(" class=\"active\"") ;
	}
	else return("") ;
} // end of factive


//------------------------------------------------------------------------------
// This build the specific menu for volunteers
function VolMenu($link="",$tt="") {

  if (HasRight("Words")) {
    echo "<li><a" ;
	  if ($link=="adminwords.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"adminwords.php\" method=post ";
	  }
	  echo " title=\"Words managment\">AdminWord</a></li>\n" ;
	}

  if (HasRight("Accepter")) {
    echo "<li><a" ;
	  if ($link=="adminaccepter.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"adminaccepter.php\" method=post ";
	  }
	  echo " title=\"Accepting members\">AdminAccepter</a></li>\n" ;
	}

  if (HasRight("Grep")) {
    echo "<li><a" ;
	  if ($link=="admingrep.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"admingrep.php\" method=post ";
	  }
	  echo " title=\"Greping files\">AdminGrep</a></li>\n" ;
	}
	
  if (HasRight("Group")) {
    echo "<li><a" ;
	  if ($link=="admingroups.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"admingroups.php\" method=post ";
	  }
	  echo " title=\"Grepping file\">AdminGroups</a></li>\n" ;
	}

  if (HasRight("Rights")) {
    echo "<li><a" ;
	  if ($link=="adminrights.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"adminrights.php\" method=post ";
	  }
	  echo " title=\"administration of members rights\">AdminRights</a></li>\n" ;
	}

  if (HasRight("Logs")) {
    echo "<li><a" ;
	  if ($link=="adminlogs.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"adminlogs.php\" method=post ";
	  }
	  echo " title=\"logs of activity\">AdminLogs</a></li>\n" ;
	}

  if (HasRight("Comments")) {
    echo "<li><a" ;
	  if ($link=="admincomments.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"admincomments.php\" method=post ";
	  }
	  echo " title=\"managing comments\">AdminComments</a></li>\n" ;
	}

  if (HasRight("Pannel")) {
    echo "<li><a" ;
	  if ($link=="adminpannel.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"adminpannel.php\" method=post ";
	  }
	  echo " title=\"managing Pannel\">AdminPannel</a></li>\n" ;
	}
	
  if (HasRight("AdminFlags")) {
    echo "<li><a" ;
	  if ($link=="adminflags.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"adminflags.php\" method=post ";
	  }
	  echo " title=\"managing flags\">AdminFlags</a></li>\n" ;
	}
	
  if (HasRight("Checker")) {
    echo "<li><a" ;
	  if ($link=="adminchecker.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"adminchecker.php\" method=post ";
	  }
	  echo " title=\"Mail Checking\">AdminChecker</a></li>\n" ;
	}

  if (HasRight("Debug")) {
    echo "<li><a" ;
	  if ($link=="phplog.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"phplog.php?showerror=5\"";
	  }
	  echo " title=\"Show last 5 phps error in log\">php error log</a></li>\n" ;
	}


} // end of VolMenu

//------------------------------------------------------------------------------
// This function display the Ads 
function ShowAds() {
  echo "\n    <!-- rightnav -->"; 
  echo "     <div id=\"columns-right\">\n" ;
  echo "       <ul>" ;
  echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
  echo "         <li></li>\n" ;
  echo "       </ul>\n" ;
  echo "     </div>\n" ;
} // end of ShowAds


//------------------------------------------------------------------------------
// This function display the Actions 
function ShowActions($Action="",$VolMenu=false) {
  echo "    <!-- leftnav -->\n"; 
  echo "     <div id=\"columns-left\">\n"; 
  echo "       <div id=\"content\">\n"; 
  echo "         <div class=\"info\">\n"; 
  if (($Action!="")or ($VolMenu)) {
    echo "           <h3>",ww("Actions"),"</h3>\n"; 

    echo "           <ul>\n"; 
    echo $Action ;
		if ($VolMenu) VolMenu() ;
    echo "\n           </ul>\n";
	} 
  echo "         </div>\n"; // Class info 
  echo "       </div>\n";  // content
  echo "     </div>\n";  // columns-left
} // end of Show Actions

?>
