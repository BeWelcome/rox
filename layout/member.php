<?php
require_once ("menus.php");
require_once ("profilepage_header.php");

function DisplayMember($m, $profilewarning = "", $TGroups,$CanBeEdited=false) {
	global $title;
	$title = ww('ProfilePageFor', $m->Username);
	require_once "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php?cid=".$m->Username);

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("member.php?cid=" . $m->id, $m);

	// Prepare the $MenuAction for ShowAction()  
	$MenuAction = "";
	$MenuAction .= "          <li><a href=\"contactmember.php?cid=" . $m->id . "\">" . ww("ContactMember") . "</a></li>\n";
	$MenuAction .= "          <li><a href=\"addcomments.php?cid=" . $m->id . "\">" . ww("addcomments") . "</a></li>\n";
	$MenuAction .= "          <li><a href=\"todo.php\">".ww("ViewForumPosts")."</a></li>\n";


	if (HasRight("Logs")) {
		$MenuAction .= "          <li><a href=\"admin/adminlogs.php?Username=" . $m->Username . "\">see logs</a> </li>\n";
	}
	if ($CanBeEdited) {
		$MenuAction .= "          <li><a href=\"editmyprofile.php?cid=" . $m->id . "\">".ww("TranslateProfileIn",LanguageName($_SESSION["IdLanguage"]))." ".FlagLanguage(-1,$title="Translate this profile")."</a> </li>\n";
	}
	if (HasRight("Admin")) {
		$MenuAction .= "          <li><a href=\"editmyprofile.php?cid=" . $m->id . "\">Edit this profile</a> </li>\n";
	}

	if (GetPreference("PreferenceAdvanced")=="Yes") {
      if ($m->IdContact==0) {
	   	  $MenuAction .= "        <li><a href=\"mycontacts.php?IdContact=" . $m->id . "&action=add\">".ww("AddToMyNotes")."</a> </li>\n";
	   }
	   else {
	   	  $MenuAction .= "        <li><a href=\"mycontacts.php?IdContact=" . $m->id . "&action=view\">".ww("ViewMyNotesForThisMember")."</a> </li>\n";
	   }
	}

	if (GetPreference("PreferenceAdvanced")=="Yes") {
      if ($m->IdRelation==0) {
	   	  $MenuAction .= "        <li><a href=\"myrelations.php?IdRelation=" . $m->id . "&action=add\">".ww("AddToMyRelations")."</a> </li>\n";
	   }
	   else {
	   		$MenuAction .= "        <li><a href=\"myrelations.php?IdRelation=" . $m->id . "&action=view\">".ww("ViewMyRelationForThisMember")."</a> </li>\n";
	   }
	}

		
	if (HasRight("Admin")) {
		$MenuAction .= "            <li><a href=\"updatemandatory.php?cid=" . $m->id . "\">update mandatory</a> </li>\n";
		$MenuAction .= "            <li><a href=\"myvisitors.php?cid=" . $m->id . "\">view visits</a> </li>\n";
		$MenuAction .= "            <li><a href=\"admin/adminrights.php?username=" . $m->Username . "\">Rights</a> </li>\n";
	}
	if (HasRight("Flags")) $MenuAction .= "<li><a href=\"admin/adminflags.php?username=" . $m->Username . "\">Flags</a> </li>\n";
	
	ShowActions($MenuAction); // Show the Actions
	ShowAds(); // Show the Ads

	// open col3 (middle column)
	echo "    <div id=\"col3\"> \n"; 
	echo "      <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	// user content
	// About Me (Profile Summary)
	echo "        <div class=\"info\">\n";
	if ($m->ProfileSummary > 0) {
		echo "        <h3 class=\"icon info22\">", strtoupper(ww('ProfileSummary')), "</h3>\n";
		echo "        <p>",  FindTrad($m->ProfileSummary,true), "</p>\n";
	}
	$max = count($m->TLanguages);
	if ($max > 0) {
		echo "            <h4>", ww("Languages"), "</h4>\n";
		echo "            <p>";
		for ($ii = 0; $ii < $max; $ii++) {
			if ($ii > 0)
				echo ", ";
			echo $m->TLanguages[$ii]->Name, " (", $m->TLanguages[$ii]->Level, ")";
		}
		echo "          </p>\n";
	}	

	/* motivation is obsolete
	if ($m->MotivationForHospitality != "") {
		echo "          <strong>", strtoupper(ww('MotivationForHospitality')), "</strong>\n";
		echo "          <p>", $m->MotivationForHospitality, "</p>\n";
	}
	*/

	if ($m->Offer != "") {
		echo "          <strong>", strtoupper(ww('ProfileOffer')), "</strong>\n";
		echo "          <p>", $m->Offer, "</p>\n";
	}

	if ($m->IdGettingThere != "") {
		echo "          <strong>", strtoupper(ww('GettingHere')), "</strong>\n";
		echo "        <  p>", $m->GettingThere, "</p>\n";
	}
	echo "        </div>\n"; // end info

  /* special relation should be in col1 (left column) -> function ShowActions needs to be changed for this 
  $Relations=$m->Relations;
	$iiMax=count($Relations);
	if ($iiMax>0) { // if member has declared confirmed relation
	   echo "        <div class=\"info\">\n";
	   echo "        <strong>", ww('MyRelations'), "</strong>ņ";
	   echo "        <table>\n";
	   for ($ii=0;$ii<$iiMax;$ii++) {
		  echo "          <tr><td valign=center>", LinkWithPicture($Relations[$ii]->Username,$Relations[$ii]->photo),"<br>",LinkWithUsername($Relations[$ii]->Username),"</td>";
		  echo "              <td valign=center>",$Relations[$ii]->Comment,"</td>\n";
	   }
	   echo "        </table>\n";
	   echo "					</div>\n"; // end info
	} // end if member has declared confirmed relation
  */

	// Hobbies & Interests
	echo "\n";
	echo "        <div class=\"info highlight\">\n";
	echo "          <h3 class=\"icon sun22\">", ww("ProfileInterests"), "</h3>\n";
	echo "            <div class=\"subcolumns\">\n";
  echo "              <div class=\"c50l\">\n";
  echo "                <div class=\"subcl\">\n";
	echo "                  <h4>", ww("Hobbies"), "</h4>\n";
	echo "                  <p>", $m->Hobbies, "</p>\n";
	echo "                  <h4>", ww("Books"), "</h4>\n";
	echo "                  <p>", $m->Books, "</p>\n";
	echo "                </div>\n";
	echo "              </div>\n";
  echo "              <div class=\"c503\">\n";
  echo "                <div class=\"subcl\">\n";		
	echo "                  <h4>", ww("Music"), "</h4>\n";
	echo "                  <p>", $m->Music, "</p>\n";
	echo "                  <h4>", ww("Movies"), "</h4>\n";
	echo "                  <p>", $m->Movies, "</p>\n";
	echo "                </div>\n";
	echo "              </div>\n";
	echo "            </div>\n";
	echo "          <h4>", ww("ProfileOrganizations"), "</h4>\n";
	echo "          <p>", $m->Organizations, "</p>\n";
	echo "        </div>\n";	
	
	// Travel Experience
	echo "\n";
	echo "        <div class=\"info\">\n";
	echo "          <h3 class=\"icon world22\">", ww("ProfileTravelExperience"), "</h3>\n";
	echo "          <h4>", ww("PastTrips"), "</h4>\n";
	echo "          <p>", $m->PastTrips, "</p>\n";
	echo "          <h4>", ww("PlannedTrips"), "</h4>\n";
	echo "          <p>", $m->PlannedTrips, "</p>\n";	
	echo "        </div>\n";	
	
	// My Groups
	echo "\n";
	echo "        <div class=\"info highlight\">\n";
	echo "            <h3 class=\"icon groups22\">", ww("ProfileGroups"), "</h3>\n";
	$max = count($TGroups);
	if ($max > 0) {
		//    echo "<h3>",ww("xxBelongsToTheGroups",$m->Username),"</h3>";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "<h4><a href=\"groups.php?action=ShowMembers&IdGroup=", $TGroups[$ii]->IdGroup, "\">", ww("Group_" . $TGroups[$ii]->Name), "</a></h4>";
			if ($TGroups[$ii]->Comment > 0)
				echo "<p>", FindTrad($TGroups[$ii]->Comment,true), "</p>\n";
		}
	}	
	echo "        </div>\n";		

	// Profile Accomodation
	echo "\n";
	echo "        <div class=\"info\">\n";
	echo "          <h3 class=\"icon accommodation22\">", ww("ProfileAccomodation"), "</h3>\n";
	echo "          <ul class=\"information\">\n";
	echo "            <li class=\"label\">", ww("ProfileNumberOfGuests"), "</li>\n";
	echo "            <li>", $m->MaxGuest, "</li>\n";
	if ($m->MaxLenghtOfStay != "") {
		echo "            <li class=\"label\">", ww("ProfileMaxLenghtOfStay"), "</li>\n";
		echo "            <li>", $m->MaxLenghtOfStay, "</li>\n";
	}
	if ($m->ILiveWith != "") {
		echo "            <li class=\"label\">", ww("ProfileILiveWith"), "</li>\n";
		echo "            <li>", $m->ILiveWith, "</li>\n";
	}
	if (($m->AdditionalAccomodationInfo != "") or ($m->InformationToGuest != "")) {
	  echo "            <li class=\"label\"> ", ww('OtherInfosForGuest'), "</li>\n";
		if ($m->AdditionalAccomodationInfo != "")
			echo "            <li>", $m->AdditionalAccomodationInfo, "</li>\n";
		if ($m->InformationToGuest != "")
			echo "            <li>", $m->InformationToGuest, "</li>\n";  
		  echo "          </ul>\n";
	}

	$max = count($m->TabRestrictions);
	if (($max > 0) or ($m->OtherRestrictions != "")) {
		echo "          <h4>", strtoupper(ww('ProfileRestrictionForGuest')), "</h4>\n";
		echo "          <ul>\n";
		if ($max > 0) {
			for ($ii = 0; $ii < $max; $ii++) {
				echo "            <li>", ww("Restriction_" . $m->TabRestrictions[$ii]), "</li>\n";
			}
		}

		if ($m->OtherRestrictions != "")
			echo "              <li>", $m->OtherRestrictions, "</li>\n";
		echo "          </ul>\n";
	}
  echo "        </div>\n";	
	
	// Contact Info
	echo "\n";
	echo "        <div class=\"info highlight\"> \n";
	echo "          <h3 class=\"icon contact22\">".ww("ContactInfo")."</h3>\n";
	echo "          <div class=\"subcolumns\">\n";
	echo "            <div class=\"c50l\">\n";
  echo "              <div class=\"subcl\">\n";
	echo "                <ul>\n"; 
	echo "                  <li class=\"label\">", ww('Name'), "</li>\n";
	echo "                  <li>", $m->FullName, "</li>\n";
	echo "                </ul>\n";
	echo "                <ul>\n";
	echo "                  <li class=\"label\">", ww("Address"), "</li>\n";
	echo "                  <li>", $m->Address, "</li>\n";
	echo "                  <li>", $m->Zip," ", $m->cityname, "</li>\n";
	echo "                  <li>", $m->regionname, "</li>\n";
	echo "                  <li>", $m->countryname, "</li>\n";
	echo "                </ul>\n";
	if (!empty($m->DisplayHomePhoneNumber) or 
		!empty($m->DisplayCellPhoneNumber) or 
		!empty($m->DisplayWorkPhoneNumber)) {
		echo "                <ul>\n";
		echo "                  <li class=\"label\">", ww("ProfilePhone"), "</li>\n";
		if (!empty($m->DisplayHomePhoneNumber))
			echo "                  <li>", ww("ProfileHomePhoneNumber"), ": ", $m->DisplayHomePhoneNumber, "</li>\n";
		if (!empty($m->DisplayCellPhoneNumber))
			echo "                  <li>", ww("ProfileCellPhoneNumber"), ": ", $m->DisplayCellPhoneNumber, "</li>\n";
		if (!empty($m->DisplayWorkPhoneNumber))
			echo "                  <li>", ww("ProfileWorkPhoneNumber"), ": ", $m->DisplayWorkPhoneNumber, "</li>\n";
		echo "                </ul>\n";
	}

	echo "              </div>\n"; //end subcl
	echo "            </div>\n"; // end c50l
	echo "            <div class=\"c50r\">\n";
	echo "              <div class=\"subcr\">\n";
	echo "                <ul>\n";
	echo "                  <li class=\"label\">Messenger</li>\n";
	if ($m->chat_SKYPE != 0)
		echo "                  <li class=\"icon skype\">Skype: ", PublicReadCrypted($m->chat_SKYPE, ww("Hidden")), "</li>\n";
	if ($m->chat_ICQ != 0)
		echo "						      <li class=\"icon icq\">ICQ: ", PublicReadCrypted($m->chat_ICQ, ww("Hidden")), "</li>\n";
	if ($m->chat_AOL != 0)
		echo "				  	      <li class=\"icon aol\">AOL: ", PublicReadCrypted($m->chat_AOL, ww("Hidden")), "</li>\n";
	if ($m->chat_MSN != 0)
		echo "                  <li class=\"icon msn\">MSN: ", PublicReadCrypted($m->chat_MSN, ww("Hidden")), "</li>\n";
	if ($m->chat_YAHOO != 0)
		echo "                  <li class=\"icon yahoo\">Yahoo: ", PublicReadCrypted($m->chat_YAHOO, ww("Hidden")), "</li>\n";
	if ($m->chat_GOOGLE != 0)
		echo "                  <li class=\"icon google\">GoogleTalk: ", PublicReadCrypted($m->chat_GOOGLE, ww("Hidden")), "</li>\n";	
	if ($m->chat_Others != 0)
		echo "                  <li>", ww("chat_others"), ": ", PublicReadCrypted($m->chat_Others, ww("Hidden")), "</li>\n";
	echo "                </ul>\n";
	if ($m->WebSite != "") {
		echo "              <ul>\n";
		echo "                <li class=\"label\">", ww("Website"), "</li>\n";
		echo "                <li><a href=\"", $m->WebSite, "\">", $m->WebSite, "</a></li>\n";
		echo "              </ul>\n";
	} // end if there is WebSite
	echo "              </div>\n"; // end subcr
	echo "            </div>\n"; // end c50r
	echo "          </div>\n"; // end subcolumns
  echo "        </div>\n"; // end info highlight
require_once "footer.php";

}
?>
