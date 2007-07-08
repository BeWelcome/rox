<?php

function DisplayProfilePageHeader( $m )
{


	global $_SYSHCVOL;
	// --- new picture displaying technique ---
	
	/*
	// main picture
	echo "          <div id=\"pic_main\"> \n"; 
	echo "            	<div id=\"pic_frame\" style=\"background: url(" . $m->photo . ") no-repeat top left;\">";
	if (!empty($m->IdPhoto)){
		echo "				<a href=\"myphotos.php?action=viewphoto&amp;IdPhoto=".$m->IdPhoto."\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">";
	}
	echo "				<img src=\"images/picmain_frame.gif\" border=\"0\" alt=\"ProfilePicture\"/>";
	if (!empty($m->$IdPhoto)){
		echo "			</a>";
	}
	echo "			</div>\n";
	echo "          </div>\n"; // end pic_main
	

	*/
	
	// Teaser of profile page
	echo "\n";
	echo "    <div id=\"main\"> \n"; 
	echo "      <div id=\"teaser\" class=\"clearfix\"> \n"; 
  echo "        <div id=\"teaser_l\"> \n"; 
  
	// main picture
	echo "          <div id=\"pic_main\"> \n"; 
	echo "            <div id=\"img1\">";
	if (!empty($m->IdPhoto)){
		echo "<a href=\"myphotos.php?action=viewphoto&amp;IdPhoto=".$m->IdPhoto."\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">";
	}
	if (empty($m->photo)) {
	  
	  echo "<img src=\"" . DummyPict($m->Gender,$m->HideGender) . "\"  alt=\"no ProfilePicture\"/>";
	}
	else {
	  echo "<img src=\"" . $m->photo . "\"  alt=\"ProfilePicture\"/>";
	}
	if (!empty($m->$IdPhoto)){
		echo "</a>";
	}
	echo "</div>\n";
	echo "            <div id=\"img2\"><img src=\"".bwlink("styles/YAML/images/pic_main_unten.gif")."\" width=\"114\" height=\"14\" alt=\"frame\" /></div>\n";
	
	// --- small pictures ---
	echo "		<div id=\"pic_sm1\">\n";
	if (!empty($m->IdPhoto)){
		echo "			<a href=\"member.php?action=previouspicture&photorank=" . $m->photorank . "&cid=" . $m->id . "\">";
	}
	echo "<img name=\"pic_sm1\" src=\"",$m->pic_sm1,"\" width=\"30\" height=\"30\" border=\"0\" alt=\"\" />";
	if (!empty($m->IdPhoto)){
		echo "</a> \n";
	}
	echo "		</div>\n";
	echo "    <div id=\"pic_sm2\"> \n";
	echo "       <img name=\"pic_sm2\" src=\"",$m->pic_sm2,"\" width=\"30\" height=\"30\" border=\"0\" alt=\"\" />\n";
	echo "    </div>\n";
	echo "    <div id=\"pic_sm3\"> \n";
	if (!empty($m->IdPhoto)){
		echo "			<a href=\"member.php?action=nextpicture&photorank=" . $m->photorank . "&cid=" . $m->id . "\">";
	}
	echo "<img name=\"pic_sm3\" src=\"",$m->pic_sm3,"\" width=\"30\" height=\"30\" border=\"0\" alt=\"\" />";
	if (!empty($m->IdPhoto)){
		echo "</a>\n";
	}
	echo "			</div>\n";
	echo "          </div>\n"; // end pic_main
	
	// future flickr/gallery support  
	// echo "<a href=\"http://www.flickr.com\"><img src=\"images/flickr.gif\"  /></a>\n";
	if (HasRight("Accepter")) { // for people with right dsiplay real status of the member
	  if ($m->Status!="Active") {
	  	  echo "<br><table><tr><td bgcolor=yellow><font color=blue><b> ",$m->Status," </b></font></td></table>\n";
	  }
	} // end of for people with right dsiplay real status of the member
	echo "        </div>\n";  // end teaser_l
	
	echo "        <div id=\"teaser_r\"> \n";
	echo "          <div id=\"navigation-path\">\n";
	echo "            <a href=\"membersbycountries.php\">", ww("country"), "</a> &gt; \n";
	echo "            <a href=\"regions.php?IdCountry=",$m->IdCountry,"\">",$m->countryname,"</a> &gt; \n";
	echo "            <a href=\"cities.php?IdRegion=",$m->IdRegion,"\">",$m->regionname,"</a> &gt; \n";
	echo "            <a href=\"membersbycities.php?IdCity=",$m->IdCity,"\">",$m->cityname,"</a>\n";
	echo "          </div>\n"; // end navigation-path
	echo "          <div id=\"profile-info\">\n";
	echo "            <div id=\"username\">\n";
	echo "              <strong>", $m->Username,"</strong>", $m->FullName, "<br />\n";
	echo "            </div>\n"; // end username

	// images for accomodation offers

	if (strstr($m->Accomodation, "anytime"))
		echo "              <img src=\"images/yesicanhost.gif\" class=\"float_left\" title=\"",ww("CanOfferAccomodationAnytime"),"\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />\n";
	if (strstr($m->Accomodation, "yesicanhost"))
		echo "              <img src=\"images/yesicanhost.gif\" class=\"float_left\" title=\"",ww("CanOfferAccomodation"),"\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />\n";
	if (strstr($m->Accomodation, "dependonrequest"))
		echo "              <img src=\"images/dependonrequest.gif\" class=\"float_left\" title=\"",ww("CanOfferdependonrequest"),"\" width=\"30\" height=\"30\" alt=\"dependonrequest\" />\n";
	if (strstr($m->Accomodation, "neverask"))
		echo "              <img src=\"images/neverask.gif\" class=\"float_left\" title=\"",ww("CannotOfferneverask"),"\" width=\"30\" height=\"30\" alt=\"neverask\" />\n";
	if (strstr($m->Accomodation, "cannotfornow"))
		echo "              <img src=\"images/neverask.gif\" class=\"float_left\" title=\"", ww("CannotOfferAccomForNow"),"\" width=\"30\" height=\"30\" alt=\"neverask\" />\n"; 

// specific icon according to membes.TypicOffer
	if (strstr($m->TypicOffer, "guidedtour"))
		echo "              <img src=\"images/icon_castle.gif\" class=\"float_left\" title=\"", ww("TypicOffer_guidedtour"),"\" width=\"30\" height=\"30\" alt=\"icon_castle\" />\n"; 
	if (strstr($m->TypicOffer, "dinner"))
		echo "              <img src=\"images/icon_food.gif\" class=\"float_left\" title=\"", ww("TypicOffer_dinner"),"\" width=\"30\" height=\"30\" alt=\"icon_food\" />\n";
	if (strstr($m->TypicOffer, "CanHostWeelChair"))
		echo "              <img src=\"images/wheelchair.gif\" class=\"float_left\" title=\"", ww("TypicOffer_CanHostWeelChair"),"\" width=\"30\" height=\"30\" alt=\"wheelchair\" />\n";

	echo "<table>";
	echo "<tr>";	
	
	// age, occupation
	echo "<td>";
	echo "              ", ww("NbComments", $m->NbComment), " (", ww("NbTrusts", $m->NbTrust), ")<br />\n";
	if ($m->Occupation > 0)
		echo "            ",$m->age, ", " ,FindTrad($m->Occupation),"\n";
	// echo "                  <p><strong>", ww("Lastlogin"), "</strong>: ", $m->LastLogin, "</p>\n";
	echo "</td>";

	// translation links
    echo "<td>";
		$IdMember=$m->id;
		if ($m->CountTrad>1) { // if member has his profile translated
			echo "              ", ww('ProfileVersionIn'),":\n";
		    for ($ii=0;$ii<$m->CountTrad;$ii++) { // display one tab per available translation
				$Trad=$m->Trad[$ii];
				echo "              <a href=\"".bwlink("member.php?cid=" . $IdMember)."&lang=".$Trad->ShortCode."\">",FlagLanguage($Trad->IdLanguage), "</a>\n";
			}
		}	
    echo "</td>";
	
    echo "</tr>";
    echo "</table>";
	echo "</div>\n"; // profile-info
	
	// old way to display short user info
	/*
	echo " 			  <ul>";
	echo "					  <li>",$m->age,"<br/>";
	if ($m->Occupation>0) echo FindTrad($m->Occupation);
	echo "</li>";
	echo "					  <li>",ww("Lastlogin"),"<br/><strong>",$m->LastLogin,"</strong></li>";
	echo "				</ul>";
	*/
	
	//link to edit the profile
	//if ($_SESSION["IdMember"] == $IdMember) { // if members own profile
	//echo "            <a href=\"".bwlink("editmyprofile.php")."\"><span>", ww('EditMyProfile')," ",FlagLanguage(), "</span></a>\n";
	//}

	echo "        </div>\n";  // end teaser_r
	echo "      </div>\n"; // end teaser
	// end of Header of the profile page
}
?>