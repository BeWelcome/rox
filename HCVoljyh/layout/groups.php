<?php
require_once("Menus.php") ;

// Display the group list without hierarchy
function DisplayGroupList($TGroup) {
  global $title ;
  $title=ww('GroupsList') ;
  include "header.php" ;

  mainmenu("groups.php",ww('Groups')) ;
  echo "\n <br><center>\n" ;
  echo "<form method=post><table>\n" ;
	echo "<input type=hidden name=cid value=$IdMember>" ;
	echo "<input type=hidden name=action value=update>" ;

	$iiMax=count($TGroup) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "<tr><td>" ;
    echo ww("Group_".$TGroup[$ii]->Name) ;
    echo "</td>" ;
    echo "<td>" ;
    echo ww("GroupDesc_".$TGroup[$ii]->Name) ;
    echo "</td>" ;
  }
	echo "\n<tr><td align=center colspan=3><input type=submit name=submit></td>";
  
  echo "</table>\n" ;
  echo "</form>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
} // end of DisplayGroupList($TGroup)

// This display the subscription for for a group
function DisplayDispSubscrForm($TGroup) {
  global $title ;
  $title=ww("SubscribeToGroup",ww("Group_".$TGroup->Name)) ;
  include "header.php" ;

  mainmenu("groups.php",ww('Groups')) ;
  echo "\n <br><center><form><table>\n" ;
	echo "<input type=hidden name=action value=Add>" ;
	echo "<input type=hidden name=IdGroup value=".$TGroup->id.">\n" ;
	if ($TGroup->Type=="NeedAcceptance") {
	  $intro=ww("ThisGroupNeedAcceptance",$TGroup->Name) ;
	}
	else {
	  $intro=ww("ThisGroupDontNeedAcceptance",$TGroup->Name) ;
	}
  echo "<tr><td colspan=2>" ;
  echo ww("GroupDesc_".$TGroup->Name) ;
  echo "</td>" ;
	echo "<tr><td colspan=2>",$intro,"</td>\n" ;
	echo "<tr><td>",ww("ExplayWhyToBeIn",$TGroup->Name),"</td><td><textarea name=Comment cols=70 rows=7></textarea></td>\n" ;
	echo "<tr><td colspan=2 align=center><input type=submit name=submit value=submit></td>" ;
  echo "</table>\n" ;
  echo "</form>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
} // end of DisplayDispSubscrForm


// This display the members in a group
function DisplayGroupMembers($TGroup,$TMembers) {
  global $title ;
  $title=ww("GroupsListFor",ww("Group_".$TGroup->Name)) ;
  include "header.php" ;

  mainmenu("groups.php",ww('Groups')) ;
  echo "\n <br><center><table>\n" ;
  echo "<tr><td>" ;
  echo "<b>",ww("Group_".$TGroup->Name),"</b>" ;
  echo "</td>" ;
  echo "<td>" ;
  echo ww("GroupDesc_".$TGroup->Name) ;
  echo "</td>" ;
	$iiMax=count($TMembers) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
	  echo "<tr><td>" ;
		echo LinkWithUsername($TMembers[$ii]->Username) ;
		echo "</td>" ;
	  echo "<td>" ;
		echo FindTrad($TMembers[$ii]->GroupComment) ;
		echo "</td>" ;
  }

	echo "<tr><td colspan=2 align=center>" ;
  echo "<form method=post>\n" ;
  echo "\n<form style=\"display:inline\"><input type=hidden name=action value=ShowJoinGroup>\n<input type=hidden name=IdGroup value=".$TGroup->IdGroup.">" ;
	echo "<input type=submit value=\"".ww("jointhisgroup")."\"></form>" ;
  echo "</form>\n" ;
	echo "</td>" ;
  
  echo "</table>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
} // end of DisplayGroupMembers($TGroup,$TList)


// Display the group list with its hierarchy
function DisplayGroupHierarchyList($TGroup) {
  global $title ;
  $title=ww('GroupsList') ;
  include "header.php" ;

  mainmenu("groups.php",ww('Groups')) ;
  echo "\n <br><center>\n" ;
  echo "<form method=post><table>\n" ;
	echo "<input type=hidden name=cid value=$IdMember>" ;
	echo "<input type=hidden name=action value=update>" ;

	$iiMax=count($TGroup) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "<tr valign=center><td>" ;
		for ($jj=0;$jj<$TGroup[$ii]->Depht;$jj++) { // indent according to depht
		  echo "&nbsp;&nbsp;" ;
		}
    echo ww("Group_".$TGroup[$ii]->Name) ;
    echo "</td>" ;
    echo "<td>" ;
//		echo "(",$TGroup[$ii]->NbChilds," sub groups) " ;
		echo "</td>\n";
    echo "<td>" ;
		if ($TGroup[$ii]->HasMembers=='HasMember') {
      echo "\n<form style=\"display:inline\"><input type=hidden name=action value=ShowMembers>\n<input type=hidden name=IdGroup value=".$TGroup[$ii]->IdGroup.">" ;
		  echo "<input type=submit value=\"".ww("viewthisgroup")." (".$TGroup[$ii]->NbMembers.")\"></form>" ;
      echo "\n<form style=\"display:inline\"><input type=hidden name=action value=ShowJoinGroup>\n<input type=hidden name=IdGroup value=".$TGroup[$ii]->IdGroup.">" ;
		  // todo not display join this group if member is already in
		  echo "<input type=submit value=\"".ww("jointhisgroup")."\"></form>" ;
		}
    echo "</td>" ;
  }
//	echo "\n<tr><td align=center colspan=3><input type=submit name=submit></td>";
  
  echo "</table>\n" ;
  echo "</form>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
} // DisplayGroupHierarchyList
?>
