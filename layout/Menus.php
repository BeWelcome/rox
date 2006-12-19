<?php
//------------------------------------------------------------------------------
// This function display the main menu
//------------------------------------------------------------------------------
function mainmenu($link="",$tt="") {
  global $title ;
	if ($tt!="") $title=$tt ;
  echo "\n<div align=\"center\" id=\"header\">" ;
  echo "\n<ul>\n" ;

  if (IsLogged()) {	
    echo "<li><a" ;
	  if ($link=="main.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"main.php\" ";
	  }
	  echo " title=\"first page.\">",ww('Welcome'),"</a></li>\n" ;
	}
	else {
    echo "<li><a" ;
	  if ($link=="login.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"login.php\" ";
	  }
	  echo " title=\"Login Page.\">",ww('Login'),"</a></li>\n" ;


    echo "<li><a" ;
	  if ($link=="whatisthis.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"whatisthis.php\" ";
	  }
	  echo " title=\"What is this ?\">",ww('Whatisthis'),"</a></li>\n" ;

	}
	
	
	
	
  echo "<li><a" ;
	if ($link=="membersbycountries.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"membersbycountries.php\" ";
	}
	echo " title=\"Members by countries\">",ww('MembersByCountries'),"</a></li>\n" ;

  echo "<li><a" ;
	if ($link=="search.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"search.php\" ";
	}
	echo " title=\"Search Page\">",ww('SearchPage'),"</a></li>\n" ;

  echo "<li><a" ;
	if ($link=="faq.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"faq.php\" ";
	}
	echo " title=\"Frequently asked questions.\">",ww('faq'),"</a></li>\n" ;

  echo "<li><a" ;
	if ($link=="feedback.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"feedback.php\" ";
	}
	echo " title=\"Contact us\">",ww('ContactUs'),"</a></li>\n" ;

  if (IsLogged()) {	
	
    echo "<li><a" ;
	  if ($link=="groups.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"groups.php\" method=post ";
	  }
	  echo " title=\"Groups in this organization\">",ww('Groups'),"</a></li>\n" ;

    echo "<li><a" ;
	  if (strstr($link,"mymessages.php")!==False) {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"mymessages.php\" method=post ";
	  }
		if (isset($_SESSION['MessageNotRead']) and ($_SESSION['MessageNotRead']>0)) {
	    echo " title=\"My messages\">",ww('MyMessagesNotRead',$_SESSION['MessageNotRead']),"</a></li>\n" ;
		}
		else {
	    echo " title=\"My messages\">",ww('MyMessages'),"</a></li>\n" ;
		}

    echo "<li><a" ;
	  if ($link=="editmyprofile.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"editmyprofile.php\" method=post ";
	  }
	  echo " title=\"Modify my profile\">",ww('EditMyProfile'),"</a></li>\n" ;

	
    echo "<li><a" ;
	  if ($link=="mypreferences.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"mypreferences.php\" method=post ";
	  }
	  echo " title=\"My preferences\">",ww('MyPreferences'),"</a></li>\n" ;

		VolMenuAdd($link,$tt) ; // This will add the volunteer menu feature if any are needed
		
    echo "<li><a" ;
	  if ($link=="main.php?action=Logout") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"main.php?action=logout\" method=post ";
	  }
	  echo " title=\"Logout\">",ww('Logout'),"</a></li>\n" ;

	}
	else {
    echo "<li><a" ;
	  if ($link=="signup.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"signup.php\" ";
	  }
	  echo " title=\"Signup Page.\">",ww('Signup'),"</a></li>\n" ;

	}
	

  echo "</ul>\n</div>\n" ;
	
	// anomalie : les 2 ligne ssuivantes sont nécéssaires pour provoquer un retour à la ligne
  echo "\n<table width=100%><tr><td align=left>&nbsp;</td></table>" ;
  echo "\n<table width=100%><tr><td align=left>&nbsp;</td></table>" ;
} // end of mainmenu

