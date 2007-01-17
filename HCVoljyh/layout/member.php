<?php
require_once("Menus.php") ;

function DisplayMember($m,$profilewarning="",$TGroups) {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header.php" ;
	
	Menu1() ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;
	
// Header of the profile page
  require_once("profilepage_header.php") ;

echo "	<div id=\"columns\">" ;
menumember("member.php?cid=".$m->id,$m->id,$m->NbComment) ;
echo "		<div id=\"columns-low\">" ;


// Prepare the $MenuAction for ShowAction()  
$MenuAction="" ;
$MenuAction.="               <li><a href=\"contactmember.php?cid=".$m->id."\">".ww("ContactMember")."</a></li>\n"; 
$MenuAction.="               <li><a href=\"addcomments.php?cid=".$m->id."\">".ww("addcomments")."</a></li>\n"; 
$MenuAction.="               <li><a href=\"todo.php\">View forum posts</a></li>\n"; 
if ($m->id==$_SESSION['IdMember']) {
  $MenuAction.="               <li><a href=\"updatemandatory.php\">".ww("UpdateMandatory")."</a></li>\n" ;
}
else {
  $MenuAction.="               <li><a href=\"todo.php\">Add to my list</a></li>\n"; 
}

$MenuAction.="               <li><a href=\"todo.php\">View forum posts</a></li>\n"; 
  if (HasRight("Logs")) {
                $MenuAction.="<li><a href=\"adminlogs.php?Username=".$m->Username."\">see logs</a> </li>\n" ;
  }
  if (HasRight("Admin")) {
                $MenuAction.="<li><a href=\"editmyprofile.php?cid=".$m->id."\">Edit this profile</a> </li>\n" ;
                $MenuAction.="<li><a href=\"updatemandatory.php?cid=".$m->id."\">update mandatory</a> </li>\n" ;
                $MenuAction.="<li><a href=\"myvisitors.php?cid=".$m->id."\">view visits</a> </li>\n" ;
  }
ShowActions($MenuAction) ; // Show the Actions
ShowAds() ; // Show the Ads


echo "\n    <!-- middlenav -->"; 
echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">" ;
echo "					<h3>Contact info</h3>" ;
echo "					<ul class=\"contact\">
						<li>
							<ul>\n  
								<li class=\"label\">",ww('Name'),"</li>
								<li>",$m->FullName,"</li>
							</ul>\n
							<ul>\n
								<li class=\"label\">",ww("Address"),"</li>
								<li>",$m->Address,"</li>
								<li>",$m->Zip,"</li>
								<li>",$m->cityname,"</li>
								<li>",$m->regionname,"</li>
								<li>",$m->countryname,"</li>
							</ul>\n
						</li>
						<li>" ;
if (($m->DisplayHomePhoneNumber!="")or($m->DisplayCellPhoneNumber!="")or($m->DisplayWorkPhoneNumber!="")) {
  echo "        <ul>" ;
  echo "							<li class=\"label\">",ww("ProfilePhone"),"</li>" ;
  if ($m->DisplayHomePhoneNumber!="") echo "							<li>",ww("ProfileHomePhoneNumber"),": ",$m->DisplayHomePhoneNumber,"</li>" ;
  if ($m->DisplayCellPhoneNumber!="") echo "							<li>",ww("ProfileCellPhoneNumber"),": ",$m->DisplayCellPhoneNumber,"</li>" ;
  if ($m->DisplayWorkPhoneNumber!="") echo "							<li>",ww("ProfileWorkPhoneNumber"),": ",$m->DisplayWorkPhoneNumber,"</li>" ;
  echo "				</ul>\n" ;
}


echo "							<ul>" ;
echo "							  <li class=\"label\">Messenger</li>" ;
if ($m->chat_SKYPE!=0) echo "							  <li>SKYPE: ",PublicReadCrypted($m->chat_SKYPE,ww("Hidden")),"</li>" ; 
if ($m->chat_ICQ!=0) echo "							  <li>ICQ: ",PublicReadCrypted($m->chat_ICQ,ww("Hidden")),"</li>" ; 
if ($m->chat_AOL!=0) echo "							  <li>AOL: ",PublicReadCrypted($m->chat_AOL,ww("Hidden")),"</li>" ; 
if ($m->chat_MSN!=0) echo "							  <li>MSN: ",PublicReadCrypted($m->chat_MSN,ww("Hidden")),"</li>" ; 
if ($m->chat_YAHOO!=0) echo "							  <li>YAHOO: ",PublicReadCrypted($m->chat_YAHOO,ww("Hidden")),"</li>" ; 
if ($m->chat_Others!=0) echo "							  <li>",ww("chat_others"),": ",PublicReadCrypted($m->chat_Others,ww("Hidden")),"</li>" ; 
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
	
  if ($m->MotivationForHospitality!="") {
echo "					<strong>",strtoupper(ww('MotivationForHospitality')),"</strong>" ;
	  echo "<p>",$m->MotivationForHospitality,"</p>" ;
  }

  if ($m->Offer!="") {
echo "					<strong>",strtoupper(ww('ProfileOffer')),"</strong>" ;
	  echo "<p>",$m->Offer,"</p>" ;
  }

if ($m->IdGettingThere!="") {						
echo "					<strong>",strtoupper(ww('GettingHere')),"</strong>" ;
	  echo "<p>",$m->GettingThere,"</p>" ;
}
echo "					</div>" ;
echo"				</div>" ;



echo "				<div class=\"info highlight\">\n" ;
echo"					<h3>",ww("InterestsAndGroups"),"</h3>\n" ;
echo"					<ul class=\"information\">\n" ;
$max=count($m->TLanguages) ;
	if ($max>0) {
echo "						<li class=\"label\">",ww("Languages"),"</li>" ;
echo "            <li>" ; 
	  for ($ii=0;$ii<$max;$ii++) {
		  if ($ii>0) echo "," ;
echo 						$m->TLanguages[$ii]->Name," (",$m->TLanguages[$ii]->Level,")" ;
    }
echo "            </li>\n" ; 
	}

	$max=count($TGroups) ;
	if ($max>0) {
//    echo "<h3>",ww("xxBelongsToTheGroups",$m->Username),"</h3>" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  echo "<li class=\"label\">",ww("Group_".$TGroups[$ii]->Name),"</li>";
      if ($TGroups[$ii]->Comment>0) echo "<li>",FindTrad($TGroups[$ii]->Comment),"</li>\n" ;
		}
	}
  if ($m->Organizations!="") {
echo "						<li class=\"label\">",ww("ProfileOrganizations"),"</li>" ;
echo "						<li>",$m->Organizations,"</li>\n" ;
}
echo "					</ul>" ;
echo "					<div class=\"clear\" ></div>\n" ;
echo "				</div>\n" ;

