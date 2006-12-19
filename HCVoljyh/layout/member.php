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
if ($photorank>0) {
  echo "<a href=\"".$_SERVER['PHP_SELF']."?action=previouspicture&photorank=".$photorank."&cid=".$m->id."\">" ;
  echo "<img border=0 height=10 src=\"images/moveleft.gif\" alt=\"previous picture \"></a>" ;
}
echo "&nbsp;&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?action=nextpicture&photorank=".$photorank."&cid=".$m->id."\">" ;
echo "<img border=0 height=10 src=\"images/moveright.gif\" alt=\"next picture \"></a>" ;

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

echo "</div>";
echo "<div id=\"experience\">" ;
echo "<img src=\"images/line.gif\" alt=\"\" width=\"1\" height=\"111\" hspace=\"15\" align=\"left\" />" ;
echo "<h2><br />" ;
echo "  Hospitality Experience<br />" ;
echo "</h2>" ;
echo "<p><img src=\"images/icon_rating.gif\" alt=\"\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /><img src=\"images/icon_rating.gif\" alt=\"dd\" width=\"16\" height=\"15\" /></p>";
echo "<p>(",$NbComment," comments)</p>";
echo "<p>(",$NbTrust," trusts)  </p>" ;
echo "</div>" ;
		
echo "			<div class=\"clear\" />" ;
echo "			</div>" ;
echo "</div>" ;	

echo "	<div id=\"columns\">" ;
menumember("member.php?cid=".$m->id,$m->id,$NbComment) ;
echo "		<div id=\"columns-low\">" ;

echo "    <!-- leftnav -->"; 
echo "     <div id=\"columns-left\">"; 
echo "       <div id=\"content\">"; 
echo "         <div class=\"info\">"; 
echo "           <h3>Actions</h3>"; 

echo "           <ul>"; 
echo "               <li><a href=\"todo.php\">Add to my list</a></li>"; 
echo "               <li><a href=\"todo.php\">View forum posts</a></li>"; 
echo "           </ul>"; 
echo "         </div>"; 
echo "       </div>"; 
echo "     </div>"; 

echo "     <div id=\"columns-right\">" ;
echo "       <ul>" ;
echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
echo "         <li></li>" ;
echo "       </ul>" ;
echo "     </div>" ;

echo "
		<div id=\"columns-middle\">
			<div id=\"content\">
				<div class=\"info\">
					<h3>Contact info</h3>
					<ul class=\"contact\">
						<li>
							<ul>  
								<li class=\"label\">",ww('Name'),"</li>
								<li>",$m->FullName,"</li>
							</ul>
							<ul>
								<li class=\"label\">Address</li>
								<li>Noordwal 513</li>
								<li>2513 DX</li>
								<li>",$cityname,"</li>
								<li>",$regionname,"</li>
								<li>",$countryname,"</li>
							</ul>
						</li>
						<li>
							<ul>
								<li class=\"label\">Phone</li>
								<li>Mobile: +31 (0)6 25 18 39 18</li>
								<li>Work: +31 (0)70 36 00 316</li>
								<li>Mother: +31 (0)118 123 123</li>
							</ul>" ;


echo "							<ul>" ;
echo "							  <li class=\"label\">Messenger</li>" ;
if ($m->chat_SKYPE!="") echo "							  <li>SKYPE: ",$m->chat_SKYPE,"</li>" ; 
if ($m->chat_ICQ!="") echo "							  <li>ICQ: ",$m->chat_ICQ,"</li>" ; 
if ($m->chat_AOL!="") echo "							  <li>AOL: ",$m->chat_AOL,"</li>" ; 
if ($m->chat_MSN!="") echo "							  <li>MSN: ",$m->chat_MSN,"</li>" ; 
if ($m->chat_YAHOO!="") echo "							  <li>YAHOO: ",$m->chat_YAHOO,"</li>" ; 
if ($m->chat_Others!="") echo "							  <li>",ww("chat_others"),": ",$m->chat_Others,"</li>" ; 
echo "							</ul>" ;
if ($m->WebSite!="") {
  echo "							<ul>" ;
  echo "								<li class=\"label\">",ww("Website"),"</li>" ;
  echo "								<li><a href=\"",$m->WebSite,"\">",$m->WebSite,"</a></li>" ;
  echo "							</ul>" ;
} // end if there is WebSite
echo "
						</li>
					</ul>
					<div class=\"clear\" />
				</div>
				<div class=\"info highlight\">" ;

