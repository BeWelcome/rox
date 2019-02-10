<div id="profilesummary" class="card mb-3">
    <h3 class="card-header bg-secondary"><?php echo $words->get('ProfileSummary'); ?>
        <?php if ($showEditLinks): ?>
            <span class="float-right">
                <a href="editmyprofile/<?php echo $profile_language_code; ?>" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
            </span>
        <?php endif; ?>
    </h3>

    <div class="p-2">
        <?php
        $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();
        echo $purifier->purify(stripslashes($member->get_trad("ProfileSummary", $profile_language, true)));
        ?>
    </div>
</div>

<div class="d-block d-lg-none mb-sm-3 mb-lg-0">
    <?php
    if (!$this->passedAway){
        $accIdSuffix = 'Left';
        require 'profile.subcolumn_accommodation.php';
    }
    ?>
</div>

<div id="languages" class="card mb-3">
    <h3 class="card-header bg-secondary"><?php echo $words->get('ProfileLanguagesSpoken'); ?>
        <?php if ($showEditLinks): ?>
            <span class="float-right">
                <a href="editmyprofile/<?php echo $profile_language_code; ?>" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
            </span>
        <?php endif; ?>
    </h3>

    <div class="p-2">
            <?php
            foreach ($member->get_languages_spoken() as $lang) {
                echo '<p class="m-0">' . $words->get($lang->WordCode) . '<sup class="ml-1 gray">' . $words->get("LanguageLevel_" . $lang->Level) . '</sup></p>';
            }
            ?>
    </div>
