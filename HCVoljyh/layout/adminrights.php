<?php
require_once("Menus_micha.php") ;
function DisplayAdminRights($username,$rightname,$rightdescription,$TRights,$TRightsVol,$rright,$lastaction,$scope) {
  global $countmatch ;
  global $title ;
  $title="Right admin" ;

  include "header_micha.php" ;
	Menu1("",$title) ; // Displays the top menu

	Menu2("adminrights.php",$title) ; // Displays the second menu


echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3>$title</h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;


echo "					<div class=\"user-content\">" ;
	
	if ($lastaction!="") {
	  echo "$lastaction<br>" ;
	}
	echo "<center>" ;

	echo "</center>" ;
	echo "Your Scope is for <b>",$scope,"</b><br>"  ;
	
	
	$max=count($TRightsVol) ;
	$count=0 ;
	
	echo "<center>\n<table width=70%>\n" ;
	echo "<form method=post>" ;
	echo "<tr><td>Username</td><td><input type=text name=username value=\"",$username,"\"></td>" ;
	if ($rightdescription!="") {
	  echo "<td rowspan=2 valign=left color=silver>" ;
		echo $rightdescription ;
	  echo "</td>" ;
	}
	echo "<td rowspan=2 valign=center>" ;
  echo "<input type=hidden name=action value=find>" ;
	echo "<input type=submit name=submit value=find>" ;
	echo "</td>" ;
	echo "<tr><td>Right</td><td>" ;
	if ($scope=="\"All\"") {
		
	  echo "\n<select name=rightname >\n" ;
	  $max=count($TRights) ;
		echo "<option value=\"\">-All-</option>\n" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  echo "<option value=\"".$TRights[$ii]->Name."\"" ;
			if ($TRights[$ii]->Name==$rightname) echo " selected " ;
			echo ">",$TRights[$ii]->Name ;
	    echo "</option>\n" ;
	  }
		for ($ii=0;$ii<count($tt[$ii]);$ii++) {
		  echo "<option value=\"".$tt[$ii]."\"" ;
			if ($tt[$ii]==$rightname) echo " selected " ;
			echo ">",$tt[$ii] ;
			echo "</option>\n" ;
		}
		echo "</select>\n" ;
	}
	echo "</td>" ;
	echo "</form>" ;
	echo "</table>\n" ;
	echo "<table width=80%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $rr=$TRightsVol[$ii] ;
		$count++ ;
		echo "<form method=post>\n" ;
		echo "<input type=hidden name=IdRightVolunteer value=",$TRightsVol[$ii]->id,">" ;
		echo "<input type=hidden name=action value=update>\n" ;
		if ($username=="") {
	    echo "<tr><td>",$rr->Username ;
		  echo "</td>" ;
		}
	  echo "<tr><td>Right <input type=text name=rightname readonly value=\"",$rr->rightname,"\">" ;
		echo "</td>" ;
		echo "<td>Level <input type=text name=Level value=",$rr->Level,"></td>" ;
		echo "<tr><td>scope</td><td><textarea name=Scope rows=1 cols=70>",$rr->Scope,"</textarea></td>" ;
		echo "<tr><td>Comment</td><td><textarea name=Comment rows=3 cols=70>",$rr->Comment,"</textarea></td>" ;
		echo "<td valign=center align=left>" ;
		echo "<input type=submit name=submit value=\"update\">" ;
		echo "</td>" ;
		echo "</form>" ;
		echo "<tr><td colspan=3><hr></td>" ;
	}

	if ($username!="") { // If a username is selected propose to add him a right
	  echo "\n<hr>\n</table><br>\n" ;
	  echo "\n<table width=80%>\n" ;
	  echo "<form method=post>" ;
	  echo "<input type=text readonly name=username value=\"",$username,"\">" ;
	  echo "<tr><td align=center colspan=2>Right " ;
	  $max=count($TRights) ;
	  echo "<select name=rightname>\n" ;
	  for ($ii=0;$ii<$max;$ii++) {
	    echo "<option value=\"",$TRights[$ii]->Name,"\">",$TRights[$ii]->Name,"</option>\n" ;
	  }
	  echo "</select>\n" ;
	  echo "&nbsp;&nbsp;&nbsp;Level <input type=text name=Level></td>" ;
	  echo "<td valign=center rowspan=4>" ;
    echo "<input type=hidden name=action value=add>" ;
	  echo "<input type=submit name=submit value=add>" ;
	  echo "</td>\n" ;
	  echo "<tr><td>scope</td><td><textarea name=Scope rows=1 cols=70></textarea></td>" ;
	  echo "<tr><td>Comment</td><td><textarea name=Comment rows=3 cols=70></textarea></td>\n" ;
	  echo "</form>" ;
	  echo "</table>\n" ;
	}
	
	

	echo "</center>" ;
echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ; // user-content
} // DisplayAdminRights($username,$rightname,$TRights,$TRightsVol,$rright,$lastaction,$scope) {


