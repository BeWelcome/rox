<?php
require_once("Menus.php") ;
function DisplayAdminRights($username,$rightname,$TRights,$TRightsVol,$rright,$lastaction,$scope) {
  global $countmatch ;
  global $title ;
  $title="Right admin" ;
  include "header.php" ;
  mainmenu("adminrights.php") ;
	
	if ($lastaction!="") {
	  echo "$lastaction<br>" ;
	}
	echo "<center>" ;

	echo "</center>" ;
	echo "Your Scope is for <b>",$scope,"</b><br>"  ;
	
	
	$max=count($TRightsVol) ;
	$count=0 ;
	
	echo "<center>\n<table width=30%>\n" ;
	echo "<form method=post>" ;
	echo "<tr><td>Username</td><td><input type=text name=username value=\"",$username,"\"></td>" ;
	echo "<td rowspan=2 valign=center>" ;
  echo "<input type=hidden name=action value=find>" ;
	echo "<input type=submit name=submit value=find>" ;
	echo "</td>" ;
	echo "<tr><td>Right</td><td><input type=text name=rightname value=\"",$rightname,"\"></td>" ;
	echo "</form>" ;
	echo "</table>\n" ;
	echo "<table width=80%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $rr=$TRightsVol[$ii] ;
		$count++ ;
		echo "<form method=post>\n" ;
		echo "<input type=hidden name=IdRightVolunteer value=",$TRightsVol[$ii]->id,">" ;
		echo "<input type=hidden name=action value=update>\n" ;
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
	echo "\n<hr>\n</table><br>\n" ;
	echo "\n<table width=80%>\n" ;
	echo "<form method=post>" ;
	echo "<input type=hidden name=username value=\"",$username,"\">" ;
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
	
	

	echo "</center>" ;
  include "footer.php" ;
} // DisplayAdminRights($username,$rightname,$TRights,$TRightsVol,$rright,$lastaction,$scope) {


