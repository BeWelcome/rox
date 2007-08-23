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
// THis function returns the param to link to the url
function ParamUrl() {
	$strurl="&Username=".GetStrParam("Username") ;
	$strurl.="&Gender=".GetStrParam("Gender") ;
	$strurl.="&Age=".GetStrParam("Age") ;
	$strurl.="&IdCountry=".GetParam("IdCountry") ;
	$strurl.="&IdCity=".GetParam("IdCity") ;
	$strurl.="&IdRegion=".GetParam("IdRegion") ;
	$strurl.="&IdGroup=".GetParam("IdGroup") ;
	$strurl.="&TextToFind=".GetStrParam("TextToFind") ;
	$strurl.="&IncludeInactive=".GetStrParam("IncludeInactive") ;

	if (GetStrParam("MapSearch","")!="") {
	   $strurl.="&MapSearch=".GetStrParam("MapSearch") ;
	   $strurl.="&bounds_center_lat=".GetParam("bounds_center_lat") ;
	   $strurl.="&bounds_center_lng=".GetParam("bounds_center_lng") ;
	   $strurl.="&bounds_zoom=".GetParam("bounds_zoom") ;
	}

	$strurl.="&CityName=".GetStrParam("CityName") ;
	$strurl.="&TypicOffer=".urlencode(serialize(GetArrayParam("TypicOffer")));
	return($strurl) ;
} // end of ParamUrl

// This function provide a pagination
function _Pagination($maxpos) {
    $curpos=GetParam("start_rec",0) ; // find current pos (0 if not)
		$width=GetParam("limitcount",10); // Number of records per page
		$PageName=$_SERVER["PHP_SELF"] ;
		
// Find the url parameters
		$strurl="action=Find".ParamUrl() ; ;
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
	$IdCountry=GetParam("IdCountry",0) ;
	$IdCity=GetParam("IdCity",0) ;
	if ($max>0) {

	   echo "          <div class=\"info\">\n";
	   echo "            <table>\n";
	   
	   // If the country is specified, display id
	   if ($IdCountry !='0') {
	   	  echo "            <tr>\n";
	   	  echo "              <th colspan=5 align=center>",getcountrynamebycode($IdCountry),"</th>\n" ;
	   }
	   echo "              <tr>\n";
	   echo "                <th>" ;
  	   if ($IdCountry !='0') {
	   	   echo "members<br>" ;
	   	   if (GetParam("OrderBy")==12) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=13\">",ww("City"),"</a></b>" ;
	   	    }
	   		elseif (GetParam("OrderBy")==13) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=12\">",ww("City"),"</a></b>" ;
	   		}
	   		else {
		   		echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=12\">",ww("City"),"</a>" ;
	   		}
	   }
	   else {
	   	   echo "members<br>" ;
	   	   if (GetParam("OrderBy")==10) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=11\">",ww("Country"),"</a></b>" ;
	   	    }
	   		elseif (GetParam("OrderBy")==11) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=10\">",ww("Country"),"</a></b>" ;
	   		}
	   		else {
		   		echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=10\">",ww("Country"),"</a>" ;
	   		}
	   }
	   echo "</th>\n";
	   echo "                <th>",ww("ProfileSummary"),"</th>\n";
	   echo "                <th>";
	   if (GetParam("OrderBy")==4) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=5\">",ww("ProfileAccomodation"),"</a></b>" ;
	   }
	   elseif (GetParam("OrderBy")==5) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=4\">",ww("ProfileAccomodation"),"</a></b>" ;
	   }
	   else {
		   		echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=4\">",ww("ProfileAccomodation"),"</a>" ;
	   }
	   echo "</th>\n";
	   echo "                <th>" ;
	   if (GetParam("OrderBy")==2) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=3\">",ww("LastLogin"),"</a></b>" ;
	   }
	   elseif (GetParam("OrderBy")==3) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=2\">",ww("LastLogin"),"</a></b>" ;
	   }
	   else {
		   		echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=2\">",ww("LastLogin"),"</a>" ;
	   }

	   echo "</th>\n";
	   echo "                <th>";
	   if (GetParam("OrderBy")==8) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=9\">",ww("NbCurrentComments"),"</a></b>" ;
	   }
	   elseif (GetParam("OrderBy")==9) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=8\">",ww("NbCurrentComments"),"</a></b>" ;
	   }
	   else {
		   		echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=9\">",ww("NbCurrentComments"),"</a>" ;
	   }
	   echo "</th>\n";
	   echo "                <th>" ;
	   if (GetParam("OrderBy")==6) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=7\">",ww("Age"),"</a></b>" ;
	   }
	   elseif (GetParam("OrderBy")==7) {
		   		echo "<b><a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=6\">",ww("Age"),"</a></b>" ;
	   }
	   else {
		   		echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=Find".ParamUrl()."&OrderBy=6\">",ww("Age"),"</a>" ;
	   }
	   echo "</th>\n" ;
	   $info_styles = array(0 => "              <tr class=\"blank\" align=\"left\" valign=\"center\">\n", 1 => "              <tr class=\"highlight\" align=\"left\" valign=\"center\">\n");
	   for ($ii=0;$ii<$max;$ii++) {
	   	   $m=$TM[$ii] ;
		   echo $info_styles[($ii%2)]; // this display the <tr>
		   echo "                <td class=\"memberlist\">" ;
		   if (($m->photo != "") and ($m->photo != "NULL")) {
            echo LinkWithPicture($m->Username,$m->photo);
		   }
		   echo "<br>", LinkWithUsername($m->Username);
  	   	   if ($IdCountry ==0) echo "<br>", $m->CountryName;
  	   	   if ($IdCity ==0) echo "<br>", $m->CityName;
		   echo "</td>\n" ;
		   echo "                <td class=\"memberlist\" valign=\"top\">" ;
		   echo $m->ProfileSummary ;
		   echo "                </td>\n";
		   echo "                <td class=\"memberlist\" align=\"center\">" ;
			echo ShowAccomodation($m);

		   echo "</td>\n" ;
		   echo "                <td class=\"memberlist\">" ;
   	   echo $m->LastLogin ;
		   echo "</td>\n" ;
		   echo "                <td class=\"memberlist\" align=center>" ;
		   echo $m->NbComment ;
		   echo "</td>\n" ;
		   echo "                <td class=\"memberlist\" align=center>" ;
		   echo $m->Age ;
		   echo "</td>\n" ;
		   echo"              </tr>\n" ;
	   }
	   echo "            </table>\n" ;
     echo "          </div>\n"; 
	} // end if $max>0

	_Pagination($maxpos) ;


} // end of   ShowMembers($TM) ;

