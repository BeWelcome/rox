<div id="accommodationinfo" class="card mb-3">
    <h3 class="card-header<? if ($member->Accomodation == 'neverask'){ echo ' bg-secondary'; } ?>"><?php echo $words->get('ProfileAccommodation'); ?>
        <?php if ($showEditLinks): ?>
            <span class="float-right">
                    <a href="editmyprofile/<?php echo $profile_language_code; ?>#!profileaccommodation" class="btn btn-sm <? if ($member->Accomodation == 'neverask'){ echo ' btn-secondary'; } else { echo 'btn-primary'; } ?> p-0"><?= $words->get('Edit'); ?></a>
                </span>
        <?php endif; ?>
    </h3>
    <div class="card-block p-2">
        <div class="card-text m-0">

            <div id="quickinfo" class="float-right text-right">
                <?php
                $icons = array();
                if (strstr($member->TypicOffer, "CanHostWeelChair"))
                {
                    $icons[] = '<img src="images/icons/wheelchairblue.png" ' .
                        'alt="' . $words->getSilent('wheelchair') . '" ' .
                        'title="' . $words->getSilent('CanHostWheelChairYes') . '" />';
                }

                $icons[] = '<img src="images/icons/' . $member->Accomodation . '.png"' .
                    ' alt="' . $words->getSilent($member->Accomodation) .'"' .
                    ' title="' . $words->getSilent('CanOffer' . $member->Accomodation) . '" />';

                for($ii=0; $ii < count($icons); $ii++)
                {
                    echo $icons[$ii];
                }
                ?>
            </div>

            <div id="accommodation" >
                <?php if ($member->MaxGuest != 0 && $member->MaxGuest != "") { ?>
                    <div class="guests h5"><?=$words->get('ProfileNumberOfGuests');?>: <?php echo $member->MaxGuest ?></div>
                <?php }
                if ($member->get_trad("MaxLenghtOfStay", $profile_language,true) != "") { ?>
                    <div class="stay h5 mb-0"><?=$words->get('ProfileMaxLenghtOfStay');?>:</div>
                    <div><?php echo $purifier->purify($member->get_trad("MaxLenghtOfStay", $profile_language,true)); ?></div>
                <?php }
                if ($member->get_trad("ILiveWith", $profile_language,true) != "") { ?>
                    <div class="h5 mb-0"><?=$words->get('ProfileILiveWith');?>:</div>
                    <div><?php echo $purifier->purify($member->get_trad("ILiveWith", $profile_language,true)); ?></div>
                <?php }
                if ($member->get_trad("PleaseBring", $profile_language,true) != "") { ?>
                    <div class="h5 mb-0"><?=$words->get('ProfilePleaseBring');?>:</div>
                    <div><?php echo $purifier->purify($member->get_trad("PleaseBring", $profile_language,true)); ?></div>
                <?php }

                $comma = false;
                $offers = '';

                $TabTypicOffer = explode(",", $member->TypicOffer);
                foreach($TabTypicOffer as $typicOffer) {
                    if ($typicOffer == '') continue;
                    if ($typicOffer == 'CanHostWeelChair') continue;
                    if ($comma) {
                        $offers .= '<br>';
                    }
                    $offers .=  $words->get("ProfileTypicOffer_" . $typicOffer);
                    $comma = true;
                }

                $offerGuests = $member->get_trad("OfferGuests", $profile_language,true);
                if (!empty($offerGuests)) {
                    if ($comma) {
                        $offers .= '<br>';
                    }
                    $offers .= $purifier->purify($member->get_trad("OfferGuests", $profile_language,true));
                }
                if (!empty($offers)) { ?>

                    <div class="h5 mb-0"><?=$words->get('ProfileOfferGuests');?>:</div>
                    <div><?php echo $offers;?></div>
                <?php }
                if ($member->get_trad("OfferHosts", $profile_language,true) != "") { ?>
                    <div class="h5 mb-0"><?=$words->get('ProfileOfferHosts');?>:</div>
                    <div><?php echo $purifier->purify($member->get_trad("OfferHosts", $profile_language,true)); ?></div>
                <?php }
                if ($member->get_trad("AdditionalAccomodationInfo", $profile_language,true) != ""
                    or $member->get_trad("InformationToGuest", $profile_language,true) != "") { ?>
                    <div class="h5 mb-0"><?=$words->get('OtherInfosForGuest');?>:</div>
                    <div>
                        <?php echo $purifier->purify($member->get_trad("AdditionalAccomodationInfo", $profile_language,true)); ?>
                        <?php echo $purifier->purify($member->get_trad("InformationToGuest", $profile_language,true)); ?>
                    </div>
                <?php }

                if ($member->get_trad("PublicTransport", $profile_language,true) != "") { ?>
                    <div class="h5 mb-0"><?=$words->get('ProfilePublicTransport');?>:</div>
                    <div><?php echo $purifier->purify($member->get_trad("PublicTransport", $profile_language,true)); ?></div>
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
                        $restrictions .= '<br>';
                    }
                    $restrictions .= $words->get("Restriction_" . $restriction);
                    $comma = true;
                }
                if (!empty($otherRestrictions)) {
                    if ($comma) {
                        $restrictions .= '<br>';
                    }
                    $restrictions .= $purifier->purify($otherRestrictions);
                }
                if (!empty($restrictions)) { ?>
                    <div class="h5 mb-0"><?=$words->get('ProfileHouseRules');?>:</div>
                    <div><?php echo $restrictions; ?></div>
                <?php } ?>
            </div>
            <? if (!$this->myself && $member->Accomodation != 'neverask') { ?>
            <div>
                <i class="fa fa-bed mr-1"></i><a href="new/request/<?= $member->Username ?>" class="btn btn-primary float-right"><?=$words->get('profile.request.hosting');?></a>
            </div>
            <? } ?>
        </div>
    </div>
</div>