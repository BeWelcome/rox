<?php
// Header of profile page
echo "\n<div id=\"maincontent\">" ;
echo "<div id=\"topcontent\">" ;
echo "<div id=\"topcontent-profile-photo\">\n" ;
echo "<a href=\"#\" onmouseover=\"alert('",str_replace("\r\n"," ",$m->phototext),"');\">\n<img src=\"".$m->photo."\" height=\"100px\" ></a>\n<br>" ;
if ($m->photorank>0) {
  echo "<a href=\"".$_SERVER['PHP_SELF']."?action=previouspicture&photorank=".$m->photorank."&cid=".$m->id."\">" ;
  echo "<img border=0 height=10 src=\"images/moveleft.gif\" alt=\"previous picture \"></a>" ;
}
echo "&nbsp;&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?action=nextpicture&photorank=".$m->photorank."&cid=".$m->id."\">" ;
echo "<img border=0 height=10 src=\"images/moveright.gif\" alt=\"next picture \"></a>" ;

echo "</div>" ;
echo "<div id=\"topcontent-columns\">" ;
echo "				<div id=\"navigation-path\"><a href=\"membersbycountries.php\">",ww("country"),"</a> &gt; <a href=\"#\">$m->countryname</a> &gt; <a href=\"#\">$m->regionname</a> &gt; $m->cityname" ;
echo "		    </div>" ;
echo "			<div id=\"profile-user-info\">" ;
echo "				<h1>",$m->Username,"</h1>" ;
echo "				<ul>" ;
echo "					<li>",$m->age,"<br/>" ;
if ($m->Occupation>0) echo FindTrad($m->Occupation);
echo "</li>" ;
echo "					<li>",ww("Lastlogin"),"<br/><strong>",$m->LastLogin,"</strong></li>" ;
echo "				</ul>" ;
echo "			</div>" ;
echo "			<div id=\"profile-user-offer\">\n" ;
echo "				<ul>" ;
if (strstr($m->Accomodation,"anytime")) echo "					<li class=\"accomodation\"><img src=\"images/yesicanhost.gif\" />&nbsp;",ww("CanOfferAccomodationAnytime"),"</li>" ;
if (strstr($m->Accomodation,"yesicanhost")) echo "					<li class=\"accomodation\"><img src=\"images/yesicanhost.gif\" />&nbsp;",ww("CanOfferAccomodation"),"</li>" ;
if (strstr($m->Accomodation,"dependonrequest")) echo "					<li class=\"accomodation\"><img src=\"images/dependonrequest.gif\" />&nbsp;",ww("CanOfferdependonrequest"),"</li>" ;
if (strstr($m->Accomodation,"neverask")) echo "					<li class=\"accomodation\"><img src=\"images/neverask.gif\" />&nbsp;",ww("CannotOfferneverask"),"</li>" ;
if (strstr($m->Accomodation,"cannotfornow")) echo "					<li class=\"accomodation\"><img src=\"images/neverask.gif\" />&nbsp;",ww("CannotOfferAccomForNow"),"</li>" ;
if (strstr($m->TypicOffer,"guidedtour")) echo "					<li class=\"tour\"><img src=\"images/icon_castle.gif\" />&nbsp;",ww("CanOfferCityTour"),"</li>" ;
if (strstr($m->TypicOffer,"dinner")) echo "					<li class=\"dinner\"><img src=\"images/icon_food.gif\" />&nbsp;",ww("CanOfferDinner"),"</li>" ;
echo "				</ul>" ;
echo "			</div>" ;

echo "</div>";
echo "<div id=\"experience\">" ;
echo "<img src=\"images/line.gif\" alt=\"\" width=\"1\" height=\"111\" hspace=\"15\" align=\"left\" />" ;
echo "<h2><br />" ;
echo ww("HospitalityExperience"),"<br />" ;
echo "</h2>" ;
echo "<p><img src=\"images/icon_rating.gif\" alt=\"\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /></p>";
echo "<p>(",ww("NbComments",$m->NbComment),") </p> ";
echo "<p>(",ww("NbTrusts",$m->NbTrust),") </p>" ;
echo "</div>" ;
		
echo "			<div class=\"clear\" />" ;
echo "			</div>" ;
echo "</div>" ;	
// end of Header of the profile page
?>