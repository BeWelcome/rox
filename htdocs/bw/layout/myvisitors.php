<?php
require_once ("menus.php");
require_once ("profilepage_header.php");

function DisplayMyVisitors($TData, $m) {
	global $title, $_SYSHCVOL;
	$title = ww('MyVisitors');
	require_once "header.php";

	Menu1(); // Displays the top menu
	Menu2("member.php?cid=".$m->Username);

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("myvisitors.php?cid=" . $m->id, $m);

	// Prepare the $MenuAction for ShowAction()  
	$MenuAction = "";
	$MenuAction .= "          <li class=\"icon contactmember16\"><a href=\"contactmember.php?cid=" . $m->id . "\">" . ww("ContactMember") . "</a></li>\n";
	$MenuAction .= "          <li class=\"icon addcomment16\"><a href=\"addcomments.php?cid=" . $m->id . "\">" . ww("addcomments") . "</a></li>\n";
	$MenuAction .= "          <li class=\"icon forumpost16\"><a href=\"todo.php\">".ww("ViewForumPosts")."</a></li>\n";

	if (GetPreference("PreferenceAdvanced")=="Yes") {
      if ($m->IdContact==0) {
	   	  $MenuAction .= "          <li class=\"icon mylist16\"><a href=\"mycontacts.php?IdContact=" . $m->id . "&amp;action=add\">".ww("AddToMyNotes")."</a> </li>\n";
	   }
	   else {
	   	  $MenuAction .= "          <li class=\"icon mylist16\"><a href=\"mycontacts.php?IdContact=" . $m->id . "&amp;action=view\">".ww("ViewMyNotesForThisMember")."</a> </li>\n";
	   }
	}

	if (GetPreference("PreferenceAdvanced")=="Yes") {
      if ($m->IdRelation==0) {
	   	  $MenuAction .= "        <li class=\"icon myrelations16\"><a href=\"myrelations.php?IdRelation=" . $m->id . "&amp;action=add\">".ww("AddToMyRelations")."</a> </li>\n";
	   }
	   else {
	   		$MenuAction .= "        <li class=\"icon myrelations16\"><a href=\"myrelations.php?IdRelation=" . $m->id . "&amp;action=view\">".ww("ViewMyRelationForThisMember")."</a> </li>\n";
	   }
	}

	if ($CanBeEdited) {
		$MenuAction .= "          <li><a href=\"editmyprofile.php?cid=" . $m->id . "\">".ww("TranslateProfileIn",LanguageName($_SESSION["IdLanguage"]))." ".FlagLanguage(-1,$title="Translate this profile")."</a> </li>\n";
	}

	$VolAction=ProfileVolunteerMenu($m); // This will receive the possible vol action for this member
	
	$SpecialRelation="" ;
//special relation should be in col1 (left column) -> function ShowActions needs to be changed for this 
  $Relations=$m->Relations;
  $iiMax=count($Relations);
  if ($iiMax>0) { // if member has declared confirmed relation
     for ($ii=0;$ii<$iiMax;$ii++) {
	  	  $SpecialRelation=$SpecialRelation."<li>". LinkWithPicture($Relations[$ii]->Username,$Relations[$ii]->photo)."<br>".LinkWithUsername($Relations[$ii]->Username);
	  	  $SpecialRelation=$SpecialRelation."<br>".$Relations[$ii]->Comment."</li>\n" ;
  	  }
  } // end if member has declared confirmed relation	
	
	ShowLeftColumn($MenuAction,$VolAction,$SpecialRelation); // Show the Actions
	ShowAds(); // Show the Ads
	
	// col3 (middle column)
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "  			<div class=\"info\">";

	$iiMax = count($TData);
	echo "<table>";
	if ($iiMax == 0) {
		echo "<tr><td align=center>", ww("NobodyHasYetVisitatedThisProfile"), "</td>";
	}
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$rr = $TData[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=center>";
//		if (($rr->photo != "") and ($rr->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
			echo LinkWithPicture($rr->Username,$rr->photo),"\n";
			echo "</div>";
//		}
		echo  LinkWithUsername($rr->Username), "</td>";
		echo " <td valign=center>", $rr->countryname, "</td> ";
		echo "<td valign=center>";
//		if ($rr->ProfileSummary > 0)
			echo $rr->ProfileSummary;

		echo "</td>";
		echo "<td>";
		echo $rr->datevisite;
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";

	require_once "footer.php";

}
?>
