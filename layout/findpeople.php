<?php
require_once ("menus.php");



// This function provide a pagination
function _Pagination($maxpos) {
    $curpos=GetParam("start_rec",0) ; // find current pos (0 if not)
		$width=GetParam("limitcount",10); // Number of records per page
		$PageName=$_SERVER["PHP_SELF"] ;
		
// Find the url parameters
		$strurl="action=Find" ;
		$strurl.="&Username=".GetStrParam("Username") ;
		$strurl.="&Gender=".GetStrParam("Gender") ;
		$strurl.="&Age=".GetStrParam("Age") ;
		$strurl.="&IdCountry=".GetParam("IdCountry") ;
		$strurl.="&TextToFind=".GetStrParam("TextToFind") ;
		$strurl.="&ProposeGroup=".GetStrParam("ProposeGroup") ;
		$strurl.="&IncludeInactive=".GetStrParam("IncludeInactive") ;
		$strurl.="&OrderBy=".GetStrParam("OrderBy") ;
		
//		echo "width=",$width,"<br>" ;
//		echo "curpos=",$curpos,"<br>" ;
//		echo "maxpos=",$maxpos,"<br>" ;
		echo "\n<center>" ;
		for ($ii=0;$ii<$maxpos;$ii=$ii+$width) {
				$i1=$ii ;
				$i2=min($ii+$width,$maxpos) ;
				if (($curpos>=$i1) and ($curpos<$i2)) { // mark in bold if it is the current position
					 echo "<b>" ;
				}
				echo "<a href=\"",$PageName,"?".$strurl."&start_rec=",$i1,"\">",$i1+1,"..",$i2,"</a> " ;
				if (($curpos>=$i1) and ($curpos<$i2)) { // end of mark in bold if it is the current position
					 echo "</b>" ;
				}
		}
		echo "</center>\n" ;
} // end of function Pagination


// ShowMembers display the list of found members
function ShowMembers($TM,$maxpos) {
	$max=count($TM) ;
	if ($max>0) {
	   echo "<center>" ;
	   echo "<table >" ;
	   echo "<tr><th>members</th><th>",ww("ProfileSummary"),"</th><th>",ww("ProfileAccomodation"),"</th><th>",ww("LastLogin"),"</th><th>",ww("NbCurrentComments"),"</th>\n" ;
	   $info_styles = array(0 => "<tr class=\"blank\" align=left valign=center>", 1 => "<tr class=\"highlight\" align=left valign=center>");
	   for ($ii=0;$ii<$max;$ii++) {
	   	   $m=$TM[$ii] ;
		   echo $info_styles[($ii%2)]; // this display the <tr>
		   echo "<td>" ;
		   if (($m->photo != "") and ($m->photo != "NULL")) {
            echo LinkWithPicture($m->Username,$m->photo);
		   }
		   echo "<br>", LinkWithUsername($m->Username);
		   echo "<br>", $m->CountryName;
		   echo "<br>", $m->CityName;
		   echo "</td>" ;
		   echo "<td>" ;
		   echo $m->ProfileSummary ;
		   echo "<td align=center>" ;

		   if (strstr($m->Accomodation, "anytime"))
		   echo "              <img src=\"images/yesicanhost.gif\"  title=\"",ww("CanOfferAccomodationAnytime"),"\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />\n";
		   if (strstr($m->Accomodation, "yesicanhost"))
		   echo "              <img src=\"images/yesicanhost.gif\" title=\"",ww("CanOfferAccomodation"),"\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />\n";
		   if (strstr($m->Accomodation, "dependonrequest"))
		   echo "              <img src=\"images/dependonrequest.gif\"  title=\"",ww("CanOfferdependonrequest"),"\" width=\"30\" height=\"30\" alt=\"dependonrequest\" />\n";
		   if (strstr($m->Accomodation, "neverask"))
		   echo "              <img src=\"images/neverask.gif\" title=\"",ww("CannotOfferneverask"),"\" width=\"30\" height=\"30\" alt=\"neverask\" />\n";
		   if (strstr($m->Accomodation, "cannotfornow"))
		   echo "              <img src=\"images/neverask.gif\"  title=\"", ww("CannotOfferAccomForNow"),"\" width=\"30\" height=\"30\" alt=\"neverask\" />\n"; 

		   echo "</td>" ;
		   echo "<td>" ;
		   echo $m->LastLogin ;
		   echo "</td>" ;
		   echo "<td align=center>" ;
		   echo $m->NbComment ;
		   echo "</td>" ;
		   echo" \n" ;
	   }
	   echo "</table>" ;
	   echo "</center>" ;
	} // end if $max>0

	_Pagination($maxpos) ;


} // end of   ShowMembers($TM) ;



