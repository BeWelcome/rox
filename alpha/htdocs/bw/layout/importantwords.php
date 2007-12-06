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

	include "header.php";

//	Menu1("", "Important Words"); // Displays the top menu


//	DisplayHeaderShortUserContent($title);
	
   echo "<div class=\"info\">\n";
	echo "<p>\n" ;
	echo "&nbsp;&nbsp;&nbsp;",ww("AboutUsPage"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Actions"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Ads"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Blogs"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Contact"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ContactUs"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("default_meta_description"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("default_meta_keyword"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("DisplayAllContacts"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("faq"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Forum"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Gallery"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Groups"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("HelloUsername"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("HospitalityExchange"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("InviteAFriendPage"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Logout"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MainPage"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Members"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("Menu"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyMessages"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyPreferences"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyProfile"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyProfile"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("NbMembersOnline"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("News"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("RecentMember"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("RecentVisitsOfyourProfile"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SearchPage"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SeeProfileOf"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("TheHospitalityNetwork"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ToChangeLanguageClickFlag"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("VolunteerAction"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("WelcomePage "),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("BeWelcomesignup"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupBirthDate"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupBirthDateDescription"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupBirthDateShape"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupCheckPassword"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupCheckYourMailToConfirm"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupChooseCity"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupChooseRegion"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupConfirmedPage"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupConfirmQuestion"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupEmail"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupEmailCheck"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupEmailCheckDescription"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupEmailDescription"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupEmailShortDesc"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorBirthDate"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorBirthDateToLow"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorEmailCheck"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorFullNameRequired"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorInvalidEmail"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorPasswordCheck"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorProvideCity"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorProvideCountry"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorProvideHouseNumber"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorProvideRegion"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorProvideStreetName"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorProvideZip"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorUsernameAlreadyTaken"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupErrorWrongUsername"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SignupFeedback"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPage"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord1"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord10"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord11"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord12"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord13"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord14"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord15"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord16"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord17"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord18"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord2"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord3"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord4"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord5"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord6"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord7"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord8"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord9"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageWord19"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageLoginSubmit"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("IndexPageTitle"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("HospitalityExchange"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MessagesThatIHaveReceived"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyMessagesReceived"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyMessagesSent"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyMessagesSpam"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyMessagesDraft"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("markspam"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ConfirmAction"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SelectAll"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SelectNone"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("delmessage"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("SelectMessages"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("EditMyProfile"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ViewComments"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("addcomments"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ViewForumPosts"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("AddToMyNotes"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("NbComments"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ProfileVersionIn"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MyVisitors"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("MemberPage"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ContactMember"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ViewMyRelationForThisMember"),"<br />\n";	
	echo "&nbsp;&nbsp;&nbsp;",ww("EditMyProfilePageFor"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("AgeEqualX"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("ModifyYourPhotos"),"<br />\n";
	echo "&nbsp;&nbsp;&nbsp;",ww("WarningYouAreWorkingIn"),"<br />\n";

	echo "</p>" ;

	echo "</div>\n";
	

	include "footer.php";
} // end of DisplayImportantWords()