echo "				<div class=\"info highlight\">\n" ;
echo "					<h3>",ww("ProfileAccomodation"),"</h3>\n" ;

echo "					<ul class=\"information\">\n" ;
echo "						<li class=\"label\">",ww("ProfileNumberOfGuests"),"</li>" ;
echo "						<li>",$m->MaxGuest,"</li>\n" ;

if ($m->MaxLenghtOfStay!="") {
echo "						<li class=\"label\">",ww("ProfileMaxLenghtOfStay"),"</li>" ;
echo "						<li>",$m->MaxLenghtOfStay,"</li>\n" ;
}

// echo "						<li class=\"label\">Length of stay</li>" ;
// echo "						<li>till the end</li>" ;

  if ($m->ILiveWith!="") {
echo "						<li class=\"label\">",ww("ProfileILiveWith"),"</li>\n" ;
	  echo "<li>",$m->ILiveWith,"</li>\n" ;
  }
echo "					</ul>" ;

echo "					<div class=\"clear\" ></div>\n" ;
echo "				</div>\n" ;

echo "				<div class=\"info highlight\">\n" ;
  if (($m->AdditionalAccomodationInfo!="")or($m->InformationToGuest!="")) {
echo "					<h3> ",ww('OtherInfosForGuest'),"</h3>\n" ;
echo "						<ul>" ;
	  if ($m->AdditionalAccomodationInfo!="") echo "<li>",$m->AdditionalAccomodationInfo,"</li><br>" ;
	  if ($m->InformationToGuest!="") echo "<li>",$m->InformationToGuest,"</li><br>" ;
echo "						</ul>" ;
  }

   $max=count($m->TabRestrictions) ;
  if (($max>0)or($m->OtherRestrictions!="")) {
echo "					<p><strong>",strtoupper(ww('ProfileRestrictionForGuest')),"</strong></p>" ;
echo "					<ul>" ;
		if ($max>0) {
		  for ($ii=0;$ii<$max;$ii++) {
	      echo "<li>",ww("Restriction_".$m->TabRestrictions[$ii]),"</li>" ;
			}
		}
    
	  if ($m->OtherRestrictions!="") echo "<li>",$m->OtherRestrictions,"</li>" ;
echo "					</ul>" ;
  }


echo "					<div class=\"clear\" ></div>\n" ;
echo "				</div>" ;
				
echo "			</div>
			<div class=\"clear\" />
		</div>
		<div class=\"clear\" />
	</div>
	</div>
</div>" ;
  include "footer.php" ;

}
?>
