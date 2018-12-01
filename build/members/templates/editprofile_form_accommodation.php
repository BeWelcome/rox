<div class="tab-pane fade card" id="accommodation" role="tabpanel" aria-labelledby="accommodation-tab">
    <div class="card-header" role="tab" id="heading-accommodation">
        <h5 class="mb-0">
            <a data-toggle="collapse" href="#collapse-accommodation" data-parent="#content" aria-expanded="true"
               aria-controls="collapse-accommodation">
                <?= $words->get('ProfileAccommodation') ?>
            </a>
        </h5>
    </div>
    <div id="collapse-accommodation" class="collapse" role="tabpanel" aria-labelledby="heading-accommodation">
        <div class="card-body">
            <div class="form-group row align-items-center mb-2">
                <label for="Accommodation" class="col-md-4 col-form-label"><?= $words->get('HostingStatus') ?></label>
                <div class="col-12 col-md-8 btn-group" data-toggle="buttons">

                    <?php
                    $syshcvol = PVars::getObj('syshcvol');
                    $tt = $syshcvol->Accomodation;
                    $max = count($tt);
                    for ($ii = 0; $ii < $max; $ii++) {
                        $acctext = $words->get("Accomodation_" . $tt[$ii]);
                        ?>

                        <label for="<?= $tt[$ii] ?>"
                               class="btn btn-light <? if ($tt[$ii] == $vars['Accomodation']) echo "active"; ?>">
                            <input type="radio" id="<?= $tt[$ii] ?>" name="Accomodation" value="<?= $tt[$ii] ?>"
                                   class="noradio" <? if ($tt[$ii] == $vars['Accomodation']) echo "checked"; ?>><img
                                    src="images/icons/<?= $tt[$ii]; ?>.png" alt="<?= $acctext; ?>"
                                    title="<?= $acctext; ?>">
                        </label>

                    <? } ?>
                </div>
            </div>
<!--
                <div class="form-group row">
                    <label for="eagerness-duration"
                           class="col-md-4 col-form-label"><? echo $words->get('profile.accommodation.eagerness'); ?></label>
                    <div class="col-12 col-md-8">
                        <input type="text" min="1" max="20" id="eagerness-duration" name="eagerness-duration" class="form-control datepicker"
                               value="<?= $vars['MaxGuest']; ?>">
                    </div>
                </div>
-->
            <div class="form-group row">
                <label for="MaxGuests"
                       class="col-md-4 col-form-label"><? echo $words->get('ProfileNumberOfGuests'); ?></label>
                <div class="col-12 col-md-8">
                    <input type="number" min="1" max="20" name="MaxGuest" class="form-control maxguestsinput"
                           value="<?= $vars['MaxGuest']; ?>">
                </div>
            </div>

            <div class="form-group row">
                <label for="MaxLengthOfStay"
                       class="col-md-4 col-form-label"><?= $words->get('ProfileMaxLenghtOfStay') ?></label>
                <div class="col-12 col-md-8">
                    <textarea name="MaxLenghtOfStay" class="form-control"
                              rows="3"><?= $vars['MaxLenghtOfStay'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="ILiveWith" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileILiveWith') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="ILiveWith" name="ILiveWith" class="form-control" rows="3"><?= $vars['ILiveWith'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="PleaseBring" class="col-md-4 col-form-label">
                    <?= $words->get('ProfilePleaseBring') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="PleaseBring" name="PleaseBring" class="form-control"
                                          rows="3"><?= $vars['PleaseBring'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="OfferGuests" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileOfferGuests') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="OfferGuests" name="OfferGuests" class="form-control"
                                          rows="3"><?= $vars['OfferGuests'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="OfferHosts" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileOfferHosts') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="OfferHosts" name="OfferHosts" class="form-control"
                                          rows="3"><?= $vars['OfferHosts'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">

                <label for="ICanAlsoOffer" class="col-md-4 col-form-label">
                    <?= $words->get('ICanAlsoOffer') ?>
                </label>
                <div class="col-12 col-md-8">
                    <?php
                    $max = count($vars['TabTypicOffer']);
                    for ($ii = 0; $ii < $max; $ii++) {
                        echo '<input type="checkbox" name="check_' . $member->TabTypicOffer[$ii] . '" ';
                        if (strpos($member->TypicOffer, $member->TabTypicOffer[$ii]) !== false)
                            echo 'checked="checked"';
                        echo '><label class="m-0 ml-2" for="check_' . $member->TabTypicOffer[$ii] . '">' . $words->get("TypicOffer_" . $member->TabTypicOffer[$ii]) . '</label><br>';
                    }
                    ?>
                </div>
            </div>

            <div class="form-group row">
                <label for="PublicTransport" class="col-md-4 col-form-label">
                    <?= $words->get('ProfilePublicTransport') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="PublicTransport" name="PublicTransport" class="form-control"
                              rows="3"><?= $vars['PublicTransport'] ?></textarea>
                </div>

            </div>

            <div class="form-group row">
                <label for="ProfileRestrictionsForGuests" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileRestrictionForGuest') ?>
                </label>
                <div class="col-12 col-md-8">
                    <?php
                    $max = count($member->TabRestrictions);
                    for ($ii = 0; $ii < $max; $ii++) {
                        echo '<input type="checkbox" name="check_' . $member->TabRestrictions[$ii] . '" ';
                        if (strpos($member->Restrictions, $member->TabRestrictions[$ii]) !== false)
                            echo 'checked="checked"';
                        echo '><label class="m-0 ml-2" for="check_' . $member->TabRestrictions[$ii] . '">' . $words->get("Restriction_" . $member->TabRestrictions[$ii]) . '</label><br>';
                    }
                    ?>
                </div>
            </div>

            <div class="form-group row">
                <label for="OtherRestrictions" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileHouseRules') ?>
                </label>
                <div class="col-12 col-md-8">
                            <textarea id="OtherRestrictions" name="OtherRestrictions" class="form-control"
                                      rows="3"><?= $vars['OtherRestrictions'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="AdditionalAccomodationInfo" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileAdditionalAccomodationInfo') ?>
                </label>
                <div class="col-12 col-md-8">
                            <textarea id="AdditionalAccomodationInfo" name="AdditionalAccomodationInfo" class="form-control"
                                      rows="3"><?= $vars['AdditionalAccomodationInfo'] ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <input type="submit" class="btn btn-primary float-right m-2" id="submit" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>