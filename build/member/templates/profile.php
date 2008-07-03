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
$words = new MOD_words();

	// user content
	// About Me (Profile Summary)
	echo "        <div class=\"info\">\n";
 	
	if ($m->ProfileSummary > 0) {
		echo "          <h3 class=\"icon info22\">", $words->get('ProfileSummary'), "</h3>\n";
		echo "          <p>",  $words->mTrad($m->ProfileSummary,true), "</p>\n";
	}
	$max = count($m->TLanguages);
	if ($max > 0) {
		echo "          <h4>", $words->get('Languages'), "</h4>\n";
		echo "          <p>";
		for ($ii = 0; $ii < $max; $ii++) {
			if ($ii > 0)
				echo ", ";
			echo $m->TLanguages[$ii]->Name, " (", $m->TLanguages[$ii]->Level, ")";
		}
		echo "          </p>\n";
	}	

	if ($m->Offer != "") {
		echo "          <strong>", $words->get('ProfileOffer') , "</strong>\n";
		echo "          <p>", $m->Offer, "</p>\n";
	}

	if (isset($m->IdGettingThere) && $m->IdGettingThere != "") {
		echo "          <strong>", strtoupper($words->get('GettingHere')), "</strong>\n";
		echo "        <  p>", $m->GettingThere, "</p>\n";
	}
	echo "        </div>\n"; // end info

