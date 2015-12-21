<?php
/**********************
**   Accomodation   **
**********************/

if (!$this->passedAway){ ?>
    <div id="accommodationinfo" class="clearfix box">
        <?php if ($showEditLinks): ?>
        <span class="float_right profile-edit-link">
            <a href="editmyprofile/<?php echo $profile_language_code; ?>#!profileaccommodation">
                <?php echo $words->get('Edit'); ?>
            </a>
        </span>
        <?php endif; ?>
        <h3 class="icon accommodation22" ><?=$words->get('ProfileAccommodation');?></h3>
        <div id="quickinfo" class="float_right" style="text-align: right;">
    <?php
    $icons = array();
    if (strstr($member->TypicOffer, "CanHostWeelChair"))
    {
        $icons[] = '<img src="images/icons/wheelchairblue.png" ' .
                        'alt="' . $words->getSilent('wheelchair') . '" ' .
                        'title="' . $words->getSilent('CanHostWheelChairYes') . '" />';
    }
    switch($member->Accomodation)
    {
        case 'anytime':
            $icons[] = '<img src="images/icons/yesicanhost.png"' .
                           ' alt="' . $words->getSilent('yesicanhost') .'"' .
                           ' title="' . $words->getSilent('CanOfferAccomodation') . '" />';
            break;
        case 'dependonrequest':
            $icons[] = '<img src="images/icons/maybe.png"' .
                           ' alt="' . $words->getSilent('dependonrequest') .'"' .
                           ' title="' . $words->getSilent('CanOfferdependonrequest') . '" />';
            break;
        case 'neverask':
            $icons[] = '<img src="images/icons/nosorry.png"' .
                           ' alt="' . $words->getSilent('neverask') .'"' .
                           ' title="' . $words->getSilent('CannotOfferneverask') . '" />';
            break;
    }
    
    for($ii=0; $ii < count($icons); $ii++)
    {
        echo $icons[$ii];
    }
    ?>
    </div> <!-- quickinfo -->
        <dl id="accommodation" >
        <?php if ($member->MaxGuest != 0 && $member->MaxGuest != "") { ?>
                <dt class="guests" ><?=$words->get('ProfileNumberOfGuests');?>:</dt>
                <dd><?php echo $member->MaxGuest ?></dd>
        <?php }
            if ($member->get_trad("MaxLenghtOfStay", $profile_language,true) != "") { ?>
                <dt class="stay" ><?=$words->get('ProfileMaxLenghtOfStay');?>:</dt>
                <dd><?php echo $purifier->purify($member->get_trad("MaxLenghtOfStay", $profile_language,true)); ?></dd>
        <?php }
            if ($member->get_trad("ILiveWith", $profile_language,true) != "") { ?>
                <dt><?=$words->get('ProfileILiveWith');?>:</dt>
                <dd><?php echo $purifier->purify($member->get_trad("ILiveWith", $profile_language,true)); ?></dd>
        <?php }
            if ($member->get_trad("PleaseBring", $profile_language,true) != "") { ?>
                <dt><?=$words->get('ProfilePleaseBring');?>:</dt>
                <dd><?php echo $purifier->purify($member->get_trad("PleaseBring", $profile_language,true)); ?></dd>
        <?php }
    
            $comma = false;
            $offers = '';
    
            $TabTypicOffer = explode(",", $member->TypicOffer);
            foreach($TabTypicOffer as $typicOffer) {
                if ($typicOffer == '') continue;
                if ($typicOffer == 'CanHostWeelChair') continue;
                if ($comma) {
                    $offers .= ', ';
                }
                $offers .=  $words->get("ProfileTypicOffer_" . $typicOffer);
                $comma = true;
            }
            if ($comma) {
                $offers .= '.';
            }
    
            $offerGuests = $member->get_trad("OfferGuests", $profile_language,true);
            if (!empty($offerGuests)) {
                if ($comma) {
                    $offers .= '<br /><br />';
                }
                $offers .= $purifier->purify($member->get_trad("OfferGuests", $profile_language,true));
            }
            if (!empty($offers)) { ?>
    
            <dt><?=$words->get('ProfileOfferGuests');?>:</dt>
            <dd><?php echo $offers;?></dd>
        <?php }
            if ($member->get_trad("OfferHosts", $profile_language,true) != "") { ?>
                <dt><?=$words->get('ProfileOfferHosts');?>:</dt>
                <dd><?php echo $purifier->purify($member->get_trad("OfferHosts", $profile_language,true)); ?></dd>
        <?php }
            if ($member->get_trad("AdditionalAccomodationInfo", $profile_language,true) != ""
                or $member->get_trad("InformationToGuest", $profile_language,true) != "") { ?>
                <dt><?=$words->get('OtherInfosForGuest');?>:</dt>
                <dd>
                    <?php echo $purifier->purify($member->get_trad("AdditionalAccomodationInfo", $profile_language,true)); ?>
                    <?php echo $purifier->purify($member->get_trad("InformationToGuest", $profile_language,true)); ?>
                </dd>
        <?php } 
    
        if ($member->get_trad("PublicTransport", $profile_language,true) != "") { ?>
                <dt><?=$words->get('ProfilePublicTransport');?>:</dt>
                <dd><?php echo $purifier->purify($member->get_trad("PublicTransport", $profile_language,true)); ?></dd>
        <?php }
        
            $restrictions = '';
            $TabRestrictions = explode(",", $member->Restrictions);
            $max = count($TabRestrictions);
    
            $otherRestrictions = $member->get_trad("OtherRestrictions", $profile_language, true);
    
            $comma = false;
            foreach($TabRestrictions as $restriction) {
                if ($restriction == '') continue;
                if ($restriction == 'SeeOtherRestrictions') continue;
                if ($comma) {
                    $restrictions .= ', ';
                }
                $restrictions .= $words->get("Restriction_" . $restriction);
                $comma = true;
            }
            if ($comma) {
                $restrictions .= '.';
            }
            if (!empty($otherRestrictions)) {
                if ($comma) {
                    $restrictions .= '<br /><br />';
                }
                $restrictions .= $purifier->purify($otherRestrictions);
            }
            if (!empty($restrictions)) { ?>
                <dt><?=$words->get('ProfileHouseRules');?>:</dt>
                <dd><?php echo $restrictions; ?></dd>
            <?php } ?>
        </dl>
    </div> <!-- profile_accommodation -->
<?php
}


