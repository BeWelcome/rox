<div id="accommodationinfo" class="floatbox box">
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="editmyprofile/<?php echo $profile_language_code; ?>#!profileaccommodation"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3 class="icon accommodation22" ><?=$words->get('ProfileAccommodation');?></h3>
    <div id="quickinfo" class="float_right" style="text-align: right;">
<?php
$icons = array();
switch($member->Accomodation)
{
    case 'anytime':
        $icons[] = '<img src="images/icons/yesicanhost.png" alt="' . $words->getSilent('yesicanhost') .'"' .
                   ' title="' . $words->getSilent('CanOfferAccomodation') . '" />';
        break;
    case 'dependonrequest': 
        $icons[] = '<img src="images/icons/maybe.png" alt="' . $words->getSilent('dependonrequest') .'"' .
                ' title="' . $words->getSilent('CanOfferdependonrequest') . '" />';
        break;
    case 'neverask':
        $icons[] = '<img src="images/icons/nosorry.png" alt="' . $words->getSilent('neverask') .'"' .
                ' title="' . $words->getSilent('CannotOfferneverask') . '" />';
        break;
}
// showing offer icons (several posibilities)
if (strstr($member->TypicOffer, "CanHostWeelChair"))
{
    $icons[] = '<img src="images/icons/wheelchair.png" alt="' . $words->getSilent('wheelchair') . '" title="' . $words->getSilent('CanHostWeelChairYes') . '" />';
}
if (strstr($member->TypicOffer, "guidedtour"))
{
    $icons[] = '<img src="images/icons/guidedtour.png" alt="' . $words->getSilent('CanOfferCityTour') . '" title="' . $words->getSilent('CanOfferCityTour') . '" />';
}
if (strstr($member->TypicOffer, "dinner"))
{
    $icons[] = '<img src="images/icons/dinner.png" alt="' . $words->getSilent('TypicOffer_dinner') . '" title="' . $words->getSilent('TypicOffer_dinner') . '" />';
}
// showing restriction icons (several posibilities)
if (strstr($member->Restrictions, "NoSmoker"))
{
    $icons[] = '<img src="images/icons/no-smoking.png" alt="' . $words->getSilent('Restriction_NoSmoker') . '" title="' . $words->getSilent('Restriction_NoSmoker') . '" />';
}
if (strstr($member->Restrictions, "NoAlchool"))
{
    $icons[] = '<img src="images/icons/no-alcohol.png" alt="' . $words->getSilent('Restriction_NoAlchool') . '" title="' . $words->getSilent('Restriction_NoAlchool') . '" />';
}
if (strstr($member->Restrictions, "NoDrugs"))
{
    $icons[] = '<img src="images/icons/no-drugs.png" alt="' . $words->getSilent('Restriction_NoDrugs') . '" title="' . $words->getSilent('Restriction_NoDrugs') . '" />';
}
$modul = count($icons) < 5 ? $modul = 5 : $modul = 2; 
for($ii=0; $ii < count($icons); $ii++)
{
    echo $icons[$ii];
    if (($ii + 1) % $modul == 0) {
        echo '<br />';
    }
}
?>
</div> <!-- quickinfo -->
    <dl id="accommodation" >
        <?php if ($member->MaxGuest != 0 && $member->MaxGuest != "") { ?>
            <dt class="label guests" ><?=$words->get('ProfileNumberOfGuests');?>:</dt>
            <dd><?php echo $member->MaxGuest ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("MaxLenghtOfStay", $profile_language,true) != "") { ?>
            <dt class="label stay" ><?=$words->get('ProfileMaxLenghtOfStay');?>:</dt>
            <dd><?php echo $purifier->purify($member->get_trad("MaxLenghtOfStay", $profile_language,true)); ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("ILiveWith", $profile_language,true) != "") { ?>
            <dt class="label" ><?=$words->get('ProfileILiveWith');?>:</dt>
            <dd><?php echo $purifier->purify($member->get_trad("ILiveWith", $profile_language,true)); ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("PleaseBring", $profile_language,true) != "") { ?>
            <dt class="label" ><?=$words->get('ProfilePleaseBring');?>:</dt>
            <dd><?php echo $purifier->purify($member->get_trad("PleaseBring", $profile_language,true)); ?></dd>
        <? } ?>
        
        <?php
        if ($member->get_trad("OfferGuests", $profile_language,true) != "") { ?>
            <dt class="label" ><?=$words->get('ProfileOfferGuests');?>:</dt>
            <dd><?php echo $purifier->purify($member->get_trad("OfferGuests", $profile_language,true)); ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("OfferHosts", $profile_language,true) != "") { ?>
            <dt class="label" ><?=$words->get('ProfileOfferHosts');?>:</dt>
            <dd><?php echo $purifier->purify($member->get_trad("OfferHosts", $profile_language,true)); ?></dd>
        <? } ?>
        
        <?php if ($member->get_trad("AdditionalAccomodationInfo", $profile_language,true) != "" or $member->get_trad("InformationToGuest", $profile_language,true) != "") { ?>
            <dt class="label" ><?=$words->get('OtherInfosForGuest');?>:</dt>
            <dd>
                <?php echo $purifier->purify($member->get_trad("AdditionalAccomodationInfo", $profile_language,true)); ?>
                <?php echo $purifier->purify($member->get_trad("InformationToGuest", $profile_language,true)); ?>
            </dd>
        <? } ?>

        <?php if ($member->get_trad("PublicTransport", $profile_language,true) != "") { ?>
            <dt class="label" ><?=$words->get('ProfilePublicTransport');?>:</dt>
            <dd><?php echo $purifier->purify($member->get_trad("PublicTransport", $profile_language,true)); ?></dd>
        <? } ?>
        
        <?php
        $TabRestrictions = explode(",", $member->Restrictions);
        $max = count($TabRestrictions);
        if (($max > 0 and $TabRestrictions[0] != "") or ($member->Restrictions != "")) {
        ?>
            <dt class="label" ><?=$words->get('ProfileRestrictionForGuest');?>:</dt>
            <?php
                if ($max > 0) {
                  echo "<dd>\n";
                    for ($ii = 0; $ii < $max; $ii++) {
                        echo ($ii > 0) ? ', <br />' : '';
                        echo $words->get("Restriction_" . $TabRestrictions[$ii]);
                    }
                    echo "</dd>\n";
                }
            ?>
            
            <dt class="label" ><?=$words->get('ProfileOtherRestrictions');?>:</dt>
            <dd><?php echo $purifier->purify($member->get_trad("OtherRestrictions", $profile_language,true)); ?></dd>
            
        <? } ?>
    </dl>
