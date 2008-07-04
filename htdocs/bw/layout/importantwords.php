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
/*
This page is only used to provide a list of most important words to translate first
*/
require_once ("menus.php");

function DisplayImportantWords() {
  global $title;
  $title = "Important words to translate";

  $lang = $_SESSION['lang']; // save session language
  $_SESSION['lang'] = CV_def_lang;
  $_SESSION['IdLanguage'] = 0; // force English for menu

  include "header.php";

  Menu1("", "Important Words"); // Displays the top menu

  Menu2("main.php", "Important Words"); // Displays the second menu

  $_SESSION['lang'] = $lang; // restore session language
  $rr = LoadRow("select * from languages where ShortCode='" . $lang . "'");
  $ShortCode = $rr->ShortCode;
  $_SESSION['IdLanguage'] = $IdLanguage = $rr->id;
  $MenuAction  = "            <li><a href=\"".bwlink("admin/adminwords.php")."\">Admin word</a></li>\n";
  $MenuAction .= "            <li><a href=\"".bwlink("importantwords.php")."\">Important words</a></li>\n";
  $MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?ShowLanguageStatus=". $rr->id)."\"> All in ". $rr->EnglishName. "</a></li>\n";
  $MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?onlymissing&ShowLanguageStatus=". $rr->id)."\"> Only missing in ". $rr->EnglishName. "</a></li>\n";
  $MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?onlyobsolete&ShowLanguageStatus=". $rr->id)."\"> Only obsolete in ". $rr->EnglishName. "</a></li>\n";
  $MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?showstats")."\">Show stats</a></li>\n";

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";
  echo "<ul>\n" ;
  echo "<li>",ww("AboutUsPage"),"</li>\n";
  echo "<li>",ww("Actions"),"</li>\n";
  echo "<li>",ww("Ads"),"</li>\n";
  echo "<li>",ww("Blogs"),"</li>\n";
  echo "<li>",ww("Contact"),"</li>\n";
  echo "<li>",ww("ContactUs"),"</li>\n";
  echo "<li>",ww("default_meta_description"),"</li>\n";
  echo "<li>",ww("default_meta_keyword"),"</li>\n";
  echo "<li>",ww("DisplayAllContacts"),"</li>\n";
  echo "<li>",ww("faq"),"</li>\n";
  echo "<li>",ww("Forum"),"</li>\n";
  echo "<li>",ww("Gallery"),"</li>\n";
  echo "<li>",ww("Groups"),"</li>\n";
  echo "<li>",ww("HelloUsername"),"</li>\n";
  echo "<li>",ww("HospitalityExchange"),"</li>\n";
  echo "<li>",ww("InviteAFriendPage"),"</li>\n";
  echo "<li>",ww("Logout"),"</li>\n";
  echo "<li>",ww("MainPage"),"</li>\n";
  echo "<li>",ww("Members"),"</li>\n";
  echo "<li>",ww("Menu"),"</li>\n";
  echo "<li>",ww("MyMessages"),"</li>\n";
  echo "<li>",ww("MyPreferences"),"</li>\n";
  echo "<li>",ww("MyProfile"),"</li>\n";
  echo "<li>",ww("MyProfile"),"</li>\n";
  echo "<li>",ww("NbMembersOnline"),"</li>\n";
  echo "<li>",ww("News"),"</li>\n";
  echo "<li>",ww("RecentMember"),"</li>\n";
  echo "<li>",ww("RecentVisitsOfyourProfile"),"</li>\n";
  echo "<li>",ww("SearchPage"),"</li>\n";
  echo "<li>",ww("SeeProfileOf"),"</li>\n";
  echo "<li>",ww("TheHospitalityNetwork"),"</li>\n";
  echo "<li>",ww("ToChangeLanguageClickFlag"),"</li>\n";
  echo "<li>",ww("VolunteerAction"),"</li>\n";
  echo "<li>",ww("WelcomePage "),"</li>\n";
  echo "<li>",ww("BeWelcomesignup"),"</li>\n";
  echo "<li>",ww("SignupBirthDate"),"</li>\n";
  echo "<li>",ww("SignupBirthDateDescription"),"</li>\n";
  echo "<li>",ww("SignupBirthDateShape"),"</li>\n";
  echo "<li>",ww("SignupCheckPassword"),"</li>\n";
  echo "<li>",ww("SignupCheckYourMailToConfirm"),"</li>\n";
  echo "<li>",ww("SignupChooseCity"),"</li>\n";
  echo "<li>",ww("SignupChooseRegion"),"</li>\n";
  echo "<li>",ww("SignupConfirmedPage"),"</li>\n";
  echo "<li>",ww("SignupConfirmQuestion"),"</li>\n";
  echo "<li>",ww("SignupEmail"),"</li>\n";
  echo "<li>",ww("SignupEmailCheck"),"</li>\n";
  echo "<li>",ww("SignupEmailCheckDescription"),"</li>\n";
  echo "<li>",ww("SignupEmailDescription"),"</li>\n";
  echo "<li>",ww("SignupEmailShortDesc"),"</li>\n";
  echo "<li>",ww("SignupErrorBirthDate"),"</li>\n";
  echo "<li>",ww("SignupErrorBirthDateToLow"),"</li>\n";
  echo "<li>",ww("SignupErrorEmailCheck"),"</li>\n";
  echo "<li>",ww("SignupErrorFullNameRequired"),"</li>\n";
  echo "<li>",ww("SignupErrorInvalidEmail"),"</li>\n";
  echo "<li>",ww("SignupErrorPasswordCheck"),"</li>\n";
  echo "<li>",ww("SignupErrorProvideCity"),"</li>\n";
  echo "<li>",ww("SignupErrorProvideCountry"),"</li>\n";
  echo "<li>",ww("SignupErrorProvideHouseNumber"),"</li>\n";
  echo "<li>",ww("SignupErrorProvideRegion"),"</li>\n";
  echo "<li>",ww("SignupErrorProvideStreetName"),"</li>\n";
  echo "<li>",ww("SignupErrorProvideZip"),"</li>\n";
  echo "<li>",ww("SignupErrorUsernameAlreadyTaken"),"</li>\n";
  echo "<li>",ww("SignupErrorWrongUsername"),"</li>\n";
  echo "<li>",ww("SignupFeedback"),"</li>\n";
  echo "<li>",ww("IndexPage"),"</li>\n";
  echo "<li>",ww("IndexPageWord1"),"</li>\n";
  echo "<li>",ww("IndexPageWord10"),"</li>\n";
  echo "<li>",ww("IndexPageWord11"),"</li>\n";
  echo "<li>",ww("IndexPageWord12"),"</li>\n";
  echo "<li>",ww("IndexPageWord13"),"</li>\n";
  echo "<li>",ww("IndexPageWord14"),"</li>\n";
  echo "<li>",ww("IndexPageWord15"),"</li>\n";
  echo "<li>",ww("IndexPageWord16"),"</li>\n";
  echo "<li>",ww("IndexPageWord17"),"</li>\n";
  echo "<li>",ww("IndexPageWord18"),"</li>\n";
  echo "<li>",ww("IndexPageWord2"),"</li>\n";
  echo "<li>",ww("IndexPageWord3"),"</li>\n";
  echo "<li>",ww("IndexPageWord4"),"</li>\n";
  echo "<li>",ww("IndexPageWord5"),"</li>\n";
  echo "<li>",ww("IndexPageWord6"),"</li>\n";
  echo "<li>",ww("IndexPageWord7"),"</li>\n";
  echo "<li>",ww("IndexPageWord8"),"</li>\n";
  echo "<li>",ww("IndexPageWord9"),"</li>\n";
  echo "<li>",ww("IndexPageWord19"),"</li>\n";
  echo "<li>",ww("IndexPageLoginSubmit"),"</li>\n";
  echo "<li>",ww("IndexPageTitle"),"</li>\n";
  echo "<li>",ww("HospitalityExchange"),"</li>\n";
  echo "<li>",ww("MessagesThatIHaveReceived"),"</li>\n";
  echo "<li>",ww("MyMessagesReceived"),"</li>\n";
  echo "<li>",ww("MyMessagesSent"),"</li>\n";
  echo "<li>",ww("MyMessagesSpam"),"</li>\n";
  echo "<li>",ww("MyMessagesDraft"),"</li>\n";
  echo "<li>",ww("markspam"),"</li>\n";
  echo "<li>",ww("ConfirmAction"),"</li>\n";
  echo "<li>",ww("SelectAll"),"</li>\n";
  echo "<li>",ww("SelectNone"),"</li>\n";
  echo "<li>",ww("delmessage"),"</li>\n";
  echo "<li>",ww("SelectMessages"),"</li>\n";
  echo "<li>",ww("EditMyProfile"),"</li>\n";
  echo "<li>",ww("ViewComments"),"</li>\n";
  echo "<li>",ww("addcomments"),"</li>\n";
  echo "<li>",ww("ViewForumPosts"),"</li>\n";
  echo "<li>",ww("AddToMyNotes"),"</li>\n";
  echo "<li>",ww("NbComments"),"</li>\n";
  echo "<li>",ww("ProfileVersionIn"),"</li>\n";
  echo "<li>",ww("MyVisitors"),"</li>\n";
  echo "<li>",ww("MemberPage"),"</li>\n";
  echo "<li>",ww("ContactMember"),"</li>\n";
  echo "<li>",ww("ViewMyRelationForThisMember"),"</li>\n";
  echo "<li>",ww("EditMyProfilePageFor"),"</li>\n";
  echo "<li>",ww("AgeEqualX"),"</li>\n";
  echo "<li>",ww("ModifyYourPhotos"),"</li>\n";
  echo "<li>",ww("WarningYouAreWorkingIn"),"</li>\n";

  echo "</ul>" ;

  echo "</div>\n";


  include "footer.php";
} // end of DisplayImportantWords()
