<?php

// Header of profile page
echo "\n<div id=\"maincontent\">";
echo "<div id=\"topcontent\">";
echo "<div id=\"topcontent-profile-photo\">\n";

/* --- main picture --- */
echo "\n<div id=\"pic_main\">";
echo "          <div id=\"img1\"><a href=\"myphotos.php?action=viewphoto&IdPhoto=".$m->IdPhoto."\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">\n<img src=\"" . $m->photo . "\" width=\"86\" /></a></div>\n";
echo "          <div id=\"img2\"><img src=\"images/pic_main_unten.gif\" width=\"114\" height=\"15\" /></div>\n";
echo "		  <div id=\"img3\">\n";
// future flickr/gallery support  - now just the photo switchers
// echo "<a href=\"http://www.flickr.com\"><img src=\"images/flickr.gif\" width=\"114\" height=\"14\" /></a>\n";
// photo switchers
if ($m->photorank > 0) {
	echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=previouspicture&photorank=" . $m->photorank . "&cid=" . $m->id . "\">";
	echo "<img border=0 height=10 src=\"images/moveleft.gif\" alt=\"previous picture \"></a>";
}
echo "&nbsp;&nbsp;&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?action=nextpicture&photorank=" . $m->photorank . "&cid=" . $m->id . "\">";
echo "<img border=0 height=10 src=\"images/moveright.gif\" alt=\"next picture \"></a>";

echo "        </div></div>\n";
// end main picture

// --- small pictures ---
// TO DO: New Programming stuff to locate wether there are more pictures: If so then display 3 of them as small thumbs next to the main picture
echo "		<div id=\"pic_sm1\"><a href=\"#\"><img name=\"pic_sm1\" src=\"images/pic_sm1.gif\" width=\"25\" height=\"25\" border=\"0\" alt=\"\" /></a> \n";
echo "        </div>\n";
echo "        <div id=\"pic_sm2\"> \n";
echo "         <a href=\"#\"><img name=\"pic_sm2\" src=\"images/pic_sm2.jpg\" width=\"25\" height=\"25\" border=\"0\" alt=\"\" /></a>\n";
echo "        </div>\n";
echo "        <div id=\"pic_sm3\"> \n";
echo "          <a href=\"#\"><img name=\"pic_sm3\" src=\"images/pic_sm3.jpg\" width=\"25\" height=\"25\" border=\"0\" alt=\"\" /></a>\n";
echo "        </div>    \n";
// - end of small pictures - 

/* echo "<a href=\"",$m->photo,"\" title=\"",str_replace("\r\n"," ",$m->phototext),"\">\n<img src=\"".$m->photo."\" height=\"100px\" ></a>\n<br>" ;
if ($m->photorank>0) {
  echo "<a href=\"".$_SERVER['PHP_SELF']."?action=previouspicture&photorank=".$m->photorank."&cid=".$m->id."\">" ;
  echo "<img border=0 height=10 src=\"images/moveleft.gif\" alt=\"previous picture \"></a>" ;
}
echo "&nbsp;&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?action=nextpicture&photorank=".$m->photorank."&cid=".$m->id."\">" ;
echo "<img border=0 height=10 src=\"images/moveright.gif\" alt=\"next picture \"></a>" ;
*/

echo "</div>";

echo "<div id=\"topcontent-columns\">";
echo "				<div id=\"navigation-path\"><a href=\"membersbycountries.php\">", ww("country"), "</a> &gt; <a href=\"#\">$m->countryname</a> &gt; <a href=\"#\">$m->regionname</a> &gt; $m->cityname";
echo "		    </div>";
echo "			<div id=\"profile-user-info\">";
echo "				<h1>", $m->Username, "</h1>";
echo "				<p>", $m->age, "";
if ($m->Occupation > 0)
	echo FindTrad($m->Occupation);
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
echo "			</div>";
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
echo "				</ul>";
echo "			</div>";

echo "</div>";
echo "<div id=\"experience\">";
echo "<img src=\"images/line.gif\" alt=\"\" width=\"1\" height=\"111\" hspace=\"15\" align=\"left\" />";
echo "<h2><br />";
echo ww("HospitalityExperience"), "<br />";
echo "</h2>";
echo "<p><img src=\"images/icon_rating.gif\" alt=\"\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /></p>";
echo "<p>(", ww("NbComments", $m->NbComment), ") </p> ";
echo "<p>(", ww("NbTrusts", $m->NbTrust), ") </p>";
echo "</div>";

echo "			<div class=\"clear\" />";
echo "			</div>";
echo "</div>";
// end of Header of the profile page
?>