// This routine dispaly the form to allow to find people
// if they is already a result is TM, then the list of resulting members is provided
function DisplayFindPeopleForm($ProposeGroup=false,$TGroup,$TM,$maxpos) {
	global $title;
	$title = ww('findpeopleform', $searchtext);
	require_once "header.php";

	Menu1("", ww('QuickSearchPage')); // Displays the top menu

	Menu2("findpeople.php", ww('findpeoplePage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);
	
	
	if (count($TM)>0) { // display the members resulting list if there is one
	   ShowMembers($TM,$maxpos) ;
	}
	
	$IdCountry=GetParam("IdCountry") ;
	$scountry = ProposeCountry($IdCountry, "findpeopleform");
	if ($IdCountry!=0) {
	   $IdCity=GetParam("IdCity") ;
	   $scity = ProposeCity($IdCity, 0, "findpeopleform",$CityName,$IdCountry);
	}

	echo "\n<br><center>\n";
	echo "<form method=post action=findpeople.php name=findpeopleform>\n" ;
	echo "<table cellspacing=3\n";
	echo "<tr><td colspan=3>" ;
	if (IsLoggedIn()) // wether the user is logged or not the text will be different
	   echo ww("FindPeopleExplanation")  ;
	else
	   echo ww("FindPeopleExplanationNotLogged") ;
	echo "</td>\n" ;
	echo "<tr><td>",ww("Country"),"</td><td>",$scountry,"</td><td></td>" ;
	echo "<tr><td>",ww("Username"),"</td><td><input type=text name=Username value=\"",GetStrParam("Username"),"\"></td><td>",ww("FindPeopleUsernameExp"),"<td></td>" ;
	echo "<tr><td>",ww("Gender"),"</td><td>" ;
	echo "<select Name=Gender>" ;
	echo "<option value=0></option>" ;
	echo "<option value=male" ;
	if (GetStrParam("Gender")=="male") echo " selected" ;
	echo ">",ww("Male"),"</option>" ;
	echo "<option value=female";
	if (GetStrParam("Gender")=="female") echo " selected" ;
	echo ">",ww("Female"),"</option>" ;
	echo "</select>" ;
	echo "</td><td>",ww("FindPeopleGenderExp"),"</td>" ;
	echo "<tr><td>",ww("Age"),"</td><td><input type=text name=Age value=\"",GetStrParam("Age"),"\"></td><td>",ww("AgePeopleGenderExp"),"</td>" ;
	echo "<tr><td>",ww("TextToFind"),"</td><td><input type=text name=text value=\"",GetStrParam("TextToFind"),"\"></td><td>",ww("FindTextExp"),"</td>" ;
	if ($ProposeGroup) {
	   echo "<input type=hidden name=ProposeGroup value=1>" ;
	   echo "<tr><td colspan=3 align=center>",ww("FindPeopleTickGroup"),"</td>" ;
	   $iiMax = count($TGroup);
	   for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<tr><td colspan=2>";
		echo ww("Group_" . $TGroup[$ii]->Name);
		echo "</td>";
		echo "<td>";
		echo "<input type=checkbox " ;
		if (GetStrParam("Group_".$TGroup[$ii]->id)=="on") echo "checked" ;
		echo ">" ;
		echo "</td>";
	   }
	}
	else {
	   echo "<tr><td colspan=3  align=center>" ;
	   echo "<input type=submit value=\"",ww("FindPeopleAddGroup"),"\" name=action>" ;
	   echo "</td>" ;
	}
	echo "<tr><td><td  align=right>" ;
	echo "<input type=submit value=\"",ww("FindPeopleSubmit"),"\" name=action>&nbsp;&nbsp;</td>" ;
	echo "<td>","&nbsp; <input type=checkbox " ;
	if (GetStrParam("IncludeInactive"=="on")) echo "checked" ;
	echo ">&nbsp;",ww("FindPeopleIncludeInactive") ;
	echo "</td>" ;
	echo "</table>\n";
	echo "</form>" ;

	echo "</center>\n";
	require_once "footer.php";
}
?>
