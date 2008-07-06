<div class="info" >
  <h3 class="icon info22" ><?=$words->getInLang('ProfileSummary', $profile_language_code);?></h3>
  	<?=$member->get_trad("ProfileSummary", $profile_language); ?>
  <H4><?=$words->getInLang('Languages', $profile_language_code);?></H4>
  
  <p></p>
</div>
<div class="info highlight" >
  <h3 class="icon sun22" ><?=$words->getInLang('ProfileInterests', $profile_language_code);?></h3>
  <div class="subcolumns" >
    <div class="c50l" >
      <div class="subcl" >
      	<?php echo $member->get_trad("Hobbies", $profile_language); ?></div>
    </div>
    <div class="c503" >
      <div class="subcl" ></div>
    </div>
  </div>
  <H4><?=$words->getInLang('ProfileOrganizations', $profile_language_code);?></H4>
  <p><?php echo $member->get_trad("Organizations", $profile_language); ?></p>
</div>
<div class="info" >
  <h3 class="icon world22" ><?=$words->getInLang('ProfileTravelExperience', $profile_language_code);?></h3>
  <H4><?=$words->getInLang('ProfilePastTrips', $profile_language_code);?></H4>
  <p><?php echo $member->get_trad("PastTrips", $profile_language); ?></p>
  <H4><?=$words->getInLang('ProfilePlannedTrips', $profile_language_code);?></H4>
  <p><?php echo $member->get_trad("PlannedTrips", $profile_language); ?></p>
</div>
<div class="info highlight" >
  <h3 class="icon groups22" ><?=$words->getInLang('ProfileGroups', $profile_language_code);?></h3>
  <?php
  foreach($groups as $group) {
  	 $group_id = $group->IdGroup;
  	 $group_name_translated = $words->getInLang($group->Name, $profile_language_code);
  	 $group_comment_translated = $member->get_trad_by_tradid($group->Comment, $profile_language);
			?>
	  <H4>
	    <A href="groups.php?action=ShowMembers&IdGroup=<?=$group_id?>" ><?=$group_name_translated?></A>
	  </H4>
	  <p><?=$group_comment_translated?></p>			
          <?php  	  
  }
  ?>
</div>
<div class="info" >
  <h3 class="icon accommodation22" ><?=$words->getInLang('ProfileAccommodation', $profile_language_code);?></h3>
  <TABLE id="accommodation" >
    <colgroup>
      <COL width="35%" ></COL>
      <COL width="65%" ></COL>
    </colgroup>
    <tbody>
      <TR align="left" >
        <td class="label" ><?=$words->getInLang('ProfileNumberOfGuests', $profile_language_code);?>:</td>
        <td><?php echo $member->MaxGuest ?></td>
      </TR>
      <TR align="left" >
        <td class="label" ><?=$words->getInLang('ProfileMaxLenghtOfStay', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("MaxLenghtOfStay", $profile_language); ?></td>
      </TR>
      <TR align="left" >
        <td class="label" ><?=$words->getInLang('ProfileILiveWith', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("ILiveWith", $profile_language); ?></td>
      </TR>
      <TR align="left" >
        <td class="label" ><?=$words->getInLang('OtherInfosForGuest', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("InformationToGuest", $profile_language); ?></td>
      </TR>
      <TR align="left" >
        <td class="label" ><?=$words->getInLang('ProfileRestrictionForGuest', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("Restrictions", $profile_language); ?></td>
      </TR>
      <TR align="left" >
        <td class="label" ><?=$words->getInLang('ProfileOtherRestrictions', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("OtherRestrictions", $profile_language); ?></td>
      </TR>
    </TBODY>
  </TABLE>
</div>
<div class="info highlight">
   
  <h3 class="icon contact22" ><?=$words->getInLang('ContactInfo', $profile_language_code);?></h3>
  <div class="subcolumns" >
    <div class="c50l" >
      <div class="subcl" >
        <ul>
<li class="label" ><?=$words->getInLang('Name', $profile_language_code);?></li>
<li><?php echo $member->name?></li>
        </UL>
        <ul>
<li class="label" ><?=$words->getInLang('Address', $profile_language_code);?></li>
<li><?php echo $member->street?></li>
<li><?php echo $member->zip	?></li>
<li><?php echo $member->region ?></li>
<li><?php echo $member->country ?></li>
        </UL>
      </div>
    </div>
    <div class="c50r" >
      <div class="subcr" >
        <UL>
<LI class="label" ><?=$words->getInLang('Messenger', $profile_language_code);?></li>
      
        <?php 
        if(isset($messengers)) 
        { ?>
<li>
  <?php
  	foreach($messengers as $m) {
  		echo "<IMG src='".PVars::getObj('env')->baseuri."bw/images/icons1616/".$m["image"]."' width='16' height='16' title='".$m["network"]."' alt='".$m["network"]."' />"
  			.$m["network"].": ".$m["address"]."<br />";
  	}
  ?>
</li>
        <?php 
        } 
        ?></UL><?php
        if(isset($website)) 
        { ?>
        <UL>
<LI class="label" ><?=$words->getInLang('Website', $profile_language_code);?></li>
<li>
  <A href="http://<?php echo $member->WebSite ?>" ><?php echo $member->WebSite ?></A>
</li>
        </UL>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
