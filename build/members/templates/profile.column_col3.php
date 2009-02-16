<?php 
if (count($languages) > 1) {
?>
<div class="inner_info">
            <?=$words->get('ProfileVersion')?>: 
            <?php if (file_exists('bw/images/flags/'.$profile_language_code.'.png')) { ?>
            <img height="11px"  width="16px"  src="bw/images/flags/<?=$profile_language_code ?>.png" style="<?=$css?>" alt="<?=$profile_language_code ?>.png">
            <?php } ?>
            <strong><?=$profile_language_name ?></strong> 
            	&nbsp;	&nbsp;	&nbsp;	&nbsp; <?=$words->get('ProfileVersionIn')?>:
        <?php 
        foreach($languages as $language) { 
            $css = 'opacity: 0.5';
            if ($language->ShortCode != $profile_language_code) {
        ?>
            <a href="members/<?=$member->Username ?>/<?=$language->ShortCode ?>">
             <img height="11px"  width="16px"  src="bw/images/flags/<?=$language->ShortCode ?>.png" style="<?=$css?>" alt="<?=$language->ShortCode ?>.png">
             <strong><?=$language->Name ?></strong>
            </a> 
        <?php 
            }
        } ?>
</div>
<?php } ?>
<div class="inner_info" >
  <h3 class="icon info22" ><?=$words->getInLang('ProfileSummary', $profile_language_code);?></h3>
  <?=$member->get_trad("ProfileSummary", $profile_language); ?>
  <?php 
  if (count($member->get_languages_spoken()) > 0) {
  ?>
  <h4><?=$words->getInLang('Languages', $profile_language_code);?></h4>

  <ul>
  <?php
  foreach ($member->get_languages_spoken() as $lang) { 
  echo '<li>' . $lang->Name . ' -  ' . $words->getInLang("LanguageLevel_" . $lang->Level, $profile_language_code) . '</li>'; 
  } ?>
  </ul>
  <?php } ?>
</div>
<? 
// if ($sections->ProfileInterests !== 0) { 
?>
<div class="inner_info" >
  <h3 class="icon sun22" ><?=$words->getInLang('ProfileInterests', $profile_language_code);?></h3>
  <div class="subcolumns" >
    <div class="c50l" >
      <div class="subcl" >
      	<?php echo $member->get_trad("Hobbies", $profile_language); ?></div>
    </div>
    <div class="c50r" >
      <div class="subcr" ></div>
    </div>
  </div>
  <h4><?=$words->getInLang('ProfileOrganizations', $profile_language_code);?></h4>
  <p><?php echo $member->get_trad("Organizations", $profile_language); ?></p>
</div>
<? 
// } 
 if (!empty($member->PastTrips) or !empty($member->PlannedTrips)) { 
?>
<div class="inner_info" >
  <h3 class="icon world22" ><?=$words->getInLang('ProfileTravelExperience', $profile_language_code);?></h3>
  <h4><?=$words->getInLang('ProfilePastTrips', $profile_language_code);?></h4>
  <p><?php echo $member->get_trad("PastTrips", $profile_language); ?></p>
  <h4><?=$words->getInLang('ProfilePlannedTrips', $profile_language_code);?></h4>
  <p><?php echo $member->get_trad("PlannedTrips", $profile_language); ?></p>
</div>
<?
 } 
// if ($sections->ProfileGroups) { 
?>
<div class="inner_info" >
  <h3 class="icon groups22" ><?=$words->getInLang('ProfileGroups', $profile_language_code);?></h3>
  <?php
  foreach($groups as $group) {
  	 $group_id = $group->IdGroup;
  	 $group_name_translated = $words->getInLang($group->Name, $profile_language_code);
  	 $group_comment_translated = $member->get_trad_by_tradid($group->Comment, $profile_language);
			?>
	  <h4>
	    <a href="groups/<?=$group_id?>" ><?php echo $group_name_translated," ",$group->Location ;?></A>
	  </h4>
	  <p><?php echo $group_comment_translated ; ?></p>
          <?php
  }
  ?>
</div>
<? 
// } 
?>