// Linkpath widget 

    // display linkpath, only if not the members own profile
    if (isset($_SESSION["IdMember"]) && strcmp($member->id,$_SESSION["IdMember"]) != 0) {
        $linkwidget = new LinkSinglePictureLinkpathWidget();
        $linkwidget->render($_SESSION["IdMember"],$member->id,'linkpath');
    }
/**********************
** Profile Relations **
**********************/

$purifier = MOD_htmlpure::getBasicHtmlPurifier();
$relations = $member->relations;
if (count($relations) > 0) { ?>
    <div id="relations" class="clearfix box">
        <?php if ($showEditLinks): ?>
        <span class="float_right profile-edit-link">
            <a href="editmyprofile/<?php echo $profile_language_code; ?>#!specialrelations">
                <?php echo $words->get('Edit'); ?>
            </a>
        </span>
        <?php endif; ?>
        <h3><?php echo $words->get('MyRelations');?></h3>
<?php   if ($this->model->getLoggedInMember()){ ?>

        <ul class="linklist">
            <?php
                foreach ($relations as $rel) {
                    $comment = $words->mInTrad($rel->IdTradComment, $profile_language, true);

                    // Hack to filter out accidental '0' or '123456' comments that were saved
                    // by users while relation comment update form was buggy (see #1580)
                    if (is_numeric($comment)) {
                        $comment = '';
                    }

                    $rel->Comment = $purifier->purify($comment);
            ?>
            <li class="clearfix">
                <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>">
                    <img class="framed float_left"  src="members/avatar/<?=$rel->Username?>?xs"  height="50px"  width="50px"  alt="Profile" />
                </a>
                <a class="float_left" href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
                <br />
                <?php echo $rel->Comment; ?>
            </li>
          <?php } ?>
        </ul>
    <?php } else {
        echo $this->getLoginLink('/members/' . $member->Username,'ProfileShowRelations');
    }?>
    
    </div> <!-- relations -->
<?php }
/*********************
** Profile Comments **
*********************/
    $comments = $this->member->comments;
    $username = $this->member->Username;
    $layoutbits = new MOD_layoutbits();
    

    $max = 3;
    if (count($comments) > 0) {
        ?> <div id="comments" class="clearfix box"> <?php
            if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="members/<?php echo $member->Username; ?>/comments/"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3><?php if ($this->passedAway) {
            echo $words->get('LatestCommentsAndCondolences');
        } else {
            echo $words->get('LatestComments');
        }
    ?></h3>

    <?php
        if ($this->model->getLoggedInMember()){
        $tt = array ();
        $commentLoopCount = 0;
        foreach ($comments as $c) {
            // skip items that are hidden for public
            if ($c->DisplayInPublic == 0) {continue;}
            // stop looping when maximum has been reached
            if (++$commentLoopCount>$max){break;}
            $quality = "neutral";
            if ($c->comQuality == "Good") {
                $quality = "good";
            }
            if ($c->comQuality == "Bad") {
                $quality = "bad";
            }
    ?>
    <div class="clearfix">
        <a href="members/<?=$c->UsernameFromMember?>">
           <img class="float_left framed"  src="members/avatar/<?=$c->UsernameFromMember?>/?xs"  height="50px"  width="50px"  alt="Profile" />
        </a>
        <div class="comment">
            <p>
              <?php if (!$this->passedAway) { ?>
                <strong class="<?=$quality?>"><?=$c->comQuality?></strong><br/><?php }?>
              <span class="small grey"><?=$words->get('CommentFrom','<a href="members/'.$c->UsernameFromMember.'">'.$c->UsernameFromMember.'</a>')?> - <?=$c->created?></span>
            </p>
            <p>
              <?php
                  $textStripped = strip_tags($c->TextFree, '<font>');
                  echo $textStripped;
              ?>
            </p>
          <?php if ($commentLoopCount < $max) echo '<hr />' ?>
        </div> <!-- comment -->
    </div> <!-- clearfix -->
        <?php } ?>
    <p class="float_right"><a href="members/<?=$member->Username?>/comments/"><?=$words->get('ShowAllComments')?></a></p>
<?php } else {
        // hide comments from others when not logged in
        echo $this->getLoginLink('/members/' . $member->Username,'ProfileShowComments');
} ?>
    </div> <!-- comments -->
<?php }

