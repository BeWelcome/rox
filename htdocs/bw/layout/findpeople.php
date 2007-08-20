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
	   if ($IdCountry !=0) {
	   	  echo "            <tr>\n";
	   	  echo "              <th colspan=5 align=center>",getcountryname($IdCountry),"</th>\n" ;
	   }
	   echo "              <tr>\n";
	   echo "                <th>" ;
  	   if ($IdCountry !=0) {
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
			echo ShowAccomidation($m);

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

function ShowAccomidation($m) {
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

function ShowMembersOnMap($TM,$maxpos) {
	global $_SYSHCVOL;

	if($_SYSHCVOL['SiteName'] == "localhost") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxShnDj7H5mWDU0QMRu55m8Dc2bJEg";
	else if($_SYSHCVOL['SiteName'] == "test.bewelcome.org") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBQw603b3eQwhy2K-i_GXhLp33dhxhTnvEMWZiFiBDZBqythTBcUzMyqvQ";
	else if($_SYSHCVOL['SiteName'] == "alpha.bewelcome.org") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBTnd2erWePPER5A2i02q-ulKWabWxTRVNKdnVvWHqcLw2Rf2iR00Jq_SQ";
	else $google_conf = PVars::getObj('config_google');
	
	$max=count($TM) ;
	if ($max>0) { // if they are selected members
?>
  <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?= $google_conf->maps_api_key ?>"
    type="text/javascript"></script>
  <div class="info">
		<div id="map" style="width: 550px; height: 400px; border: solid thin"></div>
  </div>
	<script type="text/javascript">

  //<![CDATA[
	var start = true;
	var map = null;
  function load() {
    if (GBrowserIsCompatible()) {
      map = new GMap2(document.getElementById("map"));
			map.addControl(new GLargeMapControl());
			map.addControl(new GMapTypeControl());
			map.enableDoubleClickZoom();
			GEvent.addListener(map, "click", function(overlay, point)	{
				if (overlay && overlay.un) {
						overlay.openInfoWindowHtml(
							overlay.photo + '<a href="member.php?cid=' +
							overlay.un + '">' +
							overlay.un + '</a><br>' +
							overlay.city + '<br>' +
							overlay.country + '<br>' //+
//							overlay.accomidations
						);
				}
			});
			GEvent.addListener(map, "moveend", function()	{
				update_map_loc();
			});

			var cnt = <?= $max ?>;
			var lats = [<? $lat = array(); foreach($TM as $tm) $lat[] = $tm->Latitude; echo implode(',', $lat); ?>];
			var lngs = [<? $lng = array(); foreach($TM as $tm) $lng[] = $tm->Longitude; echo implode(',', $lng); ?>];
<?
	if((GetStrParam("MapSearch","")!="") and GetParam("bounds_center_lat") and GetParam("bounds_center_lng") and GetParam("bounds_zoom")) {
		$average_lat = GetParam("bounds_center_lat");
		$average_lng = GetParam("bounds_center_lng");
		$scale = GetParam("bounds_zoom");
	}
	else if($max > 0) {
		$average_lat = 0;
		$min_lt = min($lat);
		$max_lt = max($lat);
		$min_lg = min($lng);
		$max_lg = max($lng);
		$spread_lg = abs($max_lg - $min_lg);
		if($spread_lg > 180 and $max_lg < 0) $max_lg += 360;
		$spread_lt = abs($max_lt - $min_lt);
		$average_lng = 0;
		foreach($lat as $lt) $average_lat += $lt;
		foreach($lng as $lg) $average_lng += $lg;
		$average_lat /= $max;
		$average_lng /= $max;
		if($spread_lg > 180 and $average_lng > 180) {
			$average_lng -= 360;
			$spread_lg -= 180;
		}
		$spread = max($spread_lg, $spread_lt);
		if($spread > 90) $scale = 1;
		else if($spread > 45) $scale = 2;
		else if($spread > 22) $scale = 3;
		else if($spread > 8) $scale = 4;
		else if($spread > 3) $scale = 5;
		else $scale = 6;
 }
 else {
  $average_lat = 25;
	$average_lng = 0;
	$scale = 1;
 }
?>
		  map.setCenter(new GLatLng(<? echo "$average_lat, $average_lng"; ?>), <?= $scale ?>);
			var uns = [<? $uns = array(); foreach($TM as $tm) $uns[] = "'$tm->Username'"; echo implode(',', $uns); ?>];
			var cities = [<? $cities = array(); foreach($TM as $tm) $cities[] = "'$tm->CityName'"; echo implode(',', $cities); ?>];
			var countries = [<? $countries = array(); foreach($TM as $tm) $countries[] = "'$tm->CountryName'"; echo implode(',', $countries); ?>];
			var photos = [<? $photos = array(); foreach($TM as $tm) {$photos[] = "'".LinkWithPicture($tm->Username, $tm->photo, 'map_style')."'";} echo implode(',', $photos); ?>];

			// Create our "tiny" marker icon
			var icon = new GIcon();
			icon.image = "images/gicon1.png";
			icon.shadow = "images/gicon1_shadow.png";
			icon.iconSize = new GSize(18, 27);
			icon.shadowSize = new GSize(18, 27);
			icon.iconAnchor = new GPoint(8, 27);
			icon.infoWindowAnchor = new GPoint(5, 1);

//			var accomidations = [<? $accomidations = array(); foreach($TM as $tm) {$accomidations[] = "'".ShowAccomidation($tm)."'";} echo implode(',', $accomidations); ?>];
			for (var i = 0; i < cnt; i++) {
				var lat = parseFloat(lats[i]);
				var lng = parseFloat(lngs[i]);
				var point = new GPoint(lng, lat);
				var marker = new GMarker(point, icon);
				marker.un = uns[i];
				marker.city = cities[i];
				marker.country = countries[i];
				marker.photo = photos[i];
//				marker.accomidation = accomidations[i];
				map.addOverlay(marker);
			}
		}
  }
	function update_map_loc() {
		if(start) {start = false; return;}
		var bounds = map.getBounds();
		document.getElementById('bounds_zoom').value = map.getZoom();
		var bounds_center = bounds.getCenter();
		var bounds_center_lat = bounds_center.lat();
		document.getElementById('bounds_center_lat').value = bounds_center_lat;
		var bounds_center_lng = bounds_center.lng();
		document.getElementById('bounds_center_lng').value = bounds_center_lng;
		var bounds_sw = bounds.getSouthWest();
		var bounds_ne = bounds.getNorthEast();
		var bounds_sw_lat = bounds_sw.lat();
		document.getElementById('bounds_sw_lat').value = bounds_sw_lat;
		var bounds_ne_lat = bounds_ne.lat();
		document.getElementById('bounds_ne_lat').value = bounds_ne_lat;
		var bounds_sw_lng = bounds_sw.lng();
		document.getElementById('bounds_sw_lng').value = bounds_sw_lng;
		var bounds_ne_lng = bounds_ne.lng();
		document.getElementById('bounds_ne_lng').value = bounds_ne_lng;
	}
	window.onload = load();
  //]]>
  </script>
<?
	} // end of  if they are selected members
} // end of ShowMembersOnMap

// This routine dispaly the form to allow to find people
// if they is already a result is TM, then the list of resulting members is provided
function DisplayFindPeopleForm($TGroup,$TM,$maxpos=-1) {
	global $title;
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
  ShowMembersOnMap($TM,$maxpos) ;

	$IdCountry=GetParam("IdCountry") ;
	$scountry = ProposeCountry($IdCountry, "findpeopleform");
//echo "IdMember(GetStrParam(\"TextToFind\")=",IdMember(GetStrParam("TextToFind"));
//echo " GetParam(\"OrUsername\",0)=",GetParam("OrUsername",0),"<br>\n" ;
	echo "          <div class=\"info\">\n";
	echo "            <form method=post action=",bwlink("findpeople.php")." name=findpeopleform>\n" ;
	echo "						<input type=\"hidden\" name=\"bounds_zoom\" value=\"".GetParam("bounds_zoom")."\" id=\"bounds_zoom\">\n";
	echo "						<input type=\"hidden\" name=\"bounds_center_lat\" value=\"".GetParam("bounds_center_lat")."\" id=\"bounds_center_lat\">\n";
	echo "						<input type=\"hidden\" name=\"bounds_center_lng\" value=\"".GetParam("bounds_center_lng")."\" id=\"bounds_center_lng\">\n";
	echo "						<input type=\"hidden\" name=\"bounds_sw_lat\" value=\"".GetParam("bounds_sw_lat")."\" id=\"bounds_sw_lat\">\n";
	echo "						<input type=\"hidden\" name=\"bounds_ne_lat\" value=\"".GetParam("bounds_ne_lat")."\" id=\"bounds_ne_lat\">\n";
	echo "						<input type=\"hidden\" name=\"bounds_sw_lng\" value=\"".GetParam("bounds_sw_lng")."\" id=\"bounds_sw_lng\">\n";
	echo "						<input type=\"hidden\" name=\"bounds_ne_lng\" value=\"".GetParam("bounds_ne_lng")."\" id=\"bounds_ne_lng\">\n";
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
	echo "                <li>\n";
	echo "                  <p><strong class=\"small\">",ww("CityName"),"</strong><br />\n";
	echo "                  <input type=\"text\" name=\"CityName\" size=\"30\" maxlength=\"30\" value=\"",GetStrParam("CityName"),"\" />\n";
	echo "                  </p>\n";
	echo "                </li>\n";
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
	echo "              <p>", ww("FindPeopleSearchFiltersExp"), "</p>\n";
	echo "              <ul class=\"floatbox select_float\">\n";
	echo "                <li>\n";
	echo "                  <p><strong class=\"small\">",ww("Country"),"</strong><br />\n";
	echo $scountry;
	echo "                  </p>\n";
	echo "                </li>\n";

	if (GetParam("IdCountry",0)!=0) {
	   echo "                <li>\n";
	   echo "                  <p><strong class=\"small\">",ww("City"),"</strong><br />\n";
	   echo "                  <input type=\"text\" name=\"CityName\" size=\"30\" maxlength=\"30\" value=\"",GetStrParam("CityName",""),"\"" ;
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
			if (in_array($TabTypicOffer[$ii],$TypicOffer,FALSE)) echo " selected " ;
			echo ">",ww("Filter_".$TabTypicOffer[$ii]),"</option>" ;
	}
	echo "</select>\n" ;
	echo "                </li>\n";

	echo "              </ul>\n";
	echo "              <br />\n";
	echo "              <p>\n";
	echo "            <input name=\"IncludeInactive\"type=\"checkbox\" ";
	if (GetStrParam("IncludeInactive")=="on") echo "checked" ;
	echo ">&nbsp;",ww("FindPeopleIncludeInactive") ;
	echo "              <br />\n";
	echo "            <input name=\"MapSearch\" type=\"checkbox\" ";
	if (GetStrParam("MapSearch")=="on") echo "checked" ;
	echo ">&nbsp;",ww("MapSearch") ;
	echo "              <br /><br />\n";
	echo "              <input type=\"submit\" id=\"submit\" value=\"",ww("FindPeopleSubmit"),"\" name=\"action\" >\n";
	echo "            </p>\n" ;
	echo "          </form>\n" ;
	echo "        </div>\n";

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

	require_once "footer.php";
}
?>
