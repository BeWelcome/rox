<div id="profile">
    <div class="profile_translations">
        <?php 
        $urlstring = 'members/'.$member->Username;
        require 'profileversion.php'; 
        ?>
    </div> <!-- profile_translations -->
    <div id="profile_summary" class="box">
        <h3 class="icon info22" ><?=$words->getInLang('ProfileSummary', $profile_language_code);?></h3>
        <p><?=$member->get_trad("ProfileSummary", $profile_language); ?></p>
    </div> <!-- profile_summary -->
    <div id="profile_languages" class="box">
        <h3><?=$words->getInLang('Languages', $profile_language_code);?></h3>
        <ul class="icon profile_languages">
            <?php foreach ($member->get_languages_spoken() as $lang)
            { echo '<li>' . $lang->Name . ' <sup>' . $words->getInLang("LanguageLevel_" . $lang->Level, $profile_language_code) . '</sup></li>'; } ?>
        </ul>
    </div>

    <?
    // if ($sections->ProfileInterests !== 0) {
    ?>
    <div id="profile_interests" class="box">
        <h3 class="icon sun22" ><?=$words->getInLang('ProfileInterests', $profile_language_code);?></h3>
        <p><?php echo $member->get_trad("Hobbies", $profile_language); ?></p>
        <h4><?=$words->getInLang('ProfileOrganizations', $profile_language_code);?></h4>
        <p><?php echo $member->get_trad("Organizations", $profile_language); ?></p>
    </div>
    <?
    //}
    

    //if ($sections->ProfileTravelExperience != 0) {
    ?>
    <div id="profile_travel" class="box">
        <h3 class="icon world22" ><?=$words->getInLang('ProfileTravelExperience', $profile_language_code);?></h3>
        <h4><?=$words->getInLang('ProfilePastTrips', $profile_language_code);?></h4>
        <p><?php echo $member->get_trad("PastTrips", $profile_language); ?></p>
        <h4><?=$words->getInLang('ProfilePlannedTrips', $profile_language_code);?></h4>
        <p><?php echo $member->get_trad("PlannedTrips", $profile_language); ?></p>
    </div>
    <?
    //}
      
      
    if ($sections->ProfileGroups != 0) {
    ?>
    <div id="profile_groups" class="box">
        <h3 class="icon groups22" ><?=$words->getInLang('ProfileGroups', $profile_language_code);?></h3>
        <?php
        foreach($groups as $group) {
            $group_id = $group->IdGroup;
            $group_name_translated = $words->getInLang($group->Name, $profile_language_code);
            $group_comment_translated = $member->get_trad_by_tradid($group->Comment, $profile_language);
            ?>
            <h4>
                <a href="groups/<?=$group_id?>" ><?php echo $group_name_translated," ",$group->Location ;?></a>
            </h4>
            <p><?php echo $group_comment_translated ; ?></p>
            <?php
        } ?>
    </div> <!-- profile_groups -->
    <?
    }
    ?>
    
    <div id="profile_accommodation" class="clearfix box">
        <h3 class="icon accommodation22" ><?=$words->getInLang('ProfileAccommodation', $profile_language_code);?></h3>
        <dl id="accommodation" >
            <?php if ($member->MaxGuest != 0 && $member->MaxGuest != "") { ?>
                <dt class="label" ><?=$words->getInLang('ProfileNumberOfGuests', $profile_language_code);?>:</dt>
                <dd><?php echo $member->MaxGuest ?></dd>
            <? } ?>
            
            <?php if ($member->get_trad("MaxLenghtOfStay", $profile_language) != "") { ?>
                <dt class="label" ><?=$words->getInLang('ProfileMaxLenghtOfStay', $profile_language_code);?>:</dd>
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

    <div class="profile_address" >
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
                        <li><?php echo $member->zip ?></li>
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
                        <li class="label" ><?=$words->getInLang('Messenger', $profile_language_code);?></li>
                        <?php
                        if(isset($messengers))
                        { ?>
                        <li>
                          <?php
                            foreach($messengers as $m) {
                                echo "<img src='".PVars::getObj('env')->baseuri."bw/images/icons1616/".$m["image"]."' width='16' height='16' title='".$m["network"]."' alt='".$m["network"]."' />"
                                    .$m["network"].": ".$m["address"]."<br />";
                            }
                          ?>
                        </li>
                            <?php
                            }
                            ?>
                    </ul>
                    <?php
                    if (isset($website))
                    { ?>
                    <ul>
                        <li class="label"><?=$words->getInLang('Website', $profile_language_code);?></li>
                        <li><a href="http://<?php echo $member->WebSite ?>" ><?php echo $member->WebSite ?></a></li>
                    </ul>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div> <!-- profile_address -->
</div> <!-- profile -->