</div> <!-- profile_accommodation -->

<? // Linkpath widget ?>
    <?
    // display linkpath, only if not the members own profile
    if (isset($_SESSION["IdMember"]) && strcmp($member->id,$_SESSION["IdMember"]) != 0) {
        $linkwidget = new LinkSinglePictureLinkpathWidget();
        $linkwidget->render($_SESSION["IdMember"],$member->id,'linkpath');
    } 
?>

<? // Profile Relations ?>
<?php 
$purifier = MOD_htmlpure::getBasicHtmlPurifier();
$relations = $member->relations;
if (count($relations) > 0) { ?>
    <div id="relations" class="floatbox box">
        <?php if ($showEditLinks): ?>
        <span class="float_right profile-edit-link">
            <a href="editmyprofile/<?php echo $profile_language_code; ?>#!specialrelations"><?php echo $words->get('Edit'); ?></a>
        </span>
        <?php endif; ?>
        <h3><?php echo $words->get('MyRelations');?></h3>
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
            <li class="floatbox">
                <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>">
                    <img class="framed float_left"  src="members/avatar/<?=$rel->Username?>?xs"  height="50px"  width="50px"  alt="Profile" />
                </a>
                <a class="float_left" href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
                <br />
                <?php echo $rel->Comment; ?>
            </li>
          <?php } ?>
        </ul>
    </div> <!-- relations -->
