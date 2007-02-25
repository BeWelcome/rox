<?php
require_once ("Menus.php");
function DisplayEditMyProfile($m, $profilewarning = "", $TGroups,$CanTranslate=false) {
	global $title, $_SYSHCVOL;
	$title = ww('EditMyProfilePageFor', $m->Username);
	include "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php?cid=".$m->Username); // even if in editmyprofil we can be in the myprofile menu

	// Header of the profile page
	require_once ("profilepage_header.php");

	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used (only owner can decrypt)	

	echo "	\n<div id=\"columns\">\n";
	menumember("editmyprofile.php?cid=" . $m->id, $m->id, $m->NbComment);
	echo "		\n<div id=\"columns-low\">\n";
	
	if ($m->photo == "") { // if the member has no picture propose to add one
		$MenuAction = "<li><a href=\"myphotos.php?cid=" . $m->id . "\">" . ww("AddYourPhoto") . "</a></li>\n";
	} else {
		$MenuAction = "<li><a href=\"myphotos.php?cid=" . $m->id . "\">" . ww("ModifyYourPhotos") . "</a></li>\n";
	}
	echo "    <div id=\"main\">";
	ShowActions($MenuAction); // Show the Actions
	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "          <div id=\"content\"> \n";
	echo "						<div class=\"info\">";
	if ($profilewarning != "") {
		echo "<H2 style=\"color=olive;\">", $profilewarning, "</H2>\n";
	}

	echo "\n<table id=\"preferencesTable\">\n<tr><td bgcolor=#ffff66>";
	if ($profilewarning != "")
		echo $profilewarning;
	else
		echo ww("WarningYouAreWorkingIn", LanguageName($_SESSION['IdLanguage']),FlagLanguage(),LanguageName($_SESSION['IdLanguage']));
	echo "</td>\n</table>\n";

	echo "\n<form method=\"post\" action=\"editmyprofile.php\"  id=\"preferences\">";
	echo "<table id=\"preferencesTable\" align=left>\n";

	if (IsAdmin()) { // admin can alter other profiles so in case it was not his own we must create a parameter
		$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
		echo "<input type=hidden name=cid value=", $m->id, ">";
	}
	echo "<input type=hidden name=action value=update>";

	if (!$CanTranslate) { // member translator is not akkowed to updaet crypted data
	    echo "\n<tr><td colspan=3>";
		echo "<table><tr align=left><td>" ;
		echo ww('FirstName');
		echo "</td>";
		echo "<td>";
		echo "&nbsp;&nbsp;", $ReadCrypted ($m->FirstName);
		echo "</td>";
		echo "<td>";
		echo " <input type=checkbox name=IsHidden_FirstName ";
		if (IsCrypted($m->FirstName))
		   echo " checked";
		echo "></td><td colspan=2>", ww("cryptedhidden");
		echo "</td>\n";

	    echo "<tr align=left><td>" ;
		echo ww('SecondName');
		echo "</td>" ;
		echo "<td>";
		echo "&nbsp;&nbsp;", $ReadCrypted ($m->SecondName);
		echo "</td>";
		echo "<td>";
		echo " <input type=checkbox name=IsHidden_SecondName ";
		if (IsCrypted($m->SecondName))
		    echo " checked";
		echo "></td><td colspan=2>", ww("cryptedhidden");
		echo "</td>\n";

	    echo "\n<tr align=left><td>" ;
		echo ww('LastName');
		echo "</td>" ;
		echo "<td>";
		echo "&nbsp;&nbsp;", $ReadCrypted ($m->LastName);
		echo "</td>";
		echo "<td>";
		echo " <input type=checkbox name=IsHidden_LastName ";
		if (IsCrypted($m->LastName))
		    echo " checked";
		echo "></td><td>", ww("cryptedhidden");
		echo "</td>";
		echo "<td align=right>";
		echo "<a href=\"updatemandatory.php?cid=".$m->id."\">",ww("UpdateMyName"),"</a>" ;
		echo "</td>\n</table>\n</td>";
		

		echo "\n<tr><td>";
		echo ww('SignupEmail');
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=Email value=\"", $ReadCrypted ($m->Email), "\"> ", ww("EmailIsAlwayHidden");
		echo " <input type=submit name=action value=\"", ww("TestThisEmail"), "\" title=\"".ww("ClickToHaveEmailTested")."\">";
		echo "</td> ";

		echo "\n<tr><td>";
		echo ww('ProfileHomePhoneNumber');
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=HomePhoneNumber value=\"", $ReadCrypted ($m->HomePhoneNumber), "\"> ", ww("cryptedhidden"), " <input type=checkbox name=IsHidden_HomePhoneNumber ";
		if (IsCrypted($m->HomePhoneNumber))
		    echo " checked";
		echo "></td> ";

		echo "\n<tr><td>";
		echo ww('ProfileCellPhoneNumber');
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=CellPhoneNumber value=\"", $ReadCrypted ($m->CellPhoneNumber), "\"> ", ww("cryptedhidden"), " <input type=checkbox  name=IsHidden_CellPhoneNumber ";
		if (IsCrypted($m->CellPhoneNumber))
		    echo " checked";
		echo "></td> ";

		echo "\n<tr><td>";
		echo ww('ProfileWorkPhoneNumber');
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=WorkPhoneNumber value=\"", $ReadCrypted ($m->WorkPhoneNumber), "\"> ", ww("cryptedhidden"), " <input type=checkbox  name=IsHidden_WorkPhoneNumber ";
		if (IsCrypted($m->WorkPhoneNumber))
		    echo " checked";
		echo "></td> ";

		echo "\n<tr><td>";
		echo "SKYPE :";
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=chat_SKYPE value=\"", $ReadCrypted ($m->chat_SKYPE), "\"> ", ww("cryptedhidden"), " <input type=checkbox  name=IsHidden_chat_SKYPE ";
		if (IsCrypted($m->chat_SKYPE))
		    echo " checked";
		echo "></td> ";

		echo "\n<tr><td>";
		echo "ICQ :";
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=chat_ICQ value=\"", $ReadCrypted ($m->chat_ICQ), "\"> ", ww("cryptedhidden"), " <input type=checkbox  name=IsHidden_chat_ICQ ";
		if (IsCrypted($m->chat_ICQ))
		   echo " checked";
		echo "></td>  ";

		echo "\n<tr><td>";
		echo "MSN :";
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=chat_MSN value=\"", $ReadCrypted ($m->chat_MSN), "\"> ", ww("cryptedhidden"), " <input type=checkbox  name=IsHidden_chat_MSN ";
		if (IsCrypted($m->chat_MSN))
		    echo " checked";
		echo "></td>  ";

		echo "\n<tr><td>";
		echo "AOL :";
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=chat_AOL value=\"", $ReadCrypted ($m->chat_AOL), "\"> ", ww("cryptedhidden"), " <input type=checkbox  name=IsHidden_chat_AOL ";
		if (IsCrypted($m->chat_AOL))
		    echo " checked";
		echo "></td> ";

		echo "\n<tr><td>";
		echo "YAHOO :";
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=chat_YAHOO value=\"", $ReadCrypted ($m->chat_YAHOO), "\"> ", ww("cryptedhidden"), " <input type=checkbox  name=IsHidden_chat_YAHOO ";
		if (IsCrypted($m->chat_YAHOO))
		    echo " checked";
		echo "></td> ";

		echo "\n<tr><td>";
		echo ww("chat_others"), " :";
		echo "</td>";
		echo "<td colspan=2>";
		echo "<input type=text name=chat_Others value=\"", $ReadCrypted ($m->chat_Others), "\"> ", ww("cryptedhidden"), " <input type=checkbox  name=IsHidden_chat_Others ";
		if (IsCrypted($m->chat_Others))
		    echo " checked";
		echo "></td> ";
	}

	echo "\n<tr><td>";
	echo ww('Location');
	echo "</td>";
	echo "<td colspan=2>";
	echo $m->cityname, "<br>";
	echo $m->regionname, "<br>";
	echo $m->countryname, "<br>";
	echo "</td>";

	echo "\n<tr><td>";
	echo ww('Address');
	echo "</td>";
	echo "<td colspan=2><table><tr><td align=left>" ;
	echo $m->Address ;
	echo "</td><td align=right>";
	echo " <a href=\"updatemandatory.php?cid=".$m->id."\">",ww("UpdateMyAdress"),"</a>" ;
	echo "</td></table></td>" ;

	
	echo "<tr><td>";
	echo ww('ProfileSummary');
	echo ":</td>";
	echo "<td colspan=2><textarea name=ProfileSummary rows=8>";
	if ($m->ProfileSummary > 0)
		echo FindTrad($m->ProfileSummary);
	echo "</textarea></td>";

	echo "<tr><td>";
	echo ww('ProfileOccupation');
	echo ":</td>";
	echo "<td colspan=2><input type=text name=Occupation value=\"";
	if ($m->Occupation > 0)
		echo FindTrad($m->Occupation);
	echo "\"></td>";

	$tt = mysql_get_enum("memberslanguageslevel", "Level"); // Get the different available level
	$maxtt = count($tt);

	$max = count($m->TLanguages);
	echo "<tr><td>";
	echo ww('ProfileLanguagesSpoken');
	echo ":</td>";

	echo "<td colspan=2>\n";
	echo "<table>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		echo "\n<tr>";
		echo "<td>", $m->TLanguages[$ii]->Name, "</td>";
		echo "<td><select name=\"memberslanguageslevel_level_id_" . $m->TLanguages[$ii]->id, "\">";

		for ($jj = 0; $jj < $maxtt; $jj++) {
			echo "<option value=\"" . $tt[$jj] . "\"";
			if ($tt[$jj] == $m->TLanguages[$ii]->Level)
				echo " selected ";
			echo ">", ww("LanguageLevel_" . $tt[$jj]), "</option>\n";
		}
		echo "</select>\n</td>\n";
	}
	echo "\n<tr>";
	echo "<td><select name=\"memberslanguageslevel_newIdLanguage\">";
	echo "<option value=\"\" selected>-", ww("ChooseNewLanguage"), "-</option>\n";
	for ($jj = 0; $jj < count($m->TOtherLanguages); $jj++) {
		echo "<option value=\"" . $m->TOtherLanguages[$jj]->id . "\"";
		echo ">", $m->TOtherLanguages[$jj]->Name, "</option>\n";
	}
	echo "</select>\n</td>";

	echo "<td><select name=\"memberslanguageslevel_newLevel\">";
	for ($jj = 0; $jj < $maxtt; $jj++) {
		echo "<option value=\"" . $tt[$jj] . "\"";
		if ($tt[$jj] == $m->TLanguages[$ii]->Level)
			echo " selected ";
		echo ">", ww("LanguageLevel_" . $tt[$jj]), "</option>\n";
	}
	echo "</select>\n</td>";

	echo "\n</table>\n";

	echo "</td>";

	echo "\n<tr><td>";
	echo ww('MotivationForHospitality');
	echo ":</td>";
	echo "<td colspan=2><textarea name=MotivationForHospitality cols=40 rows=6>";
	if ($m->MotivationForHospitality > 0)
		echo FindTrad($m->MotivationForHospitality);
	echo "</textarea></td>";

	//  todo process this with the main address
	//  echo "<tr><td>" ;
	//  echo ww('GettingHere') ;
	//  echo ":</td>" ;
	//  echo "<td colspan=2><textarea name=IdGettingThere cols=40 rows=6>" ;
	//  if ($m->IdGettingThere>0) echo FindTrad($m->IdGettingThere) ;
	//  echo "</textarea></td>" ;

	echo "<tr><td>";
	echo ww('Website');
	echo ":</td>";
	echo "<td colspan=2><textarea name=WebSite cols=40 rows=1 >", $m->WebSite, "</textarea></td>";

	$max = count($TGroups);
	if ($max > 0) {
		echo "\n<tr><th colspan=3><br><br>", ww("MyGroups", $m->Username), "</th>";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "\n<tr><td colpsan=2>", ww("Group_" . $TGroups[$ii]->Name), "</td>", "<td>";
			echo "<textarea cols=40 rows=6 name=\"", "Group_" . $TGroups["$ii"]->Name, "\">";
			if ($TGroups[$ii]->Comment > 0)
				echo FindTrad($TGroups[$ii]->Comment);
			echo "</textarea>";
			if (HasRight("Beta","GroupMessage")) { 
			   echo "<br> BETA " ;
			   echo "<input type=checkbox name=\"AcceptMessage_".$TGroups[$ii]->Name."\" " ;
			   if ($TGroups[$ii]->IacceptMassMailFromThisGroup=="yes") echo "checked" ;
			   echo "> " ;
			   echo ww('AcceptMessageFromThisGroup') ;
			}
			else {
			   echo "<input type=hidden name=\"AcceptMessage_".$TGroups[$ii]->Name."\" value=\"".$TGroups[$ii]->IacceptMassMailFromThisGroup."\">" ;
			}
			
			echo "</td>";
		}
	}

	echo "<tr><td>";
	echo ww('ProfileOrganizations');
	echo ":</td>";
	echo "<td colspan=2><textarea name=Organizations cols=40 rows=6>";
	if ($m->Organizations > 0)
		echo FindTrad($m->Organizations);
	echo "</textarea></td>";

	if ($m->Accomodation != "") {
		echo "<tr><td>";
		echo ww("ProfileAccomodation");
		echo ":</td>";
		echo "<td colspan=2>";
		$tt = $_SYSHCVOL['Accomodation'];
		$max = count($tt);
		echo "<select name=Accomodation>\n";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "<option value=\"" . $tt[$ii] . "\"";
			if ($tt[$ii] == $m->Accomodation)
				echo " selected ";
			echo ">", ww("Accomodation_" . $tt[$ii]), "</option>\n";
		}
		echo "</select>\n";
		echo "</td>";
	}

	echo "<tr><td>";
	echo ww('ProfileNumberOfGuests');
	echo ":</td>";
	echo "<td colspan=2><input name=MaxGuest type=text size=3 value=\"", $m->MaxGuest;
	echo "\"></td>";

	echo "<tr><td>";
	echo ww('ProfileMaxLenghtOfStay');
	echo ":</td>";
	echo "<td colspan=2><input name=MaxLenghtOfStay type=text size=40 value=\"";
	if ($m->MaxLenghtOfStay > 0)
		echo FindTrad($m->MaxLenghtOfStay);
	echo "\"></td>";

	echo "<tr><td>";
	echo ww('ProfileILiveWith');
	echo ":</td>";
	echo "<td colspan=2><input name=ILiveWith type=text size=40 value=\"";
	if ($m->ILiveWith > 0)
		echo FindTrad($m->ILiveWith);
	echo "\"></td>";

	echo "<tr><td>";
	echo ww('ProfileAdditionalAccomodationInfo');
	echo ":</td>";
	echo "<td colspan=2><textarea name=AdditionalAccomodationInfo cols=40 rows=6>";
	if ($m->AdditionalAccomodationInfo > 0) {
		echo FindTrad($m->AdditionalAccomodationInfo);
	}
	echo "</textarea></td>";

	$Relations=$m->Relations ;
	$max = count($Relations);
	if ($max > 0) {
		echo "\n<tr><th colspan=3><br><br>", ww('MyRelations'), "</th>";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "\n<tr><td colpsan=2>", LinkWithPicture($Relations[$ii]->Username,$Relations[$ii]->photo),"<br>",$Relations[$ii]->Username, "</td>";
			echo "<td align=right>";
			echo "<textarea cols=40 rows=6 name=\"", "RelationComment_" . $Relations["$ii"]->id, "\">";
			echo $Relations[$ii]->Comment ;
			echo "</textarea>";
			echo "<br><a href=\"editmyprofile.php?action=delrelation&Username=",$Relations[$ii]->Username,"\"  onclick=\"return confirm('Confirm delete ?');\">",ww("delrelation",$Relations[$ii]->Username),"</a></td>\n" ;
		}
	}

	$max = count($m->TabRestrictions);

	echo "<tr><td>";
	echo ww('ProfileRestrictionForGuest');
	echo ":</td>";
	echo "<td colspan=2>\n<ul>";
	for ($ii = 0; $ii < $max; $ii++) {
		echo "<input type=checkbox name=\"check_" . $m->TabRestrictions[$ii] . "\" ";
		if (strpos($m->Restrictions, $m->TabRestrictions[$ii]) !== false)
			echo "checked";
		echo ">";
		echo "&nbsp;&nbsp;", ww("Restriction_" . $m->TabRestrictions[$ii]), "<br>\n";
	}
	echo "</ul></td>";

	echo "<tr><td>";
	echo ww('ProfileOtherRestrictions');
	echo ":</td>";
	echo "<td colspan=2><ul><textarea name=OtherRestrictions cols=40 rows=3>";
	if ($m->OtherRestrictions > 0) {
		echo FindTrad($m->OtherRestrictions);
	}
	echo "</textarea></ul></td>";

	echo "\n<tr><td>";
	echo ww('SignupBirthDate');
	echo "</td>";
	echo "<td colspan=2>";
	echo $m->BirthDate;
	echo "\n &nbsp;&nbsp;&nbsp;&nbsp; <input Name=HideBirthDate type=checkbox ";
	if ($m->HideBirthDate == "Yes")
		echo " checked ";
	echo "> ", ww("Hidden");
	echo "</td>";

	echo "\n<tr><td colspan=3 align=center><input type=submit name=submit value=submit></td>";
	echo "</table>\n";
	echo "</form>\n";

	echo "	</div>";
	echo "	</div>";
	echo "				</div>";
	echo "				<div class=\"clear\" />";
	echo "			</div>	";
	echo "			<div class=\"clear\" />	";
	echo "		</div>	";
	echo "		</div>	";
	echo "	</div>	";


	include "footer.php";

}
?>