function ShowAccomodation($m) {
   if (strstr($m->Accomodation, "anytime"))
   return "<img src=\"images/yesicanhost.gif\"  title=\"".ww("CanOfferAccomodationAnytime")."\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />";
   if (strstr($m->Accomodation, "yesicanhost"))
   return "<img src=\"images/yesicanhost.gif\" title=\"".ww("CanOfferAccomodation")."\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />";
   if (strstr($m->Accomodation, "dependonrequest"))
   return "<img src=\"images/dependonrequest.gif\"  title=\"".ww("CanOfferdependonrequest")."\" width=\"30\" height=\"30\" alt=\"dependonrequest\" />";
   if (strstr($m->Accomodation, "neverask"))
   return "<img src=\"images/neverask.gif\" title=\"".ww("CannotOfferneverask")."\" width=\"30\" height=\"30\" alt=\"neverask\" />";
   if (strstr($m->Accomodation, "cannotfornow"))
   return "<img src=\"images/neverask.gif\"  title=\"". ww("CannotOfferAccomForNow")."\" width=\"30\" height=\"30\" alt=\"neverask\" />";
}

function ShowMembersOnMap() {

	global $_SYSHCVOL;

	if($_SYSHCVOL['SiteName'] == "localhost") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxShnDj7H5mWDU0QMRu55m8Dc2bJEg";
	else if($_SYSHCVOL['SiteName'] == "test.bewelcome.org") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBQw603b3eQwhy2K-i_GXhLp33dhxhTnvEMWZiFiBDZBqythTBcUzMyqvQ";
	else if($_SYSHCVOL['SiteName'] == "alpha.bewelcome.org") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBTnd2erWePPER5A2i02q-ulKWabWxTRVNKdnVvWHqcLw2Rf2iR00Jq_SQ";
	else $google_conf = PVars::getObj('config_google');

?>
  <script src="../script/prototype.js" type="text/javascript"></script>
  <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?= $google_conf->maps_api_key ?>"
    type="text/javascript"></script>
  <div class="info">
		<h3><? echo ww("FindPeopleMap"); ?></h3>
		<br /><br />
		<div>
    <form action="#" onsubmit="showAddress(this.address.value); return false">
      <p>
        <input type="text" size="60" name="address" id="address" value="" />
        <input id="text_search" type="submit" value="Search using text" />
      </p>
    </form>
		<br />
    <p>
		<table width='100%'><tr><td>
    <form action="#" onsubmit="update_map_loc(); return false">
        <input id="map_search" type="submit" value="Search using map boundaries" />
    </form>
		</td><td style='text-align: right;'>
		<form action="#" onsubmit="map.clearOverlays(); getElementById('member_list').innerHTML=''; return false">
        <input type="submit" value="Clear the map" />
    </form>
		</td></tr></table>
    </p>
		</div>
		<div id="map" style="width: 580px; height: 420px; border: solid thin"></div>
	  <a href="<?=$_SERVER['PHP_SELF']?>?map=off">Disable maps for this browser session</a>
	  <br>
		<div id="member_list"></div>
  </div>
 	<script src="../script/findpeople.js" type="text/javascript"></script>
 <?
} // end of ShowMembersOnMap