<?php } ?>


<? // Profile Comments ?>
<?php
    $comments = $this->member->comments;
    $username = $this->member->Username;
    $layoutbits = new MOD_layoutbits();
    $max = 3;
    if (count($comments) > 0) { 
?>

  <div id="comments" class="floatbox box">
    <?php if ($showEditLinks): ?>
    <span class="float_right profile-edit-link">
        <a href="members/<?php echo $member->Username; ?>/comments/"><?php echo $words->get('Edit'); ?></a>
    </span>
    <?php endif; ?>
    <h3><?php echo $words->get('LatestComments')?></h3> 

    <?php
        $iiMax = (isset($max) && count($comments) > $max) ? $max : count($comments);
        $tt = array ();
        for ($ii = 0; $ii < $iiMax; $ii++) {
            $c = $comments[$ii];
            $quality = "neutral";
            if ($c->comQuality == "Good") {
                $quality = "good";
            }
            if ($c->comQuality == "Bad") {
                $quality = "bad";
            }

        $tt = explode(",", $comments[$ii]->Lenght);
        // var_dump($c);
    ?>
    <div class="floatbox">
        <a href="members/<?=$c->Username?>">
           <img class="float_left framed"  src="members/avatar/<?=$c->Username?>/?xs"  height="50px"  width="50px"  alt="Profile" />
        </a>
        <div class="comment">
            <p>
              <strong class="<?=$quality?>"><?=$c->comQuality?></strong><br/>
              <span class="small grey"><?=$words->get('CommentFrom','<a href="members/'.$c->Username.'">'.$c->Username.'</a>')?> - <?=$c->created?></span>
            </p>
            <p>
              <?php
                  $textStripped = strip_tags($c->TextFree, '<font>');
                  $moreLink = '... <a href="members/' . $member->Username . '/comments">' . $ww->more . '</a>';
                  echo MOD_layoutbits::truncate($textStripped, 250, $moreLink);
              ?>
            </p>
          <? if ($ii != ($iiMax-1)) echo '<hr />' ?>
        </div> <!-- comment -->
    </div> <!-- floatbox -->
        <? } ?>
    <p class="float_right"><a href="members/<?=$member->Username?>/comments/"><?=$words->get('ShowAllComments')?></a></p>
  </div> <!-- comments -->
<? } ?>

<?php
        // This member's upcoming trips
        if ($comingposts = $member->getComingPosts()) {
            ?>
            <div id="trips" class="floatbox box">
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
                <a href="trip/show/<?php echo $member->Username;?>" title="<?php echo $words->getSilent('TripsUpComing');?>"><?php echo $words->get('TripsShowAll');?></a>
            </p>
            </div>
            <?php
        }

    // This member's gallery
      $userid = $member->userid;
      $gallery = new GalleryModel;
      $statement = $userid ? $gallery->getLatestItems($userid) : false;
      if ($statement) {
?>
          <div id="gallery" class="floatbox box">
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
          echo '<div class="floatbox">';
          foreach ($statement as $d) {
            echo '<a href="gallery/show/image/'.$d->id.'"><img src="gallery/thumbimg?id='.$d->id.'" alt="image" style="height: 50px; width: 50px; padding:2px;"/></a>';
          }
          echo '</div>';
          ?>
          <p class="float_right"><a href="gallery/show/user/<?php echo $member->Username;?>/images" title="<?php echo $words->getSilent('GalleryTitleLatest');?>"><?php echo $words->get('GalleryShowAll');?></a></p>
          </div>
          <?php
        echo $words->flushBuffer();
      }
