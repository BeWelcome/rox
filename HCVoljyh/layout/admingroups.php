<?php
require_once("Menus.php") ;

// This form propose the members to admin
function DisplayAdminGroups($TPending,$Message) {
  global $countmatch ;
  global $title ;
  $title="Admin groups" ;
  include "header.php" ;
  mainmenu("admingroups.php") ;
	
	echo "<center>" ;

	if ($Message!="") {
	  echo "<h2>$Message</h2>" ;
	}
	
	
	$max=count($TPending) ;
	$count=0 ;
	
	echo "<h3> Members to accept</h3>" ;
	echo "\n<table width=40%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $rr=$TPending[$ii] ;
		$count++ ;
	  echo "<tr>" ;
		echo "<td>",ww("Group_".$rr->GroupName),"</td>" ;
		echo "<td>",LinkWithUsername($rr->Username),"</td><td>" ;
		if ($rr->Comment>0) echo FindTrad($rr->Comment);
		echo "</td>\n" ;
		echo "<td>" ;
		echo "<form method=post>" ;
		echo "<input type=hidden name=action value=accept>" ;
		echo "<input type=hidden name=IdMembership value=",$rr->IdMembership,">" ;
		echo "<input type=submit name=submit value=accept>" ;
		echo "</form> " ;
		echo "<form method=post>" ;
		echo "<input type=hidden name=action value=Kicked>" ;
		echo "<input type=hidden name=IdMembership value=",$rr->IdMembership,">" ;
		echo "<input type=submit name=submit value=Kicked>" ;
		echo "</form>" ;
		echo "</td>" ;
	}
	echo "<tr><td align=right>Total</td><td align=left>$count</td>" ;
	echo "\n</table><br>\n" ;


	if (HasRight("Group")>=10) {
	  echo "<form method=post>" ;
		echo "<input type=hidden name=action value=formcreategroup>" ;
		echo "<input type=submit name=submit value=\"create a new group\">" ;
		echo "</form>" ;
	}
	echo "</center>" ;
  include "footer.php" ;
} // end of DisplayAdminGroups($TPending,$Message)

// This function propose to create a group
function DisplayFormCreateGroups($IdGroup,$Name="",$IdParent=0,$Type="",$HasMember="",$TGroupList) {
  global $title ;
  $title="Create a groups" ;
  include "header.php" ;
  mainmenu("AdminGroups.php") ;
	
	echo "<br><center>" ;
	echo "\n<form method=post>" ;
	echo "\n<input type=hidden name=IdGroup value=$IdGroup>" ;
	echo "<table>" ;
	echo "<tr><td width=60%>Give the code name of the group as a word entry (must not exist in word previously) like<br> <b>BeatlesLover</b> or <b>BigSausageEaters</b><br>" ;
	echo "</td>" ;
	echo "<td>" ;
	echo "<input type=text " ;
	if ($Name!="") echo "readonly" ; // don't change a group name because it is connected to words
	echo " name=Name value=\"$Name\">" ;
	echo "</td>" ;
	echo "<tr><td>Give the number of the group parent of this group</b><br>1 is the value for initial groups of first level</td>" ;
	echo "<td>" ;
	echo "<input type=text name=IdParent value=\"$IdParent\">" ;
	echo "</td>" ;
	
	echo "<tr><td>Does this group has members ?</b></td>" ;
	echo "<td>" ;
	echo "\n<select name=HasMember>\n" ;
	echo "<option value=HasMember " ;
	if ($HasMember=="HasMember") echo " selected " ;
	echo ">HasMember</option>\n" ;
	echo "<option value=HasNotMember " ;
	if ($HasMember=="HasNotMember") echo " selected " ;
	echo ">HasNotMember</option>\n" ;
	echo" \n</select>\n" ;
	echo "</td>\n" ;

	echo "<tr><td>Does this group is public ?</b></td>" ;
	echo "<td>" ;
	echo "\n<select name=Type>\n" ;
	echo "<option value=Public " ;
	if ($Type=="Public") echo " selected " ;
	echo ">Public</option>\n" ;
	echo "<option value=NeedAcceptance " ;
	if ($Type=="NeedAcceptance") echo " selected " ;
	echo ">NeedAcceptance</option>\n" ;
	echo" \n</select>\n" ;
	echo "</td>\n" ;
	
	if ($Name!="") {
	  echo "<tr><td>Name of the group (as members will see it)</td><td>",ww("Group_".$Name)," ",LinkEditWord("Group_".$Name),"</td>" ;
	  echo "<tr><td>Description (as members will see it)</td><td>",ww("GroupDesc_".$Name)," ",LinkEditWord("GroupDesc_".$Name),"</td>" ;
	}

	echo "\n<tr><td colspan=2 align=center>" ;
	
	if ($IdGroup!=0) echo "<input type=submit name=submit value=\"update group\">" ;
	else  echo "<input type=submit name=submit value=\"create group\">" ;
	
	echo "<input type=hidden name=action value=creategroup>" ;
	echo "</td>\n</table>\n" ;
	echo "</form>\n" ;
	echo "</center>" ;
  include "footer.php" ;
} // DisplayFormCreateGroups