// This routine dispaly the form to allow to find people
// if they is already a result is TM, then the list of resulting members is provided
function DisplayFindPeopleForm($TGroup,$TM,$maxpos=-1) {
	global $title, $searchtext, $menutab, $ActionList;
	$title = ww('findpeopleform', $searchtext);
	require_once "header.php";

	Menu1("", ww('QuickSearchPage')); // Displays the top menu

	Menu2("findpeople.php", ww('findpeoplePage')); // Displays the second menu

	echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser_bg\">\n";	
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";
	
	menufindmembers("findpeople.php" . $menutab, $title);
	echo "      </div>\n";

	ShowLeftColumn($ActionList,VolMenu())  ; // Show the Actions
	ShowAds(); // Show the Ads
	
	// middle column
	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	
	if(GetParam("map") == "off") $_SESSION['map'] = "off";
	if(array_key_exists('map', $_SESSION) and $_SESSION['map'] == "off") $MapOn = false;
	else $MapOn = true;

	$IdCountry=GetParam("IdCountry") ;
	$scountry = ProposeCountry($IdCountry, "findpeopleform", true);
//echo "IdMember(GetStrParam(\"TextToFind\")=",IdMember(GetStrParam("TextToFind"));
//echo " GetParam(\"OrUsername\",0)=",GetParam("OrUsername",0),"<br>\n" ;
	echo "          <div class=\"info\">\n";
	echo "            <form id=\"findpeopleform\" method=post action=",bwlink("findpeople.php")." name=findpeopleform>\n" ;
	if($MapOn) {
		echo "						<input type=\"hidden\" name=\"MapSearch\" id=\"MapSearch\" />\n";
		echo "						<input type=\"hidden\" name=\"bounds_zoom\" id=\"bounds_zoom\" />\n";
		echo "						<input type=\"hidden\" name=\"bounds_center_lat\" id=\"bounds_center_lat\" />\n";
		echo "						<input type=\"hidden\" name=\"bounds_center_lng\" id=\"bounds_center_lng\" />\n";
		echo "						<input type=\"hidden\" name=\"bounds_sw_lat\" id=\"bounds_sw_lat\" />\n";
		echo "						<input type=\"hidden\" name=\"bounds_ne_lat\" id=\"bounds_ne_lat\" />\n";
		echo "						<input type=\"hidden\" name=\"bounds_sw_lng\" id=\"bounds_sw_lng\" />\n";
		echo "						<input type=\"hidden\" name=\"bounds_ne_lng\" id=\"bounds_ne_lng\" />\n";
		echo "            <input type=\"hidden\" name=\"CityName\" id=\"CityName\" />\n";
		echo "            <input type=\"hidden\" name=\"IdCountry\" id=\"IdCountry\" />\n";
		echo "            <input type=\"hidden\" name=\"start_rec\" id=\"start_rec\" />\n";
		echo "            <input type=\"hidden\" name=\"limitcount\" id=\"limitcount\" />\n";
	}
	echo "              <h3>", ww("FindPeopleSearchTerms"), "</h3>\n";
  echo "              <p>", ww("FindPeopleSearchTermsExp"), "</p>\n";
	echo "              <ul class=\"floatbox input_float\">\n";
	echo "                <li>\n";
	echo "                  <p><strong class=\"small\">",ww("Username"),"</strong><br />\n";
	echo "                  <input type=\"text\" name=\"Username\" size=\"30\" maxlength=\"30\" value=\"";
	if ((GetParam("OrUsername",0)==1)and(IdMember(GetStrParam("TextToFind"))!=0)) { // in
		 echo GetStrParam("TextToFind") ;
	}
	else {
		 echo GetStrParam("Username") ;
	}
	echo "\" /></p>\n";
	echo "                </li>\n";
	if(!$MapOn) {
		echo "                <li>\n";
		echo "                  <p><strong class=\"small\">",ww("CityName"),"</strong><br />\n";
		echo "                  <input type=\"text\" id=\"CityName\" name=\"CityName\" size=\"30\" maxlength=\"30\" value=\"",GetStrParam("CityName"),"\" />\n";
		echo "                  </p>\n";
		echo "                </li>\n";
	}
	echo "                <li>\n";
	echo "                  <p><strong class=\"small\">",ww("Age"),"</strong><br />\n";
	echo "                  <input type=\"text\" name=\"Age\" size=\"30\" maxlength=\"30\" value=\"",GetStrParam("Age"),"\" />\n";
	echo "                  </p>\n";
	echo "                </li>\n";
	echo "                <li>\n";
	echo "                  <p><strong class=\"small\">",ww("TextToFind"),"</strong><br />\n";
	echo "                  <input type=\"text\" name=\"TextToFind\" size=\"30\" maxlength=\"30\" value=\"" ;
   if ((GetParam("OrUsername",0)==0)or(IdMember(GetStrParam("TextToFind")==0))) { // if we were not comming from the quicksearch 
	   echo GetStrParam("TextToFind") ;
	}
	echo "\"/></p>\n";
	echo "                </li>\n";
	echo "              </ul>\n";
	echo "              <br /><br />\n";
	echo "              <h3>", ww("FindPeopleFilter"), "</h3>\n";
//	echo "              <p>", ww("FindPeopleSearchFiltersExp"), "</p>\n";
	echo "              <ul class=\"floatbox select_float\">\n";
	if(!$MapOn) {
		echo "                <li>\n";
		echo "                  <p><strong class=\"small\">",ww("Country"),"</strong><br />\n";
		echo $scountry;
		echo "                  </p>\n";
		echo "                </li>\n";
	}


	echo "                <li>\n";
	echo "                  <p><strong class=\"small\">",ww("Gender"),"</strong><br />\n";
	echo "                  <select Name=\"Gender\">" ;
	echo "                    <option value=\"0\"></option>" ;
	echo "                    <option value=\"male\"" ;
	if (GetStrParam("Gender")=="male") echo " selected=\"selected\"" ;
	echo ">",ww("Male"),"</option>" ;
	echo "                    <option value=female";
	if (GetStrParam("Gender")=="female") echo " selected=\"selected\"" ;
	echo ">",ww("Female"),"</option>" ;
	echo "                 </select>" ;
	echo "                  </p>\n";
	echo "                </li>\n";
	echo "                <li>\n";
	$iiMax = count($TGroup);
	echo "                  <p><strong class=\"small\">",ww("Groups"),"</strong><br />\n";
	echo "                  <select name=\"IdGroup\">";
	echo "                    <option value=\"0\"></option>" ;
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "                    <option value=".$TGroup[$ii]->id ;
		if (GetParam("IdGroup",0)==$TGroup[$ii]->id) echo " selected" ;
		echo ">",ww("Group_" . $TGroup[$ii]->Name),"</option>\n";
	}
	echo "                  </select>\n";
	echo "                  </p>\n";
	echo "                </li>\n";
	
	echo "                <li>\n";
	echo "                  <p><strong class=\"small\">",ww("WhoOfferTypicOffer"),"</strong><br />\n";
	echo " <select name=TypicOffer[] multiple>" ;

	$TabTypicOffer = sql_get_set("members", "TypicOffer");

	$TypicOffer=GetArrayParam("TypicOffer");
	for ($ii=0;$ii<count($TabTypicOffer);$ii++) {
			echo "<option value=\"".$TabTypicOffer[$ii]."\"" ;
			if ($TabTypicOffer[$ii] == $TypicOffer) echo " selected " ;
			echo ">",ww("Filter_".$TabTypicOffer[$ii]),"</option>" ;
	}
	echo "</select>\n" ;
	echo "                </li>\n";

	if($MapOn) {
		echo "                <li>\n";
		echo "                  <p><strong class=\"small\">",ww("FindPeopleSortOrder"),"</strong><br />\n";
		echo "                  <select Name=\"OrderBy\">" ;
		echo "                    <option value=\"0\"></option>" ;
		echo "                    <option value=\"4\">",ww("Accomodation"),"</option>" ;
		echo "                    <option value=\"5\">",ww("Accomodation (reversed)"),"</option>" ;
		echo "                    <option value=\"6\">",ww("Age"),"</option>" ;
		echo "                    <option value=\"7\">",ww("Age (reversed)"),"</option>" ;
		echo "                    <option value=\"12\">",ww("City"),"</option>" ;
		echo "                    <option value=\"13\">",ww("City (reversed)"),"</option>" ;
		echo "                    <option value=\"10\">",ww("Country"),"</option>" ;
		echo "                    <option value=\"11\">",ww("Country (reversed)"),"</option>" ;
		echo "                    <option value=\"2\">",ww("LastLogin"),"</option>" ;
		echo "                    <option value=\"3\">",ww("LastLogin (reversed)"),"</option>" ;
		echo "                    <option value=\"8\">",ww("Comments"),"</option>" ;
		echo "                    <option value=\"9\">",ww("Comments (reversed)"),"</option>" ;
		echo "                 </select>" ;
		echo "                  </p>\n";
		echo "                </li>\n";
	}
	echo "              </ul>\n";


	echo "              <br /><br />\n";


	echo "              <p>\n";
	echo "            <input name=\"IncludeInactive\"type=\"checkbox\" ";
	if (GetStrParam("IncludeInactive")=="on") echo "checked" ;
	echo ">&nbsp;",ww("FindPeopleIncludeInactive") ;
	if(!$MapOn) {
		echo "              <br /><br />\n";
		echo "              <input type=\"submit\" id=\"submit\" value=\"",ww("FindPeopleSubmit"),"\" name=\"action\" >\n";
	}
	echo "            </p>\n" ;
	echo "          </form>\n" ;
	echo "        </div>\n";
	if($MapOn) showMembersOnMap() ;

	/*
	echo "              <table id=\"preferences\">\n";
	echo "                <tr>\n";
	echo "                  <td colspan=3>\n" ;
	if (IsLoggedIn()) // wether the user is logged or not the text will be different
	   echo ww("FindPeopleExplanation")  ;
	else
	   echo ww("FindPeopleExplanationNotLogged") ;
	echo "</td>\n" ;
	echo "                </tr>\n";
	echo "                <tr>\n";
	echo "                  <td class=\"label\">",ww("Country"),"</td>\n";
	echo "                  <td>",$scountry,"</td>\n";
	echo "                  <td></td>" ;
	echo "                </tr>\n";
	echo "                <tr>\n";
	echo "                  <td class=\"label\">",ww("Username"),"</td>\n";
	echo "                  <td><input type=text name=Username value=\"";
   if ((GetParam("OrUsername",0)==1)and(IdMember($TextToFind)!=0)) { // in
		 echo GetStrParam("TextToFind") ;
	}
	else {
		 echo GetStrParam("Username") ;
	}
	echo "\"></td>\n";
	echo "                <td>",ww("FindPeopleUsernameExp"),"</td>\n";
	echo "                <td></td>\n" ;
  echo "              </tr>\n";
	echo "              <tr>\n";
	echo "                <td class=\"label\">",ww("Gender"),"</td><td>" ;
	echo "                  <select Name=Gender>" ;
	echo "                    <option value=0></option>" ;
	echo "                    <option value=male" ;
	if (GetStrParam("Gender")=="male") echo " selected" ;
	echo ">",ww("Male"),"</option>" ;echo "                  <select Name=Gender>" ;
	echo "                    <option value=0></option>" ;
	echo "                    <option value=male" ;
	if (GetStrParam("Gender")=="male") echo " selected" ;
	echo ">",ww("Male"),"</option>" ;
	echo "                    <option value=female";
	if (GetStrParam("Gender")=="female") echo " selected" ;
	echo ">",ww("Female"),"</option>" ;
	echo "                 </select>" ;
	echo "                    <option value=female";
	if (GetStrParam("Gender")=="female") echo " selected" ;
	echo ">",ww("Female"),"</option>" ;
	echo "                 </select>" ;
	echo "</td>\n";
	echo "                <td>",ww("FindPeopleGenderExp"),"</td>" ;
	echo "              </tr>\n";
	echo "              <tr>\n";
	echo "                <td class=\"label\">",ww("Age"),"</td>\n";
	echo "                <td><input type=text name=Age value=\"",GetStrParam("Age"),"\"></td><td>",ww("AgePeopleGenderExp"),"</td>" ;
	echo "              </tr>\n";
	echo "              <tr>\n";
	echo "                <td class=\"label\">",ww("TextToFind"),"</td>\n";
	echo "                <td><input type=echo "          </form>\n" ;text name=TextToFind value=\"" ;
   if ((GetParam("OrUsername",0)==0)or(IdMember($TextToFind)==0)) { // if we were not comming from the quicksearch 
	   echo GetStrParam("TextToFind") ;
	}
	echo "\"></td>\n";
	echo "                <td>",ww("FindTextExp"),"</td>" ;
	echo "              </tr>\n";
	$iiMax = count($TGroup);
	echo "              <tr>\n";
	echo "                <td class=\"label\" colspan=1>",ww("Groups"),"</td>\n";
	echo "                <td>\n";
	echo "                  <select name=IdGroup>";
	echo "                    <option value=0></option>" ;
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "                    <option value=".$TGroup[$ii]->id ;
		if (GetParam("IdGroup",0)==$TGroup[$ii]->id) echo " checked" ;
		echo ">",ww("Group_" . $TGroup[$ii]->Name),"</option>\n";
	}
	echo "                  </select>\n" ;
	echo "                </td>\n";
	echo "                <td></td>\n";
	echo "              </tr>\n";
	echo "            </table>\n";
	echo "            <p align=\"center\">\n";
	echo "            <input type=\"submit\" id=\"submit\" value=\"",ww("FindPeopleSubmit"),"\" name=\"action\" >\n";
	echo "            <input type=\"checkbox\" ";
	if (GetStrParam("IncludeInactive"=="on")) echo "checked" ;
	echo ">&nbsp;",ww("FindPeopleIncludeInactive") ;
	echo "            </p>\n" ;
	echo "          </form>\n" ;
*/

	// echo "        </div>\n";


	if ($maxpos>0) { // display the members resulting list if there is one
	   ShowMembers($TM,$maxpos) ;
	}
	elseif($maxpos==0) { // If explicitely no members are found
	  echo "          <div class=\"info\">\n";
		echo "            <p align=\"center\" class=\"note\">",ww("ZeroResults"),"</p>\n" ;
		echo "          </div>\n";
	}
	elseif($maxpos==-2) { // If explicitely no criteria was propose for result
		echo "<p>",ww("PleaseProvideSomeCriteria"),"</p>\n" ;
	}

	require_once "footer.php";
}
?>