/** special relation should be in col1 (left column) -> function ShowActions needs to be changed for this 
  * $Relations=$m->Relations;
  *	$iiMax=count($Relations);
  *	if ($iiMax>0) { // if member has declared confirmed relation
  *	   echo "        <div class=\"info\">\n";
  *	   echo "        <strong>", $words->get('MyRelations'), "</strong>ņ";
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
	echo "          <h3 class=\"icon sun22\">", $words->get('ProfileInterests'), "</h3>\n";
	echo "            <div class=\"subcolumns\">\n";
  echo "              <div class=\"c50l\">\n";
  echo "                <div class=\"subcl\">\n";
	if ($m->Hobbies != "") {
	echo "                  <h4>", $words->get('ProfileHobbies'), "</h4>\n";
	echo "                  <p>", $m->Hobbies, "</p>\n";
	}
	if ($m->Books != "") {
	echo "                  <h4>", $words->get('ProfileBooks'), "</h4>\n";
	echo "                  <p>", $m->Books, "</p>\n";
	}
	echo "                </div>\n";
	echo "              </div>\n";
  echo "              <div class=\"c503\">\n";
  echo "                <div class=\"subcl\">\n";
  if ($m->Music != "") {		
	echo "                  <h4>", $words->get('ProfileMusic'), "</h4>\n";
	echo "                  <p>", $m->Music, "</p>\n";
	}
	if ($m->Music != "") {
	echo "                  <h4>", $words->get('ProfileMovies'), "</h4>\n";
	echo "                  <p>", $m->Movies, "</p>\n";
	}
	echo "                </div>\n";
	echo "              </div>\n";
	echo "            </div>\n";
	if ($m->Organizations != "") {
	echo "          <h4>", $words->get('ProfileOrganizations'), "</h4>\n";
	echo "          <p>", $m->Organizations, "</p>\n";
	}
	echo "        </div>\n";	
	
	// Travel Experience
	echo "\n";
	echo "        <div class=\"info\">\n";
	echo "          <h3 class=\"icon world22\">", $words->get('ProfileTravelExperience'), "</h3>\n";
	if ($m->PastTrips != "") {
	echo "          <h4>", $words->get('ProfilePastTrips'), "</h4>\n";
	echo "          <p>", $m->PastTrips, "</p>\n";
	}
	if ($m->PlannedTrips != "") {
	echo "          <h4>", $words->get('ProfilePlannedTrips'), "</h4>\n";
	echo "          <p>", $m->PlannedTrips, "</p>\n";
	}	
	echo "        </div>\n";	
	
	// My Groups
	echo "\n";
	echo "        <div class=\"info highlight\">\n";
	echo "            <h3 class=\"icon groups22\">", $words->get('ProfileGroups'), "</h3>\n";
	$max = count($TGroups);
	if ($max > 0) {
		//    echo "<h3>",$words->get('xxBelongsToTheGroups",$m->Username),"</h3>";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "<h4><a href=\"groups.php?action=ShowMembers&amp;IdGroup=", $TGroups[$ii]->IdGroup, "\">", $words->get('Group_' . $TGroups[$ii]->Name), "</a></h4>";
			if ($TGroups[$ii]->Comment > 0)
				echo "<p>", $words->mTrad($TGroups[$ii]->Comment,true), "</p>\n";
		}
	}	
	echo "        </div>\n";		

	// Profile Accomodation
	echo "\n";
	echo "        <div class=\"info\">\n";
	echo "          <h3 class=\"icon accommodation22\">", $words->get('ProfileAccomodation'), "</h3>\n";
	echo "          <table id=\"accommodation\">\n";
  echo "          <colgroup>\n";
  echo "            <col width=\"35%\" />\n";
  echo "            <col width=\"65%\" />\n";
  echo "           </colgroup>\n";
	echo "            <tr align=\"left\">\n";
	echo "              <td class=\"label\">", $words->get('ProfileNumberOfGuests'), ":</td>\n";
	echo "              <td>", $m->MaxGuest, "</td>\n";
	echo "            </tr>\n";
	if ($m->MaxLenghtOfStay != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", $words->get('ProfileMaxLenghtOfStay'), ":</td>\n";
		echo "              <td>", $m->MaxLenghtOfStay, "</td>\n";
		echo "            </tr>\n";
	}
	if ($m->ILiveWith != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", $words->get('ProfileILiveWith'), ":</td>\n";
		echo "              <td>", $m->ILiveWith, "</td>\n";
		echo "            </tr>\n";
	}
	if ($m->PleaseBring != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", $words->get('ProfilePleaseBring'), ":</td>\n";
		echo "              <td>", $m->PleaseBring, "</td>\n";
		echo "            </tr>\n";
	}
	if ($m->OfferGuests != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", $words->get('ProfileOfferGuests'), ":</td>\n";
		echo "              <td>", $m->OfferGuests, "</td>\n";
		echo "            </tr>\n";
	}
	if ($m->OfferHosts != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", $words->get('ProfileOfferHosts'), ":</td>\n";
		echo "              <td>", $m->OfferHosts, "</td>\n";
		echo "            </tr>\n";
	}	
	if ($m->PublicTransport != "") {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", $words->get('ProfilePublicTransport'), ":</td>\n";
		echo "              <td>", $m->PublicTransport, "</td>\n";
		echo "            </tr>\n";
	}			

	if (($m->AdditionalAccomodationInfo != "") or ($m->InformationToGuest != "")) {
	  echo "            <tr align=\"left\">\n";
	  echo "              <td class=\"label\"> ", $words->get('OtherInfosForGuest'), ":</td>\n";
		if ($m->AdditionalAccomodationInfo != "")
			echo "              <td>", $m->AdditionalAccomodationInfo, ":</td>\n";
		if ($m->InformationToGuest != "")
			echo "              <td>", $m->InformationToGuest, ":</td>\n"; 
		echo "            </tr>\n";	 
	}
	$max = count($m->TabRestrictions);
	if (($max > 0) or ($m->OtherRestrictions != "")) {
	  echo "            <tr align=\"left\">\n";
		echo "              <td class=\"label\">", $words->get('ProfileRestrictionForGuest'), ":</td>\n";
		if ($max > 0) {
		  echo "              <td>\n";
			for ($ii = 0; $ii < $max; $ii++) {
				echo "              ", $words->get('Restriction_' . $m->TabRestrictions[$ii]), ", ","\n";
			}
			echo "              </td>\n";
		}
  echo "            </tr>\n";
		if ($m->OtherRestrictions != "")
		  echo "            <tr align=\"left\">\n";
		  echo "              <td class=\"label\">", $words->get('ProfileOtherRestrictions'), ":</td>\n";
			echo "              <td>", $m->OtherRestrictions, "</td>\n";
	}
	echo "            </tr>\n";
	echo "          </table>\n";
  echo "        </div>\n";	
	
	// Contact Info
	echo "\n";
	echo "        <div class=\"info highlight\"> \n";
	echo "          <h3 class=\"icon contact22\">".$words->get('ContactInfo')."</h3>\n";
	echo "          <div class=\"subcolumns\">\n";
	echo "            <div class=\"c50l\">\n";
  echo "              <div class=\"subcl\">\n";
	echo "                <ul>\n"; 
	echo "                  <li class=\"label\">", $words->get('Name'), "</li>\n";
	echo "                  <li>", $m->FullName, "</li>\n";
	echo "                </ul>\n";
    if ($User = APP_User::login()) {
	echo "                <ul>\n";
	echo "                  <li class=\"label\">", $words->get('Address'), "</li>\n";
	echo "                  <li>", $m->Address, "</li>\n";
	echo "                  <li>", $m->Zip," ", $m->cityname, "</li>\n";
	echo "                  <li>", $m->regionname, "</li>\n";
	echo "                  <li>", $m->countryname, "</li>\n";
	echo "                </ul>\n";
	if (!empty($m->DisplayHomePhoneNumber) or 
		!empty($m->DisplayCellPhoneNumber) or 
		!empty($m->DisplayWorkPhoneNumber)) {
		echo "                <ul>\n";
		echo "                  <li class=\"label\">", $words->get('ProfilePhone'), "</li>\n";
		if (!empty($m->DisplayHomePhoneNumber))
			echo "                  <li>", $words->get('ProfileHomePhoneNumber'), ": ", $m->DisplayHomePhoneNumber, "</li>\n";
		if (!empty($m->DisplayCellPhoneNumber))
			echo "                  <li>", $words->get('ProfileCellPhoneNumber'), ": ", $m->DisplayCellPhoneNumber, "</li>\n";
		if (!empty($m->DisplayWorkPhoneNumber))
			echo "                  <li>", $words->get('ProfileWorkPhoneNumber'), ": ", $m->DisplayWorkPhoneNumber, "</li>\n";
		echo "                </ul>\n";
	}
	} // end of (IsLoggedIn())
	else {
	echo "                <ul>\n";
	echo "<font color=red><b>*</b></font>",$words->get('YouNeedToBeALoggedMember') ; 
	echo "                </ul>\n"; 
	}

	echo "              </div>\n"; //end subcl
	echo "            </div>\n"; // end c50l
	echo "            <div class=\"c50r\">\n";
	echo "              <div class=\"subcr\">\n";
	echo "                <ul>\n";
    if ($User = APP_User::login()) {
	echo "                  <li class=\"label\">Messenger</li>\n";
	if ($m->chat_SKYPE != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_skype.png\" width=\"16\" height=\"16\" title=\"Skype\" alt=\"Skype\" /> Skype: ", PublicReadCrypted($m->chat_SKYPE, $words->get('Hidden')), "</li>\n";
	if ($m->chat_ICQ != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_icq.png\" width=\"16\" height=\"16\" title=\"ICQ\" alt=\"ICQ\" /> ICQ: ", PublicReadCrypted($m->chat_ICQ, $words->get('Hidden')), "</li>\n";
	if ($m->chat_AOL != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_aim.png\" width=\"16\" height=\"16\" title=\"AOL\" alt=\"AOL\" /> AOL: ", PublicReadCrypted($m->chat_AOL, $words->get('Hidden')), "</li>\n";
	if ($m->chat_MSN != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_msn.png\" width=\"16\" height=\"16\" title=\"MSN\" alt=\"MSN\" /> MSN: ", PublicReadCrypted($m->chat_MSN, $words->get('Hidden')), "</li>\n";
	if ($m->chat_YAHOO != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_yahoo.png\" width=\"16\" height=\"16\" title=\"Yahoo\" alt=\"Yahoo\" /> Yahoo: ", PublicReadCrypted($m->chat_YAHOO, $words->get('Hidden')), "</li>\n";
	if ($m->chat_GOOGLE != 0)
		echo "                  <li><img src= \"./images/icons1616/icon_gtalk.png\" width=\"16\" height=\"16\" title=\"Google Talk\" alt=\"Google Talk\" /> GoogleTalk: ", PublicReadCrypted($m->chat_GOOGLE, $words->get('Hidden')), "</li>\n";	
	if ($m->chat_Others != 0)
		echo "                  <li>", $words->get('chat_others'), ": ", PublicReadCrypted($m->chat_Others, $words->get('Hidden')), "</li>\n";
	echo "                </ul>\n";
	} // end of (IsLoggedIn())
	if ($m->WebSite != "") {
		echo "              <ul>\n";
		echo "                <li class=\"label\">", $words->get('Website'), "</li>\n";
		echo "                <li><a href=\"", $m->WebSite, "\">", $m->WebSite, "</a></li>\n";
		echo "              </ul>\n";
	} // end if there is WebSite
	echo "              </div>\n"; // end subcr
	echo "            </div>\n"; // end c50r
	echo "          </div>\n"; // end subcolumns
  echo "        </div>\n"; // end info highlight


?>
