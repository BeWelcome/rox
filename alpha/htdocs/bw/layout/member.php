<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/



/**
 * Contains layout functions for the profile page
 *
 * @package Messaging
 * @author JY (PHP) and globetrotter_tt (layout)
 */

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
	$IdMember = $m->id;
	// Prepare the $MenuAction for ShowAction()  
    if ($_SESSION["IdMember"] == $IdMember) {
        $MenuAction = "<li><a href=\"mypreferences.php?cid=" . $m->id . "\">" . ww("MyPreferences") . "</a></li>\n";
        $MenuAction .= "<li><a href=\"editmyprofile.php\">" . ww("EditMyProfile") . "</a></li>\n";        
    }
    else {
        $MenuAction = "          <li class=\"icon contactmember16\"><a href=\"contactmember.php?cid=" . $m->id . "\">" . ww("ContactMember") . "</a></li>\n";
        $MenuAction .= "          <li class=\"icon addcomment16\"><a href=\"addcomments.php?cid=" . $m->id . "\">" . ww("addcomments") . "</a></li>\n";
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
    }
	// Please don't link to todo.php for normal members!
	//$MenuAction .= "          <li class=\"icon forumpost16\"><a href=\"todo.php\">".ww("ViewForumPosts")."</a></li>\n";

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

	// open col3 (middle column)
	echo "    <div id=\"col3\"> \n"; 
	echo "      <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	// user content
	// About Me (Profile Summary)
	echo "        <div class=\"info\">\n";
	
/**
 * ToDo: quickinfo box for profile
 *  echo "          <div id=\"quickinfo\" class=\"highlight\">\n";
 *  echo "            <ul class=\"information floatbox\">\n";
 *  echo "              <li class=\"label\">Member since:</li>\n";
 *	echo "              <li>$m->created</li>\n";
 *	echo "              <li class=\"label\">",ww("Lastlogin"),":</li>\n";
 *	echo "              <li>",$m->LastLogin,"</li>\n";
 *  echo "              <li class=\"label\">", ww("ProfileNumberOfGuests"),":</li>\n";
 *  echo "              <li>", $m->MaxGuest,"</li>\n";
 *  echo "              <li></li>\n";
 *  echo "              <li><img src=\"./images/no-smoking.png\" width=\"32\" height=\"32\"  alt=\"no-smoking\" />\n";
 *  echo "                  <img src=\"./images/no-alcohol.png\" width=\"32\" height=\"32\"  alt=\"no-alcohol\" />\n";
 *  echo "                  <img src=\"./images/no-pets.png\" width=\"32\" height=\"32\"  alt=\"no-pets\" /></li>\n";
 *  echo "            </ul>\n";
 *  echo "          </div>\n";
*/ 
 	
	if ($m->ProfileSummary > 0) {
		echo "          <h3 class=\"icon info22\">", ww('ProfileSummary'), "</h3>\n";
		echo "          <p>",  FindTrad($m->ProfileSummary,true), "</p>\n";
	}
	$max = count($m->TLanguages);
	if ($max > 0) {
		echo "          <h4>", ww("Languages"), "</h4>\n";
		echo "          <p>";
		for ($ii = 0; $ii < $max; $ii++) {
			if ($ii > 0)
				echo ", ";
			echo $m->TLanguages[$ii]->Name, " (", $m->TLanguages[$ii]->Level, ")";
		}
		echo "          </p>\n";
	}	

	if ($m->Offer != "") {
		echo "          <strong>", strtoupper(ww('ProfileOffer')), "</strong>\n";
		echo "          <p>", $m->Offer, "</p>\n";
	}

	if ($m->IdGettingThere != "") {
		echo "          <strong>", strtoupper(ww('GettingHere')), "</strong>\n";
		echo "        <  p>", $m->GettingThere, "</p>\n";
	}
	echo "        </div>\n"; // end info