</div>

        <?php
            if ($member->get_trad("Hobbies", $profile_language, true) != "" || $member->get_trad("Organizations", $profile_language, true) != "" || $member->get_trad("Books", $profile_language, true) != "" || $member->get_trad("Music", $profile_language, true) != "" || $member->get_trad("Movies", $profile_language, true) != ""){
        ?>
        <div id="interests" class="card mb-3">
            <h3 class="card-header bg-secondary"><?php echo $words->get('ProfileInterests'); ?>
                <?php if ($showEditLinks): ?>
                    <span class="float-right">
                    <a href="editmyprofile/<?php echo $profile_language_code; ?>#!profileinterests" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
                </span>
                <?php endif; ?>
            </h3>
            <div class="p-2">
                    <?php
                    if ($member->get_trad("Hobbies", $profile_language, true) != "") {
                        echo '<div class="h5 mb-0">' . $words->get('ProfileHobbies') . '</div>';
                        echo '<div>' . $purifier->purify($member->get_trad("Hobbies", $profile_language, true)) . '</div>';
                    }

                    if ($member->get_trad("Books", $profile_language, true) != "") {
                        echo '<div class="h5 mb-0">' . $words->get('ProfileBooks') . '</div>';
                        echo '<div>' . $purifier->purify($member->get_trad("Books", $profile_language, true)) . '</div>';
                    }

                    if ($member->get_trad("Music", $profile_language, true) != "") {
                        echo '<div class="h5 mb-0">' . $words->get('ProfileMusic') . '</div>';
                        echo '<div>' . $purifier->purify($member->get_trad("Music", $profile_language, true)) . '</div>';
                    }

                    if ($member->get_trad("Movies", $profile_language, true) != "") {
                        echo '<div class="h5 mb-0">' . $words->get('ProfileMovies') . '</div>';
                        echo '<div>' . $purifier->purify($member->get_trad("Movies", $profile_language, true)) . '</div>';
                    }

                    if ($member->get_trad("Organizations", $profile_language, true) != "") {
                        echo '<div class="h5 mb-0">' . $words->get('ProfileOrganizations') . '</div>';
                        echo '<div>' . $purifier->purify($member->get_trad("Organizations", $profile_language, true)) . '</div>';
                    }
                    ?>
            </div>
        </div>
        <?php } ?>

        <?php
            if ($member->get_trad("PastTrips", $profile_language, true) != "" || $member->get_trad("PlannedTrips", $profile_language, true) != "") {
                ?>

                <div id="travel" class="card mb-3">
                    <h3 class="card-header bg-secondary"><?php echo $words->get('ProfileTravelExperience'); ?>
                        <?php if ($showEditLinks): ?>
                            <span class="float-right">
                    <a href="editmyprofile/<?php echo $profile_language_code; ?>#!profileinterests" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
                </span>
                        <?php endif; ?>
                    </h3>
                    <div class="p-2">
                            <dl>
                                <dt class="h5"><?php echo $words->get('ProfilePastTrips'); ?>:</dt>
                                <dd><?php echo $purifier->purify($member->get_trad("PastTrips", $profile_language, true)); ?></dd>
                                <dt class="h5"><?php echo $words->get('ProfilePlannedTrips'); ?>:</dt>
                                <dd><?php echo $purifier->purify($member->get_trad("PlannedTrips", $profile_language, true)); ?></dd>
                            </dl>
                    </div>
                </div>

                <?php
                    }

        // display my groups, if there are any
        $my_groups = $member->getGroups();
        if (!empty($my_groups)){ ?>

        <div id="groups" class="card mb-3">
            <h3 class="card-header bg-secondary"><?php echo $words->get('ProfileGroups'); ?>
                <?php if ($showEditLinks): ?>
                    <span class="float-right">
                    <a href="/groups/mygroups" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
                </span>
                <?php endif; ?>
            </h3>
            <div class="p-2">
                    <?php
                    // display my groups, if there are any
                    for ($i = 0; $i < count($my_groups) && $i < 5; $i++) :
                        $group_img = ((strlen($my_groups[$i]->Picture) > 0) ? "groups/thumbimg/{$my_groups[$i]->getPKValue()}" : 'images/icons/group.png');
                        $group_id = $my_groups[$i]->id;
                        $group_name = htmlspecialchars($my_groups[$i]->Name, ENT_QUOTES);
                        $comment = strip_tags($purifier->purify($words->mInTrad($member->getGroupMembership($my_groups[$i])->Comment, $profile_language)));
                        ?>
                        <div class="mb-3 d-flex d-column">
                            <div>
                                <a href="groups/<? echo $group_id; ?>">
                                <img class="framed float-left mr-2" width="50" height="50" alt="Group"
                                     src="<? echo $group_img; ?>"/>
                            </a>
                            </div>
                            <div class="text-truncate">
                                <h4 class="m-0 text-truncate"><a href="groups/<? echo $group_id; ?>"><? echo $group_name; ?></a></h4>
                                <p class="m-0 text-truncate"><? echo $comment; ?></p>
                            </div>  <!-- groupinfo -->
                        </div>
                        <?php
                    endfor;
                    if (count($my_groups) > 5) :
                        echo '<a class="btn btn-sm btn-block btn-outline-primary" href="members/' . $member->Username . '/groups">' . $words->get('GroupsAllMyLink') . '</a>';
                    endif;
                    ?>
            </div>
        </div>
        <? } ?>

        <?
        if ($this->model->getLoggedInMember() && !$this->passedAway){ ?>


            <div class="card mb-3">
                <h3 class="card-header bg-secondary"><?php echo $words->get('ContactInfo'); ?>
                    <?php if ($showEditLinks): ?>
                        <span class="float-right">
                    <a href="editmyprofile/<?php echo $profile_language_code; ?>#!contactinfo" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
                </span>
                    <?php endif; ?>
                </h3>
                <div class="p-2">

                        <dl id="address">
                            <dt class="h5"><?php echo $words->get('Name'); ?></dt>
                            <dd><?php echo $member->name ?></dd>

                            <dt class="h5"><?php echo $words->get('Address'); ?></dt>
                            <dd><?php if ($member->street != ""){ echo $member->street; ?><br>
                                <?php echo $member->zip; } ?>
                                <?php echo $member->city ?><br>
                                <?php echo $member->country ?>
                            </dd>

                            <?php
                            if ($phones = $member->phone) {
                                echo '<dt class="h5">' . $words->get('ProfilePhone') .'</dt>';
                                foreach ($phones as $phone => $value) {
                                    echo '<dd>' . $words->get('Profile' . $phone) . ': ' . $value . '</dd>';
                                }
                            }

                            if (!empty($website)) {
                                $sites = explode(" ", str_replace(array("\r\n", "\r", "\n"), " ", $member->WebSite));
                                echo '<dt class="h5">' . $words->get('Website') . '</dt>';
                                foreach ($sites as $site) {
                                    $site = str_replace(array('http://', 'https://'), '', $site);
                                    echo '<dd><a href="http://' . $site . '">' . $site . '</a></dd>';
                                }
                            }
                            if ($member->hasMessengers()) {
                                ?>
                                <dt class="h5"><?php echo $words->get('Messenger'); ?>:</dt>
                                <dd>
                                    <?php
                                    foreach ($messengers as $m) {
                                        if (isset($m["address"]) && $m["address"] != '')
                                            echo "<i class='" . $m["class"] . " fa-" . $m["image"] . "' width='16' height='16' title='" . $m["network"] . "' alt='" . $m["network"] . "'></i> " . $m["network"] . ": " . $m["address"] . "<br>";
                                    }
                                    ?>
                                </dd>
                            <?php } ?>
                        </dl>
                </div>
            </div>
        <? } ?>