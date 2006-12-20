<?php
require_once("Menus_micha.php") ;

function DisplayMember($m,$photo="",$phototext="",$photorank=0,$cityname,$regionname,$countryname,$profilewarning="",$TGroups,$LastLogin="never",$NbComment,$NbTrust,$age="") {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header_micha.php" ;
	
	Menu1() ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;
	

echo "\n<div id=\"maincontent\">" ;
echo "<div id=\"topcontent\">" ;
echo "<div id=\"topcontent-profile-photo\">\n" ;
echo "<a href=\"#\" onClick=\"window.alert('$phototext');\">\n<img src=\"".$photo."\" height=\"100px\" ></a>\n<br>" ;
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
								<li class=\"label\">",ww("Address"),"</li>
								<li>",$m->Address,"</li>
								<li>",$m->Zip,"</li>
								<li>",$cityname,"</li>
								<li>",$regionname,"</li>
								<li>",$countryname,"</li>
							</ul>
						</li>
						<li>" ;
if (($m->DisplayHomePhoneNumber!="")or($m->DisplayCellPhoneNumber!="")or($m->DisplayWorkPhoneNumber!="")) {
  echo "        <ul>" ;
  echo "							<li class=\"label\">",ww("ProfilePhone"),"</li>" ;
  if ($m->DisplayHomePhoneNumber!="") echo "							<li>",ww("ProfileHomePhoneNumber"),": ",$m->DisplayHomePhoneNumber,"</li>" ;
  if ($m->DisplayCellPhoneNumber!="") echo "							<li>",ww("ProfileCellPhoneNumber"),": ",$m->DisplayCellPhoneNumber,"</li>" ;
  if ($m->DisplayWorkPhoneNumber!="") echo "							<li>",ww("ProfileWorkPhoneNumber"),": ",$m->DisplayWorkPhoneNumber,"</li>" ;
  echo "							</ul>" ;
}


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

  if ($m->Offer>0) {
echo "					<strong>",strtoupper(ww('ProfileOffer')),"</strong>" ;
	  echo "<p>",FindTrad($m->Offer),"</p>" ;
  }

if ($m->IdGettingThere>0) {						
echo "					<strong>",strtoupper(ww('GettingHere')),"</strong>" ;
	  echo "<p>",FindTrad($m->IdGettingThere),"</p>" ;
}
echo "					</div>
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
echo "						<li class=\"label\">",ww("ProfileOrganizations"),"</li>" ;
echo "						<li>",FindTrad($m->Organizations),"</li>" ;
}
echo "					</ul>" ;
echo "					<div class=\"clear\" ></div>" ;
echo "				</div>" ;
echo "				<div class=\"info highlight\">" ;
echo "					<h3>",ww("ProfileAccomodation"),"</h3>" ;
echo "					<ul class=\"information\">" ;
echo "						<li class=\"label\">",ww("ProfileNumberOfGuests"),"</li>" ;
echo "						<li>",$m->MaxGuest,"</li>" ;
if ($m->MaxLenghtOfStay>0) {
echo "						<li class=\"label\">",ww("ProfileMaxLenghtOfStay"),"</li>" ;
echo "						<li>",FindTrad($m->MaxLenghtOfStay),"</li>" ;
}

// echo "						<li class=\"label\">Length of stay</li>" ;
// echo "						<li>till the end</li>" ;

  if ($m->ILiveWith>0) {
echo "						<li class=\"label\">",ww("ProfileILiveWith"),"</li>" ;
	  echo "<li>",FindTrad($m->ILiveWith),"</li><br>" ;
  }
echo "					</ul>" ;

  if (($m->AdditionalAccomodationInfo>0)or($m->InformationToGuest>0)) {
echo "					<p><strong>",strtoupper(ww('OtherInfosForGuest')),"</strong></p>" ;
echo "						<ul>" ;
	  if ($m->AdditionalAccomodationInfo>0) echo "<li>",FindTrad($m->AdditionalAccomodationInfo),"</li><br>" ;
	  if ($m->InformationToGuest>0) echo "<li>",FindTrad($m->InformationToGuest),"</li><br>" ;
echo "						</ul>" ;
  }

   $max=count($m->TabRestrictions) ;
  if (($max>0)or($m->OtherRestrictions>0)) {
echo "					<p><strong>",strtoupper(ww('ProfileRestrictionForGuest')),"</strong></p>" ;
		if ($max>0) {
echo "						<ul>" ;
		  for ($ii=0;$ii<$max;$ii++) {
	      echo "<li>",ww("Restriction_".$m->TabRestrictions[$ii]),"</li>" ;
			}
echo "						</ul><br>" ;
		}
    
	  if ($m->OtherRestrictions>0) echo "<li>",FindTrad($m->OtherRestrictions),"</li>" ;
  }


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