/**********************
**   Profile Trips   **
**********************/

if ($comingposts = $member->getComingPosts()) {
    ?>
            <div id="trips" class="clearfix box">
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="/trip/show/my"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3><?php echo $words->getSilent('TripsUpComing');?></h3>
    <ul>
    <?php
    foreach ($comingposts as $blog) {
        $date = date("d M Y", strtotime($blog->blog_start));
        $geoname = ($blog->getGeo()) ? $blog->getGeo()->name : $blog->title;
        ?>
        <li><a href="trip/show/<?php echo $member->Username;?>" title="<?php echo $words->getSilent('TripsUpComing');?>">
                <?php echo $geoname;?>
            </a>
            - <?php echo $date;?>
        </li>
    <?php
    }
    ?>
    </ul>
    <p class="float_right">
        <a href="trip/show/<?php echo $member->Username;?>" title="<?php echo $words->getSilent('TripsUpComing');?>">
        <?php echo $words->get('TripsShowAll');?></a>
    </p>
    </div>
    <?php
}

/**********************
** Profile Gallery  **
**********************/

$userid = $member->userid;
$gallery = new GalleryModel;
$statement = $userid ? $gallery->getLatestItems($userid) : false;
if ($statement) {
?>
          <div id="gallery" class="clearfix box">
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="/gallery/manage"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3><?php echo $words->get('GalleryTitleLatest'); ?></h3>
    <?php
    // if the gallery is NOT empty, go show it
    $p = PFunctions::paginate($statement, 1, $itemsPerPage = 8);
    $statement = $p[0];
          echo '<div class="clearfix">';
    foreach ($statement as $d) {
        echo '<a href="gallery/show/image/'.$d->id.'">' .
           '<img src="gallery/thumbimg?id='.$d->id.'"' .
               ' alt="image"' .
               ' style="height: 50px; width: 50px; padding:2px;"/>' .
           '</a>';
    }
    echo '</div>';
    ?>
    <p class="float_right">
      <a href="gallery/show/user/<?php echo $member->Username;?>/images" title="<?php echo $words->getSilent('GalleryTitleLatest');?>">
          <?php echo $words->get('GalleryShowAll');?></a>
    </p>
    </div>
    <?php echo $words->flushBuffer();
}