//------------------------------------------------------------------------------
// This function display the Profile menu
//------------------------------------------------------------------------------
function ProfileMenu($link="",$tt="",$MemberUsername="") {
  global $title ;
	if ($MemberUsername=="") {
	  $cid=$_SESSION['IdMember'] ;
	}
	else {
	  $cid=$MemberUsername ;
	}
	if ($tt!="") $title=$tt ;
  echo "\n<div align=\"center\" id=\"header\">" ;
  echo "\n<ul>\n" ;

  echo "<li><a" ;
	if ($link=="main.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"main.php\" method=post ";
	}
	echo " title=\"Back to main page\">",ww('Welcome'),"</a></li>\n" ;


  if (IsLogged()) {	
    echo "<li><a" ;
	  if ($link=="member.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"member.php?cid=".$cid."\" ";
	  }
	  echo " title=\"Member page.\">",ww('MemberPage'),"</a></li>\n" ;
	}
	
  echo "<li><a" ;
	if ($link=="membersbycountries.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"membersbycountries.php\" ";
	}
	echo " title=\"Members by countries\">",ww('MembersByCountries'),"</a></li>\n" ;

  echo "<li><a" ;
	if ($link=="search.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"search.php\" ";
	}
	echo " title=\"Search Page\">",ww('SearchPage'),"</a></li>\n" ;

  echo "<li><a" ;
	if ($link=="contactmember.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"contactmember.php?cid=".$cid."\" ";
	}
	echo " title=\"Contact This Member.\">",ww('ContactMember'),"</a></li>\n" ;

  if (IsLogged()) {	
    echo "<li><a" ;
	  if (strstr($link,"viewcomments.php")!==False) {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"viewcomments.php?cid=".$cid."\" ";
	  }
	  echo " title=\"View comments\">",ww('ViewComments'),"</a></li>\n" ;

    echo "<li><a" ;
	  if (strstr($link,"addcomments.php")!==False) {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"addcomments.php?cid=".$cid."\" ";
	  }
	  echo " title=\"Add comments\">",ww('AddComments'),"</a></li>\n" ;

		VolMenuAdd($link,$tt) ; // This will add the volunteer menu feature if any are needed

    echo "<li><a" ;
	  if ($link=="main.php?action=Logout") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"main.php?action=logout\" method=post ";
	  }
	  echo " title=\"Logout\">",ww('Logout'),"</a></li>\n" ;

	}
	

  echo "</ul>\n</div>\n" ;
	
	// anomalie : les 2 ligne ssuivantes sont nécéssaires pour provoquer un retour à la ligne
  echo "\n<table width=100%><tr><td align=left>&nbsp;</td></table>" ;
  echo "\n<table width=100%><tr><td align=left>&nbsp;</td></table>" ;
} // end of ProfileMenu

//------------------------------------------------------------------------------
// This function display the Messages menu
//------------------------------------------------------------------------------
function MessagesMenu($link="",$tt="",$MemberUsername="") {

//echo "\$link=".$link,"<br>" ;
  global $title ;
	if ($MemberUsername=="") {
	  $cid=$_SESSION['IdMember'] ;
	}
	else {
	  $cid=$MemberUsername ;
	}
	if ($tt!="") $title=$tt ;
  echo "\n<div align=\"center\" id=\"header\">" ;
  echo "\n<ul>\n" ;

  echo "<li><a" ;
	if ($link=="main.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"main.php\" method=post ";
	}
	echo " title=\"Back to main page\">",ww('Welcome'),"</a></li>\n" ;


  if (IsLogged()) {	
    echo "<li><a" ;
	  if (strstr($link,"mymessages.php?action=NotRead")!==False) {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"mymessages.php?action=NotRead\"";
	  }
	  echo " title=\"messages not reads\">",ww('MyMessagesNotRead',$_SESSION['NbNotRead']),"</a></li>\n" ;

    echo "<li><a" ;
	  if (strstr($link,"mymessages.php?action=Received")!==False) {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"mymessages.php?action=Received\"";
	  }
	  echo " title=\"messages received\">",ww('MyMessagesReceived'),"</a></li>\n" ;

		echo "<li><a" ;
	  if (strstr($link,"mymessages.php?action=Sent")!==False) {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"mymessages.php?action=Sent\"";
	  }
	  echo " title=\"messages sent\">",ww('MyMessagesSent'),"</a></li>\n" ;

		echo "<li><a" ;
	  if (strstr($link,"mymessages.php?action=Spam")!==False) {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"mymessages.php?action=Spam\"";
	  }
	  echo " title=\"Spam folder.\">",ww('MyMessagesSpamFolder'),"</a></li>\n" ;

		echo "<li><a" ;
	  if (strstr($link,"mymessages.php?action=Draft")!==False) {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"mymessages.php?action=Draft\"";
	  }
	  echo " title=\"messages draft.\">",ww('MyMessagesDraft'),"</a></li>\n" ;

		VolMenuAdd($link,$tt) ; // This will add the volunteer menu feature if any are needed

    echo "<li><a" ;
	  if ($link=="main.php?action=Logout") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"main.php?action=logout\" method=post ";
	  }
	  echo " title=\"Logout\">",ww('Logout'),"</a></li>\n" ;

	}
	

  echo "</ul>\n</div>\n" ;
	
	// anomalie : les 2 ligne ssuivantes sont nécéssaires pour provoquer un retour à la ligne
  echo "\n<table width=100%><tr><td align=left>&nbsp;</td></table>" ;
  echo "\n<table width=100%><tr><td align=left>&nbsp;</td></table>" ;
} // end of MessagesMenu

//------------------------------------------------------------------------------
// This build the specific menu for volunteers
function VolMenuAdd($link="",$tt="") {

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
} // end of VolMenuAdd 

?>