<div class="inner_info" >
  <h3 class="icon accommodation22" ><?=$words->getInLang('ProfileAccommodation', $profile_language_code);?></h3>
  <table id="accommodation" >
    <colgroup>
      <COL width="35%" ></COL>
      <COL width="65%" ></COL>
    </colgroup>
    <tbody>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfileNumberOfGuests', $profile_language_code);?>:</td>
        <td><?php echo $member->MaxGuest ?></td>
      </tr>

      <?php if (!empty($member->MaxLenghtOfStay)) { ?>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfileMaxLenghtOfStay', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("MaxLenghtOfStay", $profile_language); ?></td>
      </tr>
      <?php } ?>
      <?php if (!empty($member->ILiveWith)) { ?>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfileILiveWith', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("ILiveWith", $profile_language); ?></td>
      </tr>
      <?php } ?>
      <?php if (!empty($member->PleaseBring)) { ?>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfilePleaseBring', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("PleaseBring", $profile_language); ?></td>
      </tr>
      <?php } ?>
      <?php if (!empty($member->OfferGuests)) { ?>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfileOfferGuests', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("OfferGuests", $profile_language); ?></td>
      </tr>
      <?php } ?>
      <?php if (!empty($member->OfferHosts)) { ?>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfileOfferHosts', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("OfferHosts", $profile_language); ?></td>
      </tr>
      <?php } ?>
      <?php if (!empty($member->PublicTransport)) { ?>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfilePublicTransport', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("PublicTransport", $profile_language); ?></td>
      </tr>
      <?php } ?>
      <?php if ($member->AdditionalAccomodationInfo or $member->InformationToGuest) { ?>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('OtherInfosForGuest', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("AdditionalAccomodationInfo", $profile_language); ?> 
            <?php echo $member->get_trad("inner_informationToGuest", $profile_language); ?>
        </td>
      </tr>
      <?php } ?>
        <?php
        $TabRestrictions = explode(",", $member->Restrictions);
        $max = count($TabRestrictions);
        if (($max > 0 and $TabRestrictions[0] != "") or ($member->Restrictions != "")) {
        ?>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfileRestrictionForGuest', $profile_language_code);?>:</td>
        <?php
            if ($max > 0) {
              echo "              <td>\n";
                for ($ii = 0; $ii < $max; $ii++) {
                    echo "              ", $words->get("Restriction_" . $TabRestrictions[$ii]), ", ","\n";
                }
                echo "              </td>\n";
            }
        ?>
        <td><?php echo $member->get_trad("Restrictions", $profile_language); ?></td>
      </tr>
      <tr align="left" >
        <td class="label" ><?=$words->getInLang('ProfileOtherRestrictions', $profile_language_code);?>:</td>
        <td><?php echo $member->get_trad("OtherRestrictions", $profile_language); ?></td>
      </tr>
      <?php } ?>
    </TBODY>
  </table>
</div>

<div class="inner_info">

  <h3 class="icon contact22" ><?=$words->getInLang('ContactInfo', $profile_language_code);?></h3>
  <div class="subcolumns" >
    <div class="c50l" >
      <div class="subcl" >
        <ul>
<li class="label" ><?=$words->getInLang('Name', $profile_language_code);?></li>
<li><?php echo $member->name?></li>
        </ul>
        <ul>
<li class="label" ><?=$words->getInLang('Address', $profile_language_code);?></li>
<li><?php echo $member->street?></li>
<li><?php echo $member->zip	?> <?php echo $member->city	?></li>
<li><?php echo $member->region ?></li>
<li><?php echo $member->country ?></li>
        </ul>
        <?php if ($member->phone) { ?>
        <ul>
<li class="label" ><?=$words->getInLang('ProfilePhone', $profile_language_code);?></li>
<?php
foreach ($member->phone as $phone => $value) {
    echo "<li>", $words->get('Profile'.$phone), ": ", $value, "</li>\n";
}
?>
        </ul>
        <?php } ?>
      </div>
    </div>
    <div class="c50r" >
      <div class="subcr" >
        <ul>
<LI class="label" ><?=$words->getInLang('Messenger', $profile_language_code);?></li>

        <?php
        if(isset($messengers))
        { ?>
<li>
  <?php
  	foreach($messengers as $m) {
  		echo ($m["image"] == false) ? "" : "<img src='".PVars::getObj('env')->baseuri."bw/images/icons1616/".$m["image"]."' width='16' height='16' title='".$m["network"]."' alt='".$m["network"]."' />";
  		echo $m["network"].": ".$m["address"]."<br />";
  	}
  ?>
</li>
        <?php
        }
        ?></ul><?php
        if (isset($website))
        { ?>
        <ul>
<li class="label"><?=$words->getInLang('Website', $profile_language_code);?></li>
<li>
  <a href="http://<?php echo $member->WebSite ?>" ><?php echo $member->WebSite ?></a>
</li>
        </ul>
        <?php } ?>
      </div>
    </div>
  </div>
</div>