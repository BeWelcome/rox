<?php
require_once("Menus_micha.php") ;

function DisplayMember($m,$profilewarning="",$TGroups) {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header_micha.php" ;
	
	Menu1() ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;
	
// Header of the profile page
  require_once("profilepage_header.php") ;

echo "	<div id=\"columns\">" ;
menumember("member.php?cid=".$m->id,$m->id,$m->NbComment) ;
echo "		<div id=\"columns-low\">" ;

echo "\n    <!-- leftnav -->"; 
echo "     <div id=\"columns-left\">\n"; 
echo "       <div id=\"content\">"; 
echo "         <div class=\"info\">"; 
echo "           <h3>Actions</h3>"; 

echo "           <ul>"; 
echo "               <li><a href=\"addcomments.php?cid=".$m->id."\">",ww("addcomments"),"</a></li>"; 
  if (HasRight("Logs")) {
                echo "<li><a href=\"adminlogs.php?Username=",$m->id,"\">see logs</a> </li>" ;
  }
  if (HasRight("Admin")) {
                echo "<li><a href=\"editmyprofile.php?cid=",$m->id,"\">Edit this profile</a> </li>" ;
  }
echo "               <li><a href=\"todo.php\">Add to my list</a></li>"; 
echo "               <li><a href=\"todo.php\">View forum posts</a></li>"; 
echo "           </ul>"; 
echo "         </div>"; 
echo "       </div>\n"; 
echo "     </div>\n"; 

echo "\n    <!-- rightnav -->"; 
echo "     <div id=\"columns-right\">\n" ;
echo "       <ul>" ;
echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
echo "         <li></li>" ;
echo "       </ul>\n" ;
echo "     </div>\n" ;

echo "\n    <!-- middlenav -->"; 
echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">" ;
echo "					<h3>Contact info</h3>" ;
echo "					<ul class=\"contact\">
						<li>
							<ul>  
								<li class=\"label\">",ww('Name'),"</li>
								<li>",$m->FullName,"</li>
							</ul>
							<ul>
								<li class=\"label\">",ww("Address"),"</li>
								<li>",$m->Address,"</li>
								<li>",$m->Zip,"</li>
								<li>",$m->cityname,"</li>
								<li>",$m->regionname,"</li>
								<li>",$m->countryname,"</li>
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
if ($m->chat_SKYPE!="") echo "							  <li>SKYPE: ",PublicReadCrypted($m->chat_SKYPE,ww("Hidden")),"</li>" ; 
if ($m->chat_ICQ!="") echo "							  <li>ICQ: ",PublicReadCrypted($m->chat_ICQ,ww("Hidden")),"</li>" ; 
if ($m->chat_AOL!="") echo "							  <li>AOL: ",PublicReadCrypted($m->chat_AOL,ww("Hidden")),"</li>" ; 
if ($m->chat_MSN!="") echo "							  <li>MSN: ",PublicReadCrypted($m->chat_MSN,ww("Hidden")),"</li>" ; 
if ($m->chat_YAHOO!="") echo "							  <li>YAHOO: ",PublicReadCrypted($m->chat_YAHOO,ww("Hidden")),"</li>" ; 
if ($m->chat_Others!="") echo "							  <li>",ww("chat_others"),": ",PublicReadCrypted($m->chat_Others,ww("Hidden")),"</li>" ; 
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
				</div>" ;



echo "				<div class=\"info highlight\">" ;
echo"					<h3>",ww("InterestsAndGroups"),"</h3>
					<ul class=\"information\">" ;
$max=count($m->TLanguages) ;
	if ($max>0) {
echo "						<li class=\"label\">",ww("Languages"),"</li>" ;
echo "            <li>" ; 
	  for ($ii=0;$ii<$max;$ii++) {
		  if ($ii>0) echo "," ;
echo 						$m->TLanguages[$ii]->Name," (",$m->TLanguages[$ii]->Level,")" ;
    }
echo "            </li>" ; 
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
echo "					<div class=\"clear\" ></div>\n" ;
echo "				</div>\n" ;

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
echo "					<h3> ",ww('OtherInfosForGuest'),"</h3>" ;
echo "						<ul>" ;
	  if ($m->AdditionalAccomodationInfo>0) echo "<li>",FindTrad($m->AdditionalAccomodationInfo),"</li><br>" ;
	  if ($m->InformationToGuest>0) echo "<li>",FindTrad($m->InformationToGuest),"</li><br>" ;
echo "						</ul>" ;
  }

   $max=count($m->TabRestrictions) ;
  if (($max>0)or($m->OtherRestrictions>0)) {
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
</div>
" ;
echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ;

}
?>
