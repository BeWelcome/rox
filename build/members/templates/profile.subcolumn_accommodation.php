<div id="accommodationinfo" class="card mb-3">
    <h3 class="card-header"><?php echo $words->get('ProfileAccommodation'); ?>
        <?php if ($showEditLinks): ?>
            <span class="float-right">
                    <a href="editmyprofile/<?php echo $profile_language_code; ?>#!profileaccommodation" class="btn btn-sm btn-primary p-0"><?php echo $words->get('Edit'); ?></a>
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

            <dl id="accommodation" >
                <?php if ($member->MaxGuest != 0 && $member->MaxGuest != "") { ?>
                    <dt class="guests h5"><?=$words->get('ProfileNumberOfGuests');?>: <?php echo $member->MaxGuest ?></dt>
                <?php }
                if ($member->get_trad("MaxLenghtOfStay", $profile_language,true) != "") { ?>
                    <dt class="stay h5"><?=$words->get('ProfileMaxLenghtOfStay');?>:</dt>
                    <dd><?php echo $purifier->purify($member->get_trad("MaxLenghtOfStay", $profile_language,true)); ?></dd>
                <?php }
                if ($member->get_trad("ILiveWith", $profile_language,true) != "") { ?>
                    <dt class="h5"><?=$words->get('ProfileILiveWith');?>:</dt>
                    <dd><?php echo $purifier->purify($member->get_trad("ILiveWith", $profile_language,true)); ?></dd>
                <?php }
                if ($member->get_trad("PleaseBring", $profile_language,true) != "") { ?>
                    <dt class="h5"><?=$words->get('ProfilePleaseBring');?>:</dt>
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
                        $offers .= '<br>';
                    }
                    $offers .= $purifier->purify($member->get_trad("OfferGuests", $profile_language,true));
                }
                if (!empty($offers)) { ?>

                    <dt class="h5"><?=$words->get('ProfileOfferGuests');?>:</dt>
                    <dd><?php echo $offers;?></dd>
                <?php }
                if ($member->get_trad("OfferHosts", $profile_language,true) != "") { ?>
                    <dt class="h5"><?=$words->get('ProfileOfferHosts');?>:</dt>
                    <dd><?php echo $purifier->purify($member->get_trad("OfferHosts", $profile_language,true)); ?></dd>
                <?php }
                if ($member->get_trad("AdditionalAccomodationInfo", $profile_language,true) != ""
                    or $member->get_trad("InformationToGuest", $profile_language,true) != "") { ?>
                    <dt class="h5"><?=$words->get('OtherInfosForGuest');?>:</dt>
                    <dd>
                        <?php echo $purifier->purify($member->get_trad("AdditionalAccomodationInfo", $profile_language,true)); ?>
                        <?php echo $purifier->purify($member->get_trad("InformationToGuest", $profile_language,true)); ?>
                    </dd>
                <?php }

                if ($member->get_trad("PublicTransport", $profile_language,true) != "") { ?>
                    <dt class="h5"><?=$words->get('ProfilePublicTransport');?>:</dt>
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
                        $restrictions .= ',<br>';
                    }
                    $restrictions .= $words->get("Restriction_" . $restriction);
                    $comma = true;
                }
                if ($comma) {
                    $restrictions .= '';
                }
                if (!empty($otherRestrictions)) {
                    if ($comma) {
                        $restrictions .= '<br>';
                    }
                    $restrictions .= $purifier->purify($otherRestrictions);
                }
                if (!empty($restrictions)) { ?>
                    <dt class="h5"><?=$words->get('ProfileHouseRules');?>:</dt>
                    <dd><?php echo $restrictions; ?></dd>
                <?php } ?>
            </dl>

        </div>
    </div>
</div>