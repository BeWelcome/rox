<div id="navigation-path" class="floatbox box">
    <h3><strong><a class="" href="country/<?=$member->countryCode()."/".$member->region()."/".$member->city() ?>" ><?=$member->city() ?></a></strong>
            (<a class="" href="country/<?=$member->countryCode()."/".$member->region() ?>" ><?=$member->region() ?></a>)
    <strong><a class="" href="country/<?=$member->countryCode() ?>" ><?=$member->country() ?></a></strong></h3>
<p class="grey"><?=$ww->NbComments($comments_count['all'])." (".$ww->NbTrusts($comments_count['positive']).")" ?>
<br /><?=$agestr ?><?php if($occupation != null) echo ", ".$occupation; ?>
</p>
</div> <!-- navigation-path -->

<div id="profile_summary" class="floatbox box">
    <h3 class="icon info22" ><?=$words->getInLang('ProfileSummary', $profile_language_code);?></h3>
    <p><?=$member->get_trad("ProfileSummary", $profile_language); ?></p>
</div> <!-- profile_summary -->

<div id="profile_languages" class="floatbox box">
    <h3><?=$words->getInLang('Languages', $profile_language_code);?></h3>
    <ul class="icon profile_languages">
        <?php foreach ($member->get_languages_spoken() as $lang)
        { echo '<li>' . $lang->Name . ' <sup>' . $words->getInLang("LanguageLevel_" . $lang->Level, $profile_language_code) . '</sup></li>'; } ?>
    </ul>
</div> <!-- profile_languages -->