echo "					<div class=\"user-content\">" ;
  if ($m->ProfileSummary>0) {
echo "					<strong>",strtoupper(ww('ProfileSummary')),"</strong>" ;
	  echo "<p>",FindTrad($m->ProfileSummary),"</p>" ;
  }
	
  if ($m->MotivationForHospitality>0) {
echo "					<strong>",strtoupper(ww('MotivationForHospitality')),"</strong>" ;
	  echo "<p>",FindTrad($m->MotivationForHospitality),"</p>" ;
  }
	echo "
						<p><strong>Conditions:</strong></p>
						<ul>
							<li>only members whose profiles display them taking the service seriously instead of merely taking use of people's generosity;</li>
							<li>a minimum stay of 2 nights, since I am a host, not a hostel: I'd like to have an opportunity to actually get acquainted;</li>
							<li>If more then 4 people please bring a tent;</li>
							<li>your fellow traveler(s) to also have a valid profile.</li>
						</ul>
						<p><strong>Housing</strong><br/>
						I live in a small apartment near the centre of The Hague. I've a little garden with space for tents. I made a map how to find my place.</p>
						<p><strong>FACILITIES:</strong></p>
						<ul>
							<li>twopersons airbed</li>
							<li>3 couches (1 in the garden)</li>
							<li>ree 24hour internetusage, also open wireless internet connection so you can use your laptop.</li>
							<li>kitchenfacilities, provided left proper</li>
							<li>Amsterdam guidebook - <a href=\"#\">Get Lost</a></li>
							<li>washingmachine available</li>
							<li>Bike</li>
							<li>Museum Jaarkaart - Free entrance in most museums</li>
						</ul>
						<p><strong>NOTES ON THE SIDE:</strong></p>
						<ul>
							<li>I advise you taking your sleepingbag.</li>
							<li>Take a camping mattress.</li>
							<li>I don't mind what time you return to crash here at night, as long as you take all my neighbours into account while doing so.</li>
							<li>I had a couple of NO-SHOWS without a decent notice. Pulling that off with me WILL result in adding you a flame Reference.</li>
						</ul>
						<p><strong>GETTING HERE:</strong><br/>
							 You can take tram 17 to my place from The Hague Central Station (CS) or The Hague Holland Spoor (HS). You can also walk it. A map to get here is located <a href=\"../../../www.pietertje.nl/hc/map.jpg\">here</a>. Parking your car in front of my door is only free on sundays. Ask me about free parkingspaces.</p>
					</div>
				</div>
				<div class=\"info\">
					<h3>",ww("InterestsAndGroups"),"</h3>
					<ul class=\"information\">" ;
$max=count($m->TLanguages) ;
	if ($max>0) {
echo "						<li class=\"label\">",ww("Languages"),"</li>" ;
    echo "<li>" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  if ($ii>0) echo "," ;
echo 						$m->TLanguages[$ii]->Name," (",$m->TLanguages[$ii]->Level,")" ;
    }
    echo "</li>" ;
	}

	$max=count($TGroups) ;
	if ($max>0) {
//    echo "<h3>",ww("xxBelongsToTheGroups",$m->Username),"</h3>" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  echo "<li class=\"label\">",ww("Group_".$TGroups[$ii]->Name),"</li>";
      if ($TGroups[$ii]->Comment>0) echo "<li>",FindTrad($TGroups[$ii]->Comment),"</li>" ;
		}
	}
  if ($m->Organizations>0) {
echo "						<li class=\"label\">",ww("OrganizationsIammemberof"),"</li>" ;
echo "						<li>",FindTrad($m->Organizations),"</li>" ;
}
echo "						<li class=\"label\">Travel experience</li>" ;
echo "						<li>Trip to Eastern Europe in 2004, Swede, Ireland, Spain, France, Italy, Turkey</li>" ;
echo "					</ul>" ;
echo "					<div class=\"clear\" ></div>" ;
echo "				</div>" ;
echo "				<div class=\"info highlight\">" ;
echo "					<h3>",ww("ProfileAccomodation"),"</h3>" ;
echo "					<ul class=\"information\">" ;
echo "						<li class=\"label\">Number of guests</li>" ;
echo "						<li>",$m->MaxGuest,"</li>" ;
// echo "						<li class=\"label\">Length of stay</li>" ;
// echo "						<li>till the end</li>" ;

  if ($m->ILiveWith>0) {
echo "						<li class=\"label\">",ww("ProfileILiveWith"),"</li>" ;
	  echo "<li>",FindTrad($m->ILiveWith),"</li>" ;
  }
echo "					</ul>" ;
echo "					<div class=\"clear\" ></div>" ;
echo "				</div>" ;
				
echo "			</div>
			<div class=\"clear\" />
		</div>
		<div class=\"clear\" />
	</div>
	</div>
</div>
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
