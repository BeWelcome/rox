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


require_once ("menus.php");
require_once ("profilepage_header.php");

function DisplayEditMyProfile($m, $profilewarning = "", $TGroups,$CanTranslate=false) {
	global $title, $_SYSHCVOL;
	$title = ww('EditMyProfilePageFor', $m->Username);
	require_once "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php?cid=".$m->Username); // even in editmyprofile we can be in the myprofile menu

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used (only owner can decrypt)	

	menumember("editmyprofile.php?cid=" . $m->id, $m);	
	if ($m->photo == "") { // if the member has no picture propose to add one
		$MenuAction = "<li><a href=\"myphotos.php?cid=" . $m->id . "\">" . ww("AddYourPhoto") . "</a></li>\n";
	} else {
		$MenuAction = "<li><a href=\"myphotos.php?cid=" . $m->id . "\">" . ww("ModifyYourPhotos") . "</a></li>\n";
	}	
  $MenuAction .= "<li><a href=\"changepassword.php?cid=" . $m->id . "\">" . ww("ChangePassword") . "</a></li>\n";
  
	ShowActions($MenuAction); // Show the Actions
	ShowAds(); // Show the Ads

	// open col3 (middle column)
	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n";
	echo "          <div class=\"info\">\n";
	echo "            <p class=\"note\">\n";
	if ($profilewarning != "") {
		echo $profilewarning;
	}
	else {
		echo "            ",ww("WarningYouAreWorkingIn", LanguageName($_SESSION['IdLanguage']),FlagLanguage(),LanguageName($_SESSION['IdLanguage']));
	}
	echo "</p>\n";
  
	echo "            <form id=\"preferences\" method=\"post\" action=\"editmyprofile.php\" >\n";
  
	// Profile Summary
	
	echo "              <fieldset>\n";
	echo "              <legend class=\"icon info22\">",ww('ProfileSummary'),"</legend>\n";
	echo "                <table border=\"0\">\n";
	echo "                  <colgroup>\n";
	echo "                    <col width=\"25%\" />\n";
	echo "                    <col width=\"75%\" />\n";
	echo "                  </colgroup>\n";
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileSummary'),":</td>\n";
	echo "                    <td><textarea name=\"ProfileSummary\" cols=\"40\" rows=\"8\">";
	if ($m->ProfileSummary > 0)
		echo FindTrad($m->ProfileSummary);
	echo "</textarea></td>\n";
	echo "                  </tr>\n";

	// Birth date and co
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('SignupBirthDate'),":</td>\n";
	echo "                    <td colspan=\"2\">";
	echo $m->BirthDate;
	echo "\n &nbsp;&nbsp;&nbsp;&nbsp; <input name=\"HideBirthDate\" type=\"checkbox\" ";
	if ($m->HideBirthDate == "Yes")
		echo " checked=\"checked\"";
	echo " /> ", ww("Hidden");
	echo "</td>";  
	echo "                  </tr>\n"; 
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileOccupation'),":</td>\n";
	echo "                    <td><input type=\"text\" name=\"Occupation\" value=\"";
	if ($m->Occupation > 0)
		echo FindTrad($m->Occupation);
	echo "\" /></td>\n";
	echo "                  </tr>\n";
 	$tt = sql_get_enum("memberslanguageslevel", "Level"); // Get the different available level
	$maxtt = count($tt);

	$max = count($m->TLanguages);
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileLanguagesSpoken'),":</td>\n";
	echo "                    <td>\n";
	echo "                      <table>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		echo "                        <tr>\n";
		echo "                          <td>", $m->TLanguages[$ii]->Name, "</td>\n";
		echo "                          <td><select name=\"memberslanguageslevel_level_id_" . $m->TLanguages[$ii]->id, "\">\n";

		for ($jj = 0; $jj < $maxtt; $jj++) {
			echo "                              <option value=\"" . $tt[$jj] . "\"";
			if ($tt[$jj] == $m->TLanguages[$ii]->Level)
				echo " selected=\"selected\"";
			echo ">", ww("LanguageLevel_" . $tt[$jj]), "</option>\n";
		}
		echo "                              </select>\n";
		echo "                          </td>\n";
		echo "                        </tr>\n";
	}
	// field MotivationForHospitality is obsolete now
	// echo "                  <tr align=\"left\">\n";
	// echo "                    <td class=\"label\">",ww('MotivationForHospitality'),":</td>\n";
	// echo "                    <td colspan=2><textarea name=MotivationForHospitality cols=40 rows=6>";
	// if ($m->MotivationForHospitality > 0)
	// 	echo FindTrad($m->MotivationForHospitality);
	// echo "</textarea></td>";
	// echo "                        </tr>\n";	
	echo "                        <tr>\n";
	echo "                          <td><select name=\"memberslanguageslevel_newIdLanguage\">\n";
	echo "                              <option value=\"\" selected=\"selected\">-", ww("ChooseNewLanguage"), "-</option>\n";
	for ($jj = 0; $jj < count($m->TOtherLanguages); $jj++) {
		echo "                              <option value=\"" . $m->TOtherLanguages[$jj]->id . "\"";
		echo ">", $m->TOtherLanguages[$jj]->Name, "</option>\n";
	}
	echo "                              </select>\n";
	echo "                          </td>\n";
	echo "                          <td><select name=\"memberslanguageslevel_newLevel\">\n";
	for ($jj = 0; $jj < $maxtt; $jj++) {
		echo "                              <option value=\"" . $tt[$jj] . "\"";
		if ($tt[$jj] == $m->TLanguages[$ii]->Level)
			echo " selected=\"selected\"";
		echo ">", ww("LanguageLevel_" . $tt[$jj]), "</option>\n";
	}
	echo "                              </select>\n";
	echo "                          </td>\n";
	echo "                        </tr>\n";
	echo "                      </table>\n";
	echo "                    </td>\n";
	echo "                  </tr>\n";
	echo "                </table>\n";
	echo "              </fieldset>\n";
	

  // Contact Information
	echo "              <fieldset>\n";
	echo "              <legend class=\"icon contact22\">",ww('ContactInfo'),"</legend>\n";
	if (IsAdmin()) { // admin can alter other profiles so in case it was not his own we must create a parameter
		$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
	}
	echo "                <input type=\"hidden\" name=\"cid\" value=\"", $m->id, "\" />\n";
	echo "                <input type=\"hidden\" name=\"action\" value=\"update\" />\n";
	echo "                <table border=\"0\">\n";
	echo "                  <colgroup>\n";
  echo "                    <col width=\"25%\" />\n";
  echo "                    <col width=\"25%\" />\n";
  echo "                    <col width=\"15%\" />\n";
  echo "                    <col width=\"35%\" />\n";
  echo "                  </colgroup>\n";
	if (!$CanTranslate) { // member translator is not allowed to update crypted data
		echo "                  <tr align=\"left\">\n";
		echo "                    <td class=\"label\">",ww('FirstName'),":</td>\n";
		echo "                    <td>", $ReadCrypted ($m->FirstName), "</td>\n";
		echo "                    <td><input type=\"checkbox\" name=\"IsHidden_FirstName\" ";
		if (IsCrypted($m->FirstName))
		   echo "checked";
		echo " /> ", ww("cryptedhidden"),"</td>\n";
		echo "                  </tr>\n";
    echo "                  <tr align=\"left\">\n";
    echo "                    <td class=\"label\">",ww('SecondName'),":</td>\n";
		echo "                    <td>", $ReadCrypted ($m->SecondName), "</td>\n";
		echo "                    <td><input type=\"checkbox\" name=\"IsHidden_SecondName\" ";
		if (IsCrypted($m->SecondName))
		    echo "checked";
		echo " /> ", ww("cryptedhidden"),"</td>\n";
		echo "                  </tr>\n";
    echo "                  <tr align=\"left\">\n";
    echo "                    <td class=\"label\">",ww('LastName'),":</td>\n";
		echo "                    <td>", $ReadCrypted ($m->LastName), "</td>\n";
		echo "                    <td><input type=\"checkbox\" name=\"IsHidden_LastName\" ";
		if (IsCrypted($m->LastName))
		    echo "checked";
		echo " /> ", ww("cryptedhidden"),"</td>\n";
		echo "                    <td><a href=\"updatemandatory.php?cid=".$m->id."\">",ww("UpdateMyName"),"</a></td>\n";
    echo "                  </tr>\n";		
		echo "                  <tr align=\"left\">\n";
		echo "                    <td class=\"label\">",ww('Address'),":</td>\n";
		echo "                    <td>", $m->Address, "</td>\n";
		echo "                    <td><input type=\"checkbox\" name=\"IsHidden_Address\" ";
	  if ((IsCrypted($m->rAddress->StreeName)) or (IsCrypted($m->rAddress->HouseNumber)))
		   echo " checked";
		echo " /> ", ww("cryptedhidden"),"</td>\n";
		echo "                    <td><a href=\"updatemandatory.php?cid=".$m->id."\">",ww("UpdateMyAdress"),"</a></td>\n";
		echo "                  </tr>\n";
		echo "                  <tr align=\"left\">\n";
	  echo "                    <td class=\"label\">",ww('Zip'),":</td>\n";
	  echo "                    <td>", $m->Zip, "</td>\n";
	  echo "                    <td><input type=\"checkbox\" name=\"IsHidden_Zip\" ";
	  if (IsCrypted($m->rAddress->Zip)) 
		   echo " checked";
	  echo " /> ", ww("cryptedhidden"),"</td>\n";
	  echo "                    <td><a href=\"updatemandatory.php?cid=".$m->id."\">",ww("UpdateMyZip"),"</a></td>\n";
 		echo "                  </tr>\n"; 
	  echo "                  <tr align=\"left\">\n";
	  echo "                    <td class=\"label\">",ww('Location'),":</td>\n";
	  echo "                    <td colspan=\"2\">";
	  echo $m->cityname, "<br />"; 
	  echo $m->regionname, "<br />";
	  echo $m->countryname, "<br />";
	  echo "</td>\n";
	  echo "                    <td><a href=\"updatemandatory.php?cid=".$m->id."\">",ww("UpdateMyLocation"),"</a></td>\n";
	  echo "                  </tr>\n";
	  echo "                  <tr align=\"left\">\n";		
		echo "                    <td class=\"label\">",ww('ProfileHomePhoneNumber'),":</td>\n";
		echo "                    <td><input type=\"text\" name=\"HomePhoneNumber\" value=\"", $ReadCrypted ($m->HomePhoneNumber), "\" /></td>\n";
  	echo "                    <td><input type=\"checkbox\" name=\"IsHidden_HomePhoneNumber\" ";
		if (IsCrypted($m->HomePhoneNumber))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";    
    echo "                  </tr>\n";	  		
 		echo "                  <tr align=\"left\">\n";
		echo "                    <td class=\"label\">",ww('ProfileCellPhoneNumber'),":</td>\n";
		echo "                    <td><input type=\"text\" name=\"CellPhoneNumber\" value=\"", $ReadCrypted ($m->CellPhoneNumber), "\" /></td>\n";
		echo "                    <td><input type=\"checkbox\"  name=\"IsHidden_CellPhoneNumber\" ";
		if (IsCrypted($m->CellPhoneNumber))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";
    echo "                  </tr>\n";	  		
 		echo "                  <tr align=\"left\">\n";
		echo "                    <td class=\"label\">",ww('ProfileWorkPhoneNumber'),"</td>\n";
		echo "                    <td><input type=\"text\" name=\"WorkPhoneNumber\" value=\"", $ReadCrypted ($m->WorkPhoneNumber), "\" /></td>\n";
	  echo "                    <td><input type=\"checkbox\"  name=\"IsHidden_WorkPhoneNumber\" ";
		if (IsCrypted($m->WorkPhoneNumber))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";    
	  echo "                  </tr>\n";	  		
 		echo "                  <tr align=\"left\">\n";    		
		echo "                    <td class=\"label\">",ww('SignupEmail'),":</td>\n";
		echo "                    <td><input type=\"text\" name=\"Email\" value=\"", $ReadCrypted ($m->Email), "\" /></td>\n";
		echo "                    <td>",ww("EmailIsAlwayHidden"),"</td>\n";
		echo "                    <td><input type=\"submit\" id=\"submit\" name=\"action\" value=\"", ww("TestThisEmail"), "\" title=\"".ww("ClickToHaveEmailTested")."\" /></td>\n";
		echo "                  </tr>\n";
	  echo "                  <tr align=\"left\">\n";
	  echo "                    <td class=\"label\">",ww('Website'),":</td>\n";
	  echo "                    <td><input type=\"text\" name=\"WebSite\" value=\"", $m->WebSite, "\" /></td>\n";
		echo "                  </tr>\n";	
  	echo "                  <tr align=\"left\">\n"; 
		echo "                    <td class=\"label\">Skype:</td>\n";
		echo "                    <td><input type=\"text\" name=\"chat_SKYPE\" value=\"", $ReadCrypted ($m->chat_SKYPE), "\" /></td>\n";
		echo "                    <td><input type=\"checkbox\"  name=\"IsHidden_chat_SKYPE\" ";
		if (IsCrypted($m->chat_SKYPE))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";    
		echo "                  </tr>\n";
  	echo "                  <tr align=\"left\">\n";		
		echo "                    <td class=\"label\">ICQ:</td>\n";
  	echo "                    <td><input type=\"text\" name=\"chat_ICQ\" value=\"", $ReadCrypted ($m->chat_ICQ), "\" /></td>\n";
  	echo "                      <td><input type=\"checkbox\"  name=\"IsHidden_chat_ICQ\" ";
		if (IsCrypted($m->chat_ICQ))
		   echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";   
		echo "                  </tr>\n";
  	echo "                  <tr align=\"left\">\n";				
		echo "                    <td class=\"label\">MSN:</td>\n";
		echo "                    <td><input type=\"text\" name=\"chat_MSN\" value=\"", $ReadCrypted ($m->chat_MSN), "\" /></td>\n";
		echo "                    <td><input type=\"checkbox\"  name=\"IsHidden_chat_MSN\" ";
		if (IsCrypted($m->chat_MSN))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";    
		echo "                  </tr>\n";
  	echo "                  <tr align=\"left\">\n";
		echo "                    <td class=\"label\">AOL:</td>\n";
		echo "                    <td><input type=\"text\" name=\"chat_AOL\" value=\"", $ReadCrypted ($m->chat_AOL), "\" /></td>\n";
		echo "                    <td><input type=\"checkbox\"  name=\"IsHidden_chat_AOL\" ";
		if (IsCrypted($m->chat_AOL))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";    
 		echo "                  </tr>\n";
  	echo "                  <tr align=\"left\">\n"; 	
  	echo "                   <td class=\"label icon yahoo16\">Yahoo:</td>\n";
		echo "                   <td><input type=\"text\" name=\"chat_YAHOO\" value=\"", $ReadCrypted ($m->chat_YAHOO), "\" /></td>\n";
		echo "                   <td><input type=\"checkbox\"  name=\"IsHidden_chat_YAHOO\" ";
		if (IsCrypted($m->chat_YAHOO))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";    
 		echo "                  </tr>\n";
 		echo "                  <tr align=\"left\">\n"; 	
  	echo "                   <td class=\"label\">Google Talk:</td>\n";
		echo "                   <td><input type=\"text\" name=\"chat_GOOGLE\" value=\"", $ReadCrypted ($m->chat_GOOGLE), "\" /></td>\n";
		echo "                   <td><input type=\"checkbox\"  name=\"IsHidden_chat_GOOGLE\" ";
		if (IsCrypted($m->chat_GOOGLE))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";    
 		echo "                  </tr>\n";
  	echo "                  <tr align=\"left\">\n";		
		echo "                    <td class=\"label\">",ww("chat_others"),":</td>\n";
		echo "                    <td><input type=\"text\" name=\"chat_Others\" value=\"", $ReadCrypted ($m->chat_Others), "\" /></td>\n";
		echo "                    <td><input type=\"checkbox\"  name=\"IsHidden_chat_Others\" ";
		if (IsCrypted($m->chat_Others))
		    echo " checked=\"checked\"";
		echo " /> ", ww("cryptedhidden"),"</td>\n";    
 		echo "                  </tr>\n";		  	
		echo "                </table>\n";
	}
	echo "              </fieldset>\n";
	

	
	// Accommodation
  echo "              <fieldset>\n";
  echo "              <legend class=\"icon accommodation22\">",ww('ProfileAccommodation'),"</legend>\n";
  echo "                <table border=\"0\">\n";
  echo "                  <colgroup>\n";
  echo "                    <col width=\"25%\" />\n";
  echo "                    <col width=\"75%\" />\n";
  echo "                  </colgroup>\n";
  if ($m->Accomodation != "") {
		echo "                  <tr align=\"left\">\n";
		echo "                    <td class=\"label\">",ww("ProfileAccomodation"),"</td>\n";
		echo "                    <td>\n";
		$tt = $_SYSHCVOL['Accomodation'];
		$max = count($tt);
		echo "                      <select name=Accomodation>\n";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "                      <option value=\"" . $tt[$ii] . "\"";
			if ($tt[$ii] == $m->Accomodation)
				echo " selected=\"selected\"";
			echo ">", ww("Accomodation_" . $tt[$ii]), "</option>\n";
		}
		echo "                      </select>\n";
		echo "                    </td>";
		echo "                  </tr>\n";
	}	
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileNumberOfGuests'),":</td>\n";
	echo "                    <td><input name=\"MaxGuest\" type=\"text\" size=\"3\" value=\"", $m->MaxGuest,"\" /></td>\n";
	echo "                  </tr>\n";
	echo "                  <tr align=\"left\">\n";	
	echo "                    <td class=\"label\">",ww('ProfileMaxLenghtOfStay'),":</td>\n";
	echo "                    <td colspan=\"2\"><input name=\"MaxLenghtOfStay\" type=\"text\" size=\"40\" value=\"";
	if ($m->MaxLenghtOfStay > 0)
		echo FindTrad($m->MaxLenghtOfStay);
	echo "\" /></td>\n";
	echo "                  </tr>\n";
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileILiveWith'),":</td>\n";
	echo "                    <td colspan=\"2\"><input name=\"ILiveWith\" type=\"text\" size=\"40\" value=\"";
	if ($m->ILiveWith > 0)
		echo FindTrad($m->ILiveWith);
	echo "\" /></td>\n";
	echo "                  </tr>\n";
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfilePleaseBring'),":</td>\n";
	echo "                    <td colspan=\"2\"><input name=\"PleaseBring\" type=\"text\" size=\"40\" value=\"";
	if ($m->PleaseBring > 0)
		echo FindTrad($m->PleaseBring);
	echo "\" /></td>\n";	
	echo "                  </tr>\n";
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileOfferGuests'),":</td>\n";
	echo "                    <td colspan=\"2\"><input name=\"OfferGuests\" type=\"text\" size=\"40\" value=\"";
	if ($m->OfferGuests > 0)
		echo FindTrad($m->OfferGuests);
	echo "\" /></td>\n";	
	echo "                  </tr>\n";
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileOfferHosts'),":</td>\n";
	echo "                    <td colspan=\"2\"><input name=\"OfferHosts\" type=\"text\" size=\"40\" value=\"";
	if ($m->OfferHosts > 0)
		echo FindTrad($m->OfferHosts);
	echo "\" /></td>\n";
	echo "                  </tr>\n";


  $max = count($m->TabTypicOffer);
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ICanAlsoOffer'),":</td>\n";
	echo "                    <td colspan=\"2\">\n";
	echo "                      <ul>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		echo "                      <li><input type=\"checkbox\" name=\"check_" . $m->TabTypicOffer[$ii] . "\" ";
		if (strpos($m->TypicOffer, $m->TabTypicOffer[$ii]) !== false)
			echo "checked=\"checked\"";
		echo " />";
		echo "&nbsp;&nbsp;", ww("TypicOffer_" . $m->TabTypicOffer[$ii]), "</li>\n";
	}
	echo "                      </ul>\n";
	echo "                    </td>\n";
  	echo "                  </tr>\n";


	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfilePublicTransport'),":</td>\n";
	echo "                    <td colspan=\"2\"><input name=\"PublicTransport\" type=\"text\" size=\"40\" value=\"";
	if ($m->PublicTransport > 0)
		echo FindTrad($m->PublicTransport);
	echo "\" /></td>\n";
  echo "                  </tr>\n";
  //  todo process this with the main address
	//  echo "<tr align=\"left\">\n";
	//  echo "                    <td class=\"label\">",ww('GettingHere'),":</td>\n";
	//  echo "                    <td colspan=2><textarea name=IdGettingThere cols=40 rows=4>";
	//  if ($m->IdGettingThere>0)
	//  echo FindTrad($m->IdGettingThere);
	//  echo "</textarea></td>\n";
	//  echo "                  </tr>\n";
  $max = count($m->TabRestrictions);
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileRestrictionForGuest'),":</td>\n";
	echo "                    <td colspan=\"2\">\n";
	echo "                      <ul>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		echo "                      <li><input type=\"checkbox\" name=\"check_" . $m->TabRestrictions[$ii] . "\" ";
		if (strpos($m->Restrictions, $m->TabRestrictions[$ii]) !== false)
			echo "checked=\"checked\"";
		echo " />";
		echo "&nbsp;&nbsp;", ww("Restriction_" . $m->TabRestrictions[$ii]), "</li>\n";
	}
	echo "                      </ul>\n";
	echo "                    </td>\n";
  	echo "                  </tr>\n";


	echo "                  <tr align=\"left\">\n";
  echo "                    <td class=\"label\">",ww('ProfileOtherRestrictions'),":</td>\n";
	echo "                    <td colspan=\"2\">\n";
	echo "                      <textarea name=\"OtherRestrictions\" cols=\"40\" rows=\"3\">";
	if ($m->OtherRestrictions > 0) {
		echo FindTrad($m->OtherRestrictions);
	}
	echo "</textarea>\n";
	echo "                    </td>\n";  
  echo "                  </tr>\n";
  echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileAdditionalAccomodationInfo'),":</td>\n";	
	echo "                    <td colspan=\"2\">\n";
	echo "                      <textarea name=\"AdditionalAccomodationInfo\" cols=\"40\" rows=\"4\">";
	if ($m->AdditionalAccomodationInfo > 0) {
		echo FindTrad($m->AdditionalAccomodationInfo);
	}
	echo "</textarea>";
	echo "</td>\n";
	echo "                  </tr>\n";
	echo "                </table>\n";
	echo "              </fieldset>\n";
	
	// Hobbies & Interests
	echo "              <fieldset>\n";
  echo "              <legend class=\"icon sun22\">",ww('ProfileInterests'),"</legend>\n";
  echo "                <table border=\"0\">\n";
  echo "                  <colgroup>\n";
  echo "                    <col width=\"25%\" />\n";
  echo "                    <col width=\"75%\" />\n";
  echo "                  </colgroup>\n";
  echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileHobbies'),":</td>\n";
	echo "                    <td><textarea name=\"Hobbies\" cols=\"40\" rows=\"4\">";
	if ($m->Hobbies > 0)
		echo FindTrad($m->Hobbies);
	echo "</textarea></td>\n";
  echo "                  </tr>\n";
  echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileBooks'),":</td>\n";
	echo "                    <td><textarea name=\"Books\" cols=\"40\" rows=\"4\">";
	if ($m->Books > 0)
		echo FindTrad($m->Books);
	echo "</textarea></td>\n";
  echo "                  </tr>\n";	
	echo "                  <tr align=\"left\">\n";
  echo "                    <td class=\"label\">",ww('ProfileMusic'),":</td>\n";
	echo "                    <td><textarea name=\"Music\" cols=\"40\" rows=\"4\">";
	if ($m->Music > 0)
		echo FindTrad($m->Music);
	echo "</textarea></td>\n";
  echo "                  </tr>\n";
  echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfileMovies'),":</td>\n";
	echo "                    <td><textarea name=\"Movies\" cols=\"40\" rows=\"4\">";
	if ($m->Movies > 0)
		echo FindTrad($m->Movies);
	echo "</textarea></td>\n";
  echo "                  </tr>\n";    
	echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">", ww("ProfileOrganizations"), ":</td>\n";
	echo "                    <td><textarea name=\"Organizations\" cols=\"40\" rows=\"4\">";
	if ($m->Organizations > 0)
		echo FindTrad($m->Organizations);
	echo "</textarea></td>\n";
  echo "                  </tr>\n";  
  echo "                </table>\n";
  echo "              </fieldset>\n";
  
  // Travel Experience
 	echo "              <fieldset>\n";
  echo "              <legend class=\"icon world22\">",ww('ProfileTravelExperience'),"</legend>\n";
  echo "                <table border=\"0\">\n";
  echo "                  <colgroup>\n";
  echo "                    <col width=\"25%\" />\n";
  echo "                    <col width=\"75%\" />\n";
  echo "                  </colgroup>\n";
  echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfilePastTrips'),":</td>\n";
	echo "                    <td><textarea name=\"PastTrips\" cols=\"40\" rows=\"4\">";
	if ($m->PastTrips > 0)
		echo FindTrad($m->PastTrips);
	echo "</textarea></td>\n";
  echo "                  </tr>\n"; 
  echo "                  <tr align=\"left\">\n";
	echo "                    <td class=\"label\">",ww('ProfilePlannedTrips'),":</td>\n";
	echo "                    <td><textarea name=\"PlannedTrips\" cols=\"40\" rows=\"4\">";
	if ($m->PlannedTrips > 0)
		echo FindTrad($m->PlannedTrips);
	echo "</textarea></td>\n";
  echo "                  </tr>\n";    
  echo "                </table>\n";
  echo "              </fieldset>\n";
  
  // My Groups
	echo "              <fieldset>\n";
  echo "              <legend class=\"icon groups22\">",ww('MyGroups'),"</legend>\n";
  echo "                <table border=\"0\">\n";
  echo "                  <colgroup>\n";
  echo "                    <col width=\"25%\" />\n";
  echo "                    <col width=\"75%\" />\n";
  echo "                  </colgroup>\n";
	$max = count($TGroups);
	if ($max > 0) {
		for ($ii = 0; $ii < $max; $ii++) {
			if (empty($TGroups[$ii]->Name)) continue ; // weird bug todo fix properly : we enter in this loop even with an empty TGroup !
			echo "                <tr align=\"left\">\n";
			echo "                  <td class=\"label\">", ww("Group_" . $TGroups[$ii]->Name), "</td>\n";
			echo "                  <td  colspan=\"2\">\n";
			echo "                    <textarea cols=\"40\" rows=\"6\" name=\"", "Group_" . $TGroups["$ii"]->Name, "\">";
			if ($TGroups[$ii]->Comment > 0)
				echo FindTrad($TGroups[$ii]->Comment);
			echo "</textarea>\n";
			if (HasRight("Beta","GroupMessage")) { 
			   echo "<br /> BETA ";
			   echo "                <input type=\"checkbox\" name=\"AcceptMessage_".$TGroups[$ii]->Name."\" ";
			   if ($TGroups[$ii]->IacceptMassMailFromThisGroup=="yes") echo "checked";
			   echo " />\n";
			   echo ww('AcceptMessageFromThisGroup');
			}
			else {
			   echo "                    <input type=\"hidden\" name=\"AcceptMessage_".$TGroups[$ii]->Name."\" value=\"".$TGroups[$ii]->IacceptMassMailFromThisGroup."\" />\n";
			}
			
			echo "                  </td>\n";
			echo "                </tr>\n";
		}
	}
  echo "              </table>\n";
  echo "              </fieldset>\n";

  // Special Relations (should this be listed in editmyprofile or on a sperate page ?)
  
	$Relations=$m->Relations;
	$max = count($Relations);
	if ($max > 0) {
  	   echo "              <fieldset>\n";
  	   echo "              <legend class=\"icon groups22\">",ww('MyRelations'),"</legend>\n";
  	   echo "                <table align=left border=0>\n";
	   for ($ii = 0; $ii < $max; $ii++) {
			echo "                  <tr>\n";
			echo "                    <td>", LinkWithPicture($Relations[$ii]->Username,$Relations[$ii]->photo),"<br />",$Relations[$ii]->Username, "</td>";
			echo "                    <td align=right colspan=2>";
			echo "<textarea cols=40 rows=6 name=\"", "RelationComment_" . $Relations["$ii"]->id, "\">";
			echo $Relations[$ii]->Comment;
			echo "</textarea></td>\n";
			echo "                    <td><a href=\"editmyprofile.php?action=delrelation&Username=",$Relations[$ii]->Username,"\"  onclick=\"return confirm('Confirm delete ?');\">",ww("delrelation",$Relations[$ii]->Username),"</a></td>\n";
		  echo "                  </tr>\n";
		}
	}
  echo "              </table>\n";
  echo "              </fieldset>\n";
  
  
  echo "                <table>\n";
	echo "                  <tr>\n";
	echo "                    <td colspan=\"3\" align=\"center\"><input type=\"submit\" id=\"submit\" name=\"submit\" value=\"submit\" /></td>\n";
	echo "                  </tr>\n";
	echo "                </table>\n";
	echo "             </form>\n";
	echo "           </div>"; // end info highlight

	require_once "footer.php";

}
?>