/** special relation should be in col1 (left column) -> function ShowActions needs to be changed for this 
  * $Relations=$m->Relations;
  *	$iiMax=count($Relations);
  *	if ($iiMax>0) { // if member has declared confirmed relation
  *	   echo "        <div class=\"info\">\n";
  *	   echo "        <strong>", ww('MyRelations'), "</strong>ņ";
  *	   echo "        <table>\n";
  *	   for ($ii=0;$ii<$iiMax;$ii++) {
  *		  echo "          <tr><td valign=center>", LinkWithPicture($Relations[$ii]->Username,$Relations[$ii]->photo),"<br>",LinkWithUsername($Relations[$ii]->Username),"</td>";
  *		  echo "              <td valign=center>",$Relations[$ii]->Comment,"</td>\n";
  *	   }
  *	   echo "        </table>\n";
  *	   echo "					</div>\n"; // end info
  *	} // end if member has declared confirmed relation
*/

	// Hobbies & Interests
	echo "\n";
	echo "        <div class=\"info highlight\">\n";
	echo "          <h3 class=\"icon sun22\">", ww("ProfileInterests"), "</h3>\n";
	echo "            <div class=\"subcolumns\">\n";
  echo "              <div class=\"c50l\">\n";
  echo "                <div class=\"subcl\">\n";
	if ($m->Hobbies != "") {
	echo "                  <h4>", ww("ProfileHobbies"), "</h4>\n";
	echo "                  <p>", $m->Hobbies, "</p>\n";
	}
	if ($m->Books != "") {
	echo "                  <h4>", ww("ProfileBooks"), "</h4>\n";
	echo "                  <p>", $m->Books, "</p>\n";
	}
	echo "                </div>\n";
	echo "              </div>\n";
  echo "              <div class=\"c503\">\n";
  echo "                <div class=\"subcl\">\n";
  if ($m->Music != "") {		
	echo "                  <h4>", ww("ProfileMusic"), "</h4>\n";
	echo "                  <p>", $m->Music, "</p>\n";
	}
	if ($m->Music != "") {
	echo "                  <h4>", ww("ProfileMovies"), "</h4>\n";
	echo "                  <p>", $m->Movies, "</p>\n";
	}
	echo "                </div>\n";
	echo "              </div>\n";
	echo "            </div>\n";
	if ($m->Organizations != "") {
	echo "          <h4>", ww("ProfileOrganizations"), "</h4>\n";
	echo "          <p>", $m->Organizations, "</p>\n";
	}
	echo "        </div>\n";	
	
	// Travel Experience
	echo "\n";
	echo "        <div class=\"info\">\n";
	echo "          <h3 class=\"icon world22\">", ww("ProfileTravelExperience"), "</h3>\n";
	if ($m->PastTrips != "") {
	echo "          <h4>", ww("ProfilePastTrips"), "</h4>\n";
	echo "          <p>", $m->PastTrips, "</p>\n";
	}
	if ($m->PlannedTrips != "") {
	echo "          <h4>", ww("ProfilePlannedTrips"), "</h4>\n";
	echo "          <p>", $m->PlannedTrips, "</p>\n";
	}	
	echo "        </div>\n";	
	
	// My Groups
	echo "\n";
	echo "        <div class=\"info highlight\">\n";
	echo "            <h3 class=\"icon groups22\">", ww("ProfileGroups"), "</h3>\n";
	$max = count($TGroups);
	if ($max > 0) {
		//    echo "<h3>",ww("xxBelongsToTheGroups",$m->Username),"</h3>";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "<h4><a href=\"groups.php?action=ShowMembers&amp;IdGroup=", $TGroups[$ii]->IdGroup, "\">", ww("Group_" . $TGroups[$ii]->Name), "</a></h4>";
			if ($TGroups[$ii]->Comment > 0)
				echo "<p>", FindTrad($TGroups[$ii]->Comment,true), "</p>\n";
		}
	}	
	echo "        </div>\n";		

	// Profile Accomodation
	echo "\n";
	echo "        <div class=\"info\">\n";
	echo "          <h3 class=\"icon accommodation22\">", ww("ProfileAccomodation"), "</h3>\n";
	echo "          <table id=\"accommodation\">\n";
  echo "          <colgroup>\n";
  echo "            <col width=\"35%\" />\n";
  echo "            <col width=\"65%\" />\n";
  echo "           </colgroup>\n";
	echo "            <tr align=\"left\">\n";
	echo "              <td class=\"label\">", ww("ProfileNumberOfGuests"), ":</td>\n";
	echo "              <td>", $m->MaxGuest, "</td>\n";
	echo "            </tr>\n";
	if ($m->MaxLenghtOfStay != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", ww("ProfileMaxLenghtOfStay"), ":</td>\n";
		echo "              <td>", $m->MaxLenghtOfStay, "</td>\n";
		echo "            </tr>\n";
	}
	if ($m->ILiveWith != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", ww("ProfileILiveWith"), ":</td>\n";
		echo "              <td>", $m->ILiveWith, "</td>\n";
		echo "            </tr>\n";
	}
	if ($m->PleaseBring != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", ww("ProfilePleaseBring"), ":</td>\n";
		echo "              <td>", $m->PleaseBring, "</td>\n";
		echo "            </tr>\n";
	}
	if ($m->OfferGuests != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", ww("ProfileOfferGuests"), ":</td>\n";
		echo "              <td>", $m->OfferGuests, "</td>\n";
		echo "            </tr>\n";
	}
	if ($m->OfferHosts != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", ww("ProfileOfferHosts"), ":</td>\n";
		echo "              <td>", $m->OfferHosts, "</td>\n";
		echo "            </tr>\n";
	}	
	if ($m->PublicTransport != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", ww("ProfilePublicTransport"), ":</td>\n";
		echo "              <td>", $m->PublicTransport, "</td>\n";
		echo "            </tr>\n";
	}			

	if (($m->AdditionalAccomodationInfo != "") or ($m->InformationToGuest != "")) {
	  echo "            <tr align=\"left\">\n";
	  echo "              <td class=\"label\"> ", ww('OtherInfosForGuest'), ":</td>\n";
		if ($m->AdditionalAccomodationInfo != "")
			echo "              <td>", $m->AdditionalAccomodationInfo, ":</td>\n";
		if ($m->InformationToGuest != "")
			echo "              <td>", $m->InformationToGuest, ":</td>\n"; 
		echo "            </tr>\n";	 
	}
	$max = count($m->TabRestrictions);
	if (($max > 0) or ($m->OtherRestrictions != "")) {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", ww('ProfileRestrictionForGuest'), ":</td>\n";
		if ($max > 0) {
		  echo "              <td>\n";
			for ($ii = 0; $ii < $max; $ii++) {
				echo "              ", ww("Restriction_" . $m->TabRestrictions[$ii]), ", ","\n";
			}
			echo "              </td>\n";
		}
  echo "            </tr>\n";
		if ($m->OtherRestrictions != "")
		  echo "            <tr align=\"left\">\n";
		  echo "              <td class=\"label\">", ww('ProfileOtherRestrictions'), ":</td>\n";
			echo "              <td>", $m->OtherRestrictions, "</td>\n";
	}
	echo "            </tr>\n";
	echo "          </table>\n";
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
	if (IsLoggedIn()) {
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
	} // end of (IsLoggedIn())
	else {
	echo "                <ul>\n";
	echo "<font color=red><b>*</b></font>",ww("YouNeedToBeALoggedMember") ; 
	echo "                </ul>\n"; 
	}

	echo "              </div>\n"; //end subcl
	echo "            </div>\n"; // end c50l
	echo "            <div class=\"c50r\">\n";
	echo "              <div class=\"subcr\">\n";
	echo "                <ul>\n";
	if (IsLoggedIn()) {
	echo "                  <li class=\"label\">Messenger</li>\n";
	if ($m->chat_SKYPE != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_skype.png\" width=\"16\" height=\"16\" title=\"Skype\" alt=\"Skype\" /> Skype: ", PublicReadCrypted($m->chat_SKYPE, ww("Hidden")), "</li>\n";
	if ($m->chat_ICQ != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_icq.png\" width=\"16\" height=\"16\" title=\"ICQ\" alt=\"ICQ\" /> ICQ: ", PublicReadCrypted($m->chat_ICQ, ww("Hidden")), "</li>\n";
	if ($m->chat_AOL != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_aim.png\" width=\"16\" height=\"16\" title=\"AOL\" alt=\"AOL\" /> AOL: ", PublicReadCrypted($m->chat_AOL, ww("Hidden")), "</li>\n";
	if ($m->chat_MSN != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_msn.png\" width=\"16\" height=\"16\" title=\"MSN\" alt=\"MSN\" /> MSN: ", PublicReadCrypted($m->chat_MSN, ww("Hidden")), "</li>\n";
	if ($m->chat_YAHOO != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_yahoo.png\" width=\"16\" height=\"16\" title=\"Yahoo\" alt=\"Yahoo\" /> Yahoo: ", PublicReadCrypted($m->chat_YAHOO, ww("Hidden")), "</li>\n";
	if ($m->chat_GOOGLE != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_gtalk.png\" width=\"16\" height=\"16\" title=\"Google Talk\" alt=\"Google Talk\" /> GoogleTalk: ", PublicReadCrypted($m->chat_GOOGLE, ww("Hidden")), "</li>\n";	
	if ($m->chat_Others != 0)
		echo "                  <li>", ww("chat_others"), ": ", PublicReadCrypted($m->chat_Others, ww("Hidden")), "</li>\n";
	echo "                </ul>\n";
	} // end of (IsLoggedIn())
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