<?
if ($member->get_trad("Hobbies", $profile_language) != "" or $member->get_trad("Organizations", $profile_language) != "" or $member->get_trad("Books", $profile_language) != "" or $member->get_trad("Music", $profile_language) != "" or $member->get_trad("Movies", $profile_language) != "") { 
?>
<div id="profile_interests" class="floatbox box">
    <h3 class="icon sun22" ><?=$words->getInLang('ProfileInterests', $profile_language_code);?></h3>
    <dl id="interests" >
    <?php if ($member->get_trad("Hobbies", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileHobbies', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("Hobbies", $profile_language); ?></dd>
        <? } ?>
        
    <?php if ($member->get_trad("Books", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileBooks', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("Books", $profile_language); ?></dd>
        <? } ?>
    
    <?php if ($member->get_trad("Music", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileMusic', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("Music", $profile_language); ?></dd>
        <? } ?>
    
    <?php if ($member->get_trad("Movies", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileMovies', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("Hobbies", $profile_language); ?></dd>
        <? } ?>
        
    <?php if ($member->get_trad("Organizations", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileOrganizations', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("Organizations", $profile_language); ?></dd>
        <? } ?>
    </dl>

</div>
<?
}


if ($member->get_trad("PastTrips", $profile_language) != "" or $member->get_trad("PlannedTrips", $profile_language) != "") { 
?>
<div id="profile_travel" class="floatbox box">
    <h3 class="icon world22" ><?=$words->getInLang('ProfileTravelExperience', $profile_language_code);?></h3>
    <dl id="travelexperience">
        <dt><?=$words->getInLang('ProfilePastTrips', $profile_language_code);?>:</dt>
        <dd><?php echo $member->get_trad("PastTrips", $profile_language); ?></dd>
        <dt><?=$words->getInLang('ProfilePlannedTrips', $profile_language_code);?>:</dt>
        <dd><?php echo $member->get_trad("PlannedTrips", $profile_language); ?></dd>
    </dl>
</div>
<?
}

// display my groups, if there are any
$my_groups = $member->getGroups();
if (!empty($my_groups)) :
?>
<div id="profile_groups" class="floatbox box">
        <?php // display my groups, if there are any
            echo "<h3>{$words->getInLang('ProfileGroups', $profile_language_code)}</h3>";
            for($i = 0; $i < count($my_groups) && $i < 3; $i++) : ?>
                <div class="groupbox floatbox">
                    <a href="groups/<?=$my_groups[$i]->id ?>">
                        <img class="framed float_left"  width="50px" height="50px" alt="Group" src="<?= ((strlen($my_groups[$i]->Picture) > 0) ? "groups/thumbimg/{$my_groups[$i]->getPKValue()}" : 'images/icons/group.png' ) ;?>"/>
                    </a>
                    <div class="groupinfo">
                    <h4><a href="groups/<?= $my_groups[$i]->id ?>"><?=$words->getInLang($my_groups[$i]->Name, $profile_language_code)?></a></h4>
                    <p>
                        <?=$member->get_trad_by_tradid($my_groups[$i]->IdDescription, $profile_language)?>
                    </p>
                    </div>  <!-- groupinfo -->
                </div> <!-- groupbox clearfix -->
            <?php endfor; ?>
            <p><strong><a href="groups/mygroups"><?= $words->get('GroupsAllMyLink'); ?></a></strong></p>
</div> <!-- profile_groups -->
<?php endif ; ?>

<div id="profile_accommodation" class="floatbox box">
    <h3 class="icon accommodation22" ><?=$words->getInLang('ProfileAccommodation', $profile_language_code);?></h3>
    <dl id="accommodation" >
        <?php if ($member->MaxGuest != 0 && $member->MaxGuest != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileNumberOfGuests', $profile_language_code);?>:</dt>
            <dd><?php echo $member->MaxGuest ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("MaxLenghtOfStay", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileMaxLenghtOfStay', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("MaxLenghtOfStay", $profile_language); ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("ILiveWith", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileILiveWith', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("ILiveWith", $profile_language); ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("PleaseBring", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfilePleaseBring', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("PleaseBring", $profile_language); ?></dd>
        <? } ?>
        
        <?php
        if ($member->get_trad("OfferGuests", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileOfferGuests', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("OfferGuests", $profile_language); ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("OfferHosts", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('ProfileOfferHosts', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("OfferHosts", $profile_language); ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("AdditionalAccomodationInfo", $profile_language) != "" or $member->get_trad("InformationToGuest", $profile_language) != "") { ?>
            <dt class="label" ><?=$words->getInLang('OtherInfosForGuest', $profile_language_code);?>:</dt>
            <dd>
                <?php echo $member->get_trad("AdditionalAccomodationInfo", $profile_language); ?>
                <?php echo $member->get_trad("InformationToGuest", $profile_language); ?>
            </dd>
        <? } ?>
        
        <?php
        $TabRestrictions = explode(",", $member->Restrictions);
        $max = count($TabRestrictions);
        if (($max > 0 and $TabRestrictions[0] != "") or ($member->Restrictions != "")) {
        ?>
            <dt class="label" ><?=$words->getInLang('ProfileRestrictionForGuest', $profile_language_code);?>:</dt>
            <?php
                if ($max > 0) {
                  echo "<dd>\n";
                    for ($ii = 0; $ii < $max; $ii++) {
                        echo ($ii > 0) ? ', <br />' : '';
                        echo $words->getInLang("Restriction_" . $TabRestrictions[$ii], $profile_language_code);
                    }
                    echo "</dd>\n";
                }
            ?>
            <dd><?php echo $member->get_trad("Restrictions", $profile_language); ?></dd>
            
            <dt class="label" ><?=$words->getInLang('ProfileOtherRestrictions', $profile_language_code);?>:</dt>
            <dd><?php echo $member->get_trad("OtherRestrictions", $profile_language); ?></dd>
            
        <? } ?>
    </dl>
</div> <!-- profile_accommodation -->

<div class="profile_address box" >
    <h3 class="icon contact22" ><?=$words->getInLang('ContactInfo', $profile_language_code);?></h3>
    <dl id="address">
        <dt><?=$words->getInLang('Name', $profile_language_code);?>:</dt>
        <dd><?php echo $member->name?></dd>

        <dt><?=$words->getInLang('Address', $profile_language_code);?>:</dt>
        <dd><?php echo $member->street?><br />
            <?php echo $member->zip ?>
            <?php echo $member->city ?><br />
            <?php echo $member->country ?>
        </dd>
        
        <?php if ($member->phone) { ?>
            <dt><?=$words->getInLang('ProfilePhone', $profile_language_code);?>:</dt>
            <?php
            foreach ($member->phone as $phone => $value) {
                echo "<dd>", $words->get('Profile'.$phone), ": ", $value, "</dd>\n";
            }
        ?>
        
        <?php
        if (isset($website))
        { ?>
            <dt><?=$words->getInLang('Website', $profile_language_code);?>:</dt>
            <dd><a href="http://<?php echo $member->WebSite ?>" ><?php echo $member->WebSite ?></a></dd>
        <?php } ?>
        <?php } ?>
            <?php
            if(isset($messengers))
            { ?>
            <dt><?=$words->getInLang('Messenger', $profile_language_code);?>:</dt>
            <dd>
              <?php
                foreach($messengers as $m) {
                    if (isset($m["address"]) && $m["address"] != '')
                    echo "<img src='".PVars::getObj('env')->baseuri."bw/images/icons1616/".$m["image"]."' width='16' height='16' title='".$m["network"]."' alt='".$m["network"]."' />"
                        .$m["network"].": ".$m["address"]."<br />";
                }
              ?>
            </dd>
            <?php } ?>
        </dl>   
</div> <!-- profile_address -->