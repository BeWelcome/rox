

<div id="profilesummary" class="floatbox box">
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="editmyprofile/<?php echo $profile_language_code; ?>"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3 class="icon info22" ><?=$words->get('ProfileSummary');?></h3>
    <?php
    $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();
    echo $purifier->purify(stripslashes($member->get_trad("ProfileSummary", $profile_language,true)));
?>
</div> <!-- profilesummary -->

<div id="languages" class="floatbox box">
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="editmyprofile/<?php echo $profile_language_code; ?>"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3><?php echo $words->get('ProfileLanguagesSpoken'); ?></h3>
    <ul class="icon profile_languages">
<?php
        foreach ($member->get_languages_spoken() as $lang)
        {
            echo <<<HTML
            <li>{$words->get($lang->WordCode)} <sup>{$words->get("LanguageLevel_" . $lang->Level)}</sup></li>
HTML;
        }
        echo <<<HTML
    </ul>
</div> <!-- profile_languages -->

HTML;
        if ($member->get_trad("Hobbies", $profile_language,true) != "" || $member->get_trad("Organizations", $profile_language,true) != "" || $member->get_trad("Books", $profile_language,true) != "" || $member->get_trad("Music", $profile_language,true) != "" || $member->get_trad("Movies", $profile_language,true) != "")
        { 
?>
<div id="interests" class="floatbox box">
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="editmyprofile/<?php echo $profile_language_code; ?>#!profileinterests"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3 class="icon sun22"><?php echo $words->get('ProfileInterests'); ?></h3>
    <dl>
<?php
            if ($member->get_trad("Hobbies", $profile_language,true) != "")
            {
                echo <<<HTML
            <dt class="label">{$words->get('ProfileHobbies')}:</dt>
            <dd>{$purifier->purify($member->get_trad("Hobbies", $profile_language,true))}</dd>
HTML;
            }
            
            if ($member->get_trad("Books", $profile_language,true) != "")
            { 
                echo <<<HTML
            <dt class="label">{$words->get('ProfileBooks')}:</dt>
            <dd>{$purifier->purify($member->get_trad("Books", $profile_language,true))}</dd>
HTML;
            }
        
            if ($member->get_trad("Music", $profile_language,true) != "")
            {
                echo <<<HTML
            <dt class="label">{$words->get('ProfileMusic')}:</dt>
            <dd>{$purifier->purify($member->get_trad("Music", $profile_language,true))}</dd>
HTML;
            }
        
            if ($member->get_trad("Movies", $profile_language,true) != "")
            {
                echo <<<HTML
            <dt class="label">{$words->get('ProfileMovies')}:</dt>
            <dd>{$purifier->purify($member->get_trad("Movies", $profile_language,true))}</dd>
HTML;
            }
            
            if ($member->get_trad("Organizations", $profile_language,true) != "")
            {
                echo <<<HTML
            <dt class="label" >{$words->get('ProfileOrganizations')}:</dt>
            <dd>{$purifier->purify($member->get_trad("Organizations", $profile_language,true))}</dd>
HTML;
            }
            echo <<<HTML
            </dl>
    </div>
HTML;
        }


        if ($member->get_trad("PastTrips", $profile_language,true) != "" || $member->get_trad("PlannedTrips", $profile_language,true) != "")
        {
?>
<div id="travel" class="floatbox box">
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="editmyprofile/<?php echo $profile_language_code; ?>#!profileinterests"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3 class="icon world22" ><?php echo $words->get('ProfileTravelExperience'); ?></h3>
    <dl>
        <dt><?php echo $words->get('ProfilePastTrips'); ?>:</dt>
        <dd><?php echo $purifier->purify($member->get_trad("PastTrips", $profile_language,true)); ?></dd>
        <dt><?php echo $words->get('ProfilePlannedTrips'); ?>:</dt>
        <dd><?php echo $purifier->purify($member->get_trad("PlannedTrips", $profile_language,true)); ?></dd>
    </dl>
</div>
<?php
        }

        // display my groups, if there are any
        $my_groups = $member->getGroups();
        if (!empty($my_groups)) :
            echo <<<HTML
<div id="groups" class="floatbox box">
HTML;
?>
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="/groups/mygroups"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
<?php
            // display my groups, if there are any
            echo "<h3>{$words->get('ProfileGroups')}</h3>";
            for ($i = 0; $i < count($my_groups) && $i < 3; $i++) :
                $group_img = ((strlen($my_groups[$i]->Picture) > 0) ? "groups/thumbimg/{$my_groups[$i]->getPKValue()}" : 'images/icons/group.png' );
                $group_id = $my_groups[$i]->id;
                $group_name = htmlspecialchars($my_groups[$i]->Name, ENT_QUOTES);
                $comment = $purifier->purify($words->mInTrad($member->getGroupMembership($my_groups[$i])->Comment,$profile_language));
                echo <<<HTML
                <div class="groupbox floatbox">
                    <a href="groups/{$group_id}">
                        <img class="framed float_left"  width="50px" height="50px" alt="Group" src="{$group_img}"/>
                    </a>
                    <div class="groupinfo">
                    <h4><a href="groups/{$group_id}">{$group_name}</a></h4>
                    <p>{$comment}</p>
                    </div>  <!-- groupinfo -->
                </div> <!-- groupbox clearfix -->
HTML;
            endfor;
            echo <<<HTML
            <p class="float_right"><a href="members/{$member->Username}/groups">{$words->get('GroupsAllMyLink')}</a></p>
</div> <!-- profile_groups -->
HTML;
            endif;
?>


<?php if ($this->model->getLoggedInMember()) : ?>
<div class="address box" >
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="editmyprofile/<?php echo $profile_language_code; ?>#!contactinfo"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3 class="icon contact22" ><?=$words->get('ContactInfo');?></h3>
    <dl id="address">
        <dt><?=$words->get('Name');?>:</dt>
        <dd><?php echo $member->name?></dd>

        <dt><?=$words->get('Address');?>:</dt>
        <dd><?php echo $member->street?><br />
            <?php echo $member->zip ?>
            <?php echo $member->city ?><br />
            <?php echo $member->country ?>
        </dd>
        
        <?php
        if ($phones = $member->phone)
        {
            echo <<<HTML
            <dt>{$words->get('ProfilePhone')}:</dt>
HTML;
            foreach ($phones as $phone => $value)
            {
                echo <<<HTML
                <dd>{$words->get('Profile'.$phone)}: {$value}</dd>
HTML;
            }
        }
        
        if (!empty($website))
        {
            $sites = explode(" ", str_replace(array("\r\n", "\r", "\n"), " ", $member->WebSite));
            echo <<<HTML
            <dt>{$words->get('Website')}:</dt>
HTML;
            foreach ($sites as $site)
            {
                $site = str_replace(array('http://','https://'), '', $site);
                echo <<<HTML
            <dd><a href="http://{$site}">{$site}</a></dd>
HTML;
            }
        }
        if($member->hasMessengers())
        {
        ?>
        <dt><?=$words->get('Messenger');?>:</dt>
        <dd>
          <?php
            foreach($messengers as $m) {
                if (isset($m["address"]) && $m["address"] != '')
                echo "<img src='".PVars::getObj('env')->baseuri."bw/images/icons1616/".$m["image"]."' width='16' height='16' title='".$m["network"]."' alt='".$m["network"]."' /> "
                    .$m["network"].": ".$m["address"]."<br />";
            }
          ?>
        </dd>
        <?php } ?>
        </dl>   
</div> <!-- profile_address -->
<?php endif; ?>
