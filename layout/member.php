<?php
require_once("Menus_micha.php") ;

function DisplayMember($m,$photo="",$phototext="",$photorank=0,$cityname,$regionname,$countryname,$profilewarning="",$TGroups,$LastLogin="never",$NbComment,$NbTrust,$age="") {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header_micha.php" ;
	
	Menu1() ;

	Menu2($_SERVER["PHP_SELF"]) ;
	

echo "\n<div id=\"maincontent\">" ;
echo "<div id=\"topcontent\">" ;
echo "<div id=\"topcontent-profile-photo\">" ;
echo "<img src=\"".$photo."\" height=\"100px\" alt=\"",$phototext,"\"><br>" ;
echo "</div>" ;
echo "<div id=\"topcontent-columns\">" ;
echo "				<div id=\"navigation-path\"><a href=\"membersbycountries.php\">",ww("country"),"</a> &gt; <a href=\"#\">$countryname</a> &gt; <a href=\"#\">$regionname</a> &gt;" ;
echo "		  $cityname </div>" ;
echo "			<div id=\"profile-user-info\">" ;
echo "				<h1>",$m->Username,"</h1>" ;
echo "				<ul>" ;
echo "					<li>",$age,"<br/>" ;
if ($m->Occupation>0) echo FindTrad($m->Occupation);
echo "</li>" ;
echo "					<li>Last login<br/><strong>",$LastLogin,"</strong></li>" ;
echo "				</ul>" ;
echo "			</div>\"" ;
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

echo "</div>
<div id=\"experience\">
<img src=\"images/line.gif\" alt=\"\" width=\"1\" height=\"111\" hspace=\"15\" align=\"left\" />
<h2><br />
  Hospitality Experience<br />
</h2>
<p><img src=\"images/icon_rating.gif\" alt=\"\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /></p>
<p>(",$NbComment," comments)</p>
<p>(",$NbTrust," trusts)  </p>
</div>
		
			<div class=\"clear\" />
			</div>
</div>

" ;	
echo "
	<div id=\"columns\">
	<div id=\"columns-top\">
				<ul id=\"navigation-content\">
				<li class=\"active\"><a href=\"#\">Profile</a></li>
				<li><a href=\"#\">Send message</a></li>
				<li><a href=\"#\">Comments (54)</a></li>
				<li><a href=\"#\">Blog</a></li>
				<li><a href=\"#\">Map</a></li>
			</ul>
	</div>
		<div id=\"columns-low\">
		<div id=\"columns-left\">
" ;
//  include "footer.php" ;

}
function OldDisplayMember($m,$photo="",$phototext="",$photorank=0,$cityname,$regionname,$countryname,$profilewarning="",$TGroups) {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header.php" ;

  ProfileMenu("member.php",ww('MainPage'),$m->id) ;
	if ($profilewarning!="") {
    echo "<center><H1>",$profilewarning,"</H1></center>\n" ;
	}
	else {
    echo "<center><H1>",$m->Username,"</H1></center>\n" ;
	}
  echo "\n<center>\n" ;
  echo "<table width=50%>\n" ;

  echo "<tr><td>" ;
  echo ww('Name') ;
  echo "</td>" ;
  echo "<td>" ;
	echo PublicReadCrypted($m->FirstName)," " ;
	echo PublicReadCrypted($m->SecondName)," " ;
	echo PublicReadCrypted($m->LastName) ;
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('Location') ;
  echo "</td>" ;
  echo "<td>" ;
	echo $cityname,"<br>" ;
	echo $regionname,"<br>" ;
	echo $countryname,"<br>" ;
  echo "</td>" ;
  echo "<td align=center  bgcolor=#ffffcc >" ;
	if ($photo!="") {
	  echo "photo<br>" ;
	  echo "<img src=\"".$photo."\" height=200 alt=\"$phototext\"><br>" ;
		echo "<table bgcolor=#ffffcc width=60%>" ;
		echo "<tr>" ;
		echo "<td align=left>" ;
		if ($photorank>0) {
		  echo "<a href=\"".$_SERVER['PHP_SELF']."?action=previouspicture&photorank=".$photorank."&cid=".$m->id."\">" ;
		  echo "<img border=0 height=10 src=\"images/moveleft.gif\" alt=\"previous picture \"></a>" ;
		}
		echo "</td>" ;
		echo "<td align=right>" ;
		echo "<a href=\"".$_SERVER['PHP_SELF']."?action=nextpicture&photorank=".$photorank."&cid=".$m->id."\">" ;
		echo "<img border=0 height=10 src=\"images/moveright.gif\" alt=\"next picture \"></a>" ;
		
		echo "</td>" ;
		echo "<tr><td cosplan=2 align=center><font size=1>",$phototext,"</font></td>" ;
		echo "</table><br>" ;
	}
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileSummary') ;
  echo ":</td>" ;
  echo "<td colspan=2>" ;
  if ($m->ProfileSummary>0) echo FindTrad($m->ProfileSummary) ;
  echo "</td>" ;

	if ($m->Organizations>0) {
    echo "<tr><td>" ;
    echo ww('ProfileOrganizations') ;
    echo ":</td>" ;
    echo "<td colspan=2>" ;
    echo FindTrad($m->Organizations) ;
    echo "</td>" ;
	}

	if ($m->Accomodation!="") {
    echo "<tr><td>" ;
    echo ww('ProfileAccomodation') ;
    echo ":</td>" ;
    echo "<td colspan=2>" ;
	  $tt=explode(",",$m->Accomodation) ;
	  $max=count($tt) ;
    echo "<table valign=center style=\"font-size:12;\">" ;
	  for ($ii=0;$ii<$max;$ii++) {
	    echo "<tr><td>",ww("Accomodation_".$tt[$ii]),"</td>" ;
	  }
	  echo "</table></td>" ;
    echo "</td>" ;
	}

	if ($m->AdditionalAccomodationInfo>0) {
    echo "<tr><td>" ;
    echo ww('ProfileAdditionalAccomodationInfo') ;
    echo ":</td>" ;
    echo "<td colspan=2>" ;
    echo FindTrad($m->AdditionalAccomodationInfo) ;
    echo "</td>" ;
	}

	$max=count($TGroups) ;
	if ($max>0) {
    echo "<tr><td colspan=3></td>" ;
    echo "<tr><th colspan=3>",ww("xxBelongsToTheGroups",$m->Username),"</th>" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  echo "<tr><td>",ww("Group_".$TGroups[$ii]->Name),"</td>";
			echo"<td  colpsan=2>" ;
      if ($TGroups[$ii]->Comment>0) echo FindTrad($TGroups[$ii]->Comment) ;
		  echo "</td>" ;
		}
	}
	
  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
