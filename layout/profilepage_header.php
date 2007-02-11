<?php

// --- small pictures ---
// TO DO: New Programming stuff to locate wether there are more pictures: If so then display 3 of them as small thumbs next to the main picture
echo "\n		<div id=\"pic_sm1\">\n		  <a href=\"" ."member.php". "?action=previouspicture&photorank=" . $m->photorank . "&cid=" . $m->id . "\"><img name=\"pic_sm1\" src=\"",$m->pic_sm1,"\" width=\"25\" height=\"25\" border=\"0\" alt=\"\" /></a> \n";
echo "    </div>\n";
echo "    <div id=\"pic_sm2\"> \n";
echo "       <a href=\"#\"><img name=\"pic_sm2\" src=\"",$m->pic_sm2,"\" width=\"25\" height=\"25\" border=\"0\" alt=\"\" /></a>\n";
echo "    </div>\n";
echo "    <div id=\"pic_sm3\"> \n";
echo "       <a href=\"" ."member.php". "?action=nextpicture&photorank=" . $m->photorank . "&cid=" . $m->id . "\"><img name=\"pic_sm3\" src=\"",$m->pic_sm3,"\" width=\"25\" height=\"25\" border=\"0\" alt=\"\" /></a>\n";
echo "    </div>\n";

// Header of profile page
echo "<div id=\"maincontent\"> \n"; 
echo "  <div id=\"topcontent\"> \n"; 
/* --- BLUE Beginning of 3-columns-part --- */
echo "    <div id=\"main\"> \n";
/* --- BLUE left column --- */ 
echo "      <div id=\"col1\" class=\"pic\"> \n"; 
echo "        <div id=\"col1_content\" class=\"clearfix\"> \n"; 

// main picture
echo "          <div id=\"pic_main\"> \n"; 
echo "            <div id=\"img1\"><a href=\"myphotos.php?action=viewphoto&IdPhoto=".$m->IdPhoto."\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">\n<img src=\"" . $m->photo . "\" width=\"86\" /></a></div>\n"; 
echo "            <div id=\"img2\"><img src=\"images/spacer.gif\" height=\"10px\"></div>\n"; 
// future flickr/gallery support  
// echo "<a href=\"http://www.flickr.com\"><img src=\"images/flickr.gif\"  /></a>\n";
echo "          </div>\n";  // close main picture

echo "        </div>\n";  // close col1 content
echo "      </div>\n";  // close col1

/* --- BLUE right column --- */ 
// echo "      <div id=\"col2\"> \n"; 
// echo "        <div id=\"col2_content\" class=\"clearfix\"></div>\n"; 
// echo "      </div>\n";

echo "      <div id=\"col3\"> \n"; 
echo "        <div id=\"col3_content\" class=\"clearfix\"> \n";
echo "				<div id=\"navigation-path\"><a href=\"membersbycountries.php\">", ww("country"), "</a> &gt; <a href=\"#\">",$m->countryname,"</a> &gt; <a href=\"#\">",$m->regionname,"</a> &gt; ",$m->cityname,"\n";
echo "		    	</div>\n";
echo "<div id=\"topcontent-columns\">\n";
echo "			<div id=\"profile-user-info\">\n";
echo "				<h1>", $m->Username, "</h1>";
echo "				<p>", $m->age, "";
if ($m->Occupation > 0)
	echo "<br>",FindTrad($m->Occupation);
echo " </p>";
echo "				<p><strong>", ww("Lastlogin"), "</strong><br>", $m->LastLogin, "</p>";

// old way to display short user info
/*
echo " 			<ul>" ;
echo "					<li>",$m->age,"<br/>" ;
if ($m->Occupation>0) echo FindTrad($m->Occupation);
echo "</li>" ;
echo "					<li>",ww("Lastlogin"),"<br/><strong>",$m->LastLogin,"</strong></li>" ;
echo "				</ul>" ;
*/
echo "\n			</div>\n"; // profile-user-info
echo "			<div id=\"profile-user-offer\">\n";
echo "				<ul>";
if (strstr($m->Accomodation, "anytime"))
	echo "					<li class=\"accomodation\"><img src=\"images/yesicanhost.gif\" />&nbsp;", ww("CanOfferAccomodationAnytime"), "</li>";
if (strstr($m->Accomodation, "yesicanhost"))
	echo "					<li class=\"accomodation\"><img src=\"images/yesicanhost.gif\" />&nbsp;", ww("CanOfferAccomodation"), "</li>";
if (strstr($m->Accomodation, "dependonrequest"))
	echo "					<li class=\"accomodation\"><img src=\"images/dependonrequest.gif\" />&nbsp;", ww("CanOfferdependonrequest"), "</li>";
if (strstr($m->Accomodation, "neverask"))
	echo "					<li class=\"accomodation\"><img src=\"images/neverask.gif\" />&nbsp;", ww("CannotOfferneverask"), "</li>";
if (strstr($m->Accomodation, "cannotfornow"))
	echo "					<li class=\"accomodation\"><img src=\"images/neverask.gif\" />&nbsp;", ww("CannotOfferAccomForNow"), "</li>";
if (strstr($m->TypicOffer, "guidedtour"))
	echo "					<li class=\"tour\"><img src=\"images/icon_castle.gif\" />&nbsp;", ww("CanOfferCityTour"), "</li>";
if (strstr($m->TypicOffer, "dinner"))
	echo "					<li class=\"dinner\"><img src=\"images/icon_food.gif\" />&nbsp;", ww("CanOfferDinner"), "</li>";
echo "				</ul>\n";
echo "			</div>\n"; // profile-user-offer
echo "</div>\n";

echo "<div id=\"experience\">\n";
echo "<img src=\"images/line.gif\" alt=\"\" width=\"1\" height=\"98%\" hspace=\"15\" align=\"left\" />";
echo "<h2>", ww("HospitalityExperience"), "<br /></h2>";
echo "<p><img src=\"images/icon_rating.gif\" alt=\"\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /></p>";
echo "<p>(", ww("NbComments", $m->NbComment), ") <br />";
echo "(", ww("NbTrusts", $m->NbTrust), ") </p>\n";
echo "		</div>\n"; // experience
echo "	</div>\n";
echo "</div>\n";
// BLUE IE Column Clearing 
echo "<div id=\"ie_clearing\">&nbsp;</div>\n"; 

echo "    </div>\n"; 
// End: BLUE 3-column-part

echo "	</div>\n";
echo "</div>\n";
// end of Header of the profile page
?>