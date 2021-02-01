<?php
$hostingInterest = [
    $words->get('Please set your hosting interest'),
    $words->get('Very low'),
    $words->get('low'),
    $words->get('lower'),
    $words->get('low to medium'),
    $words->get('medium'),
    $words->get('medium to high'),
    $words->get('high'),
    $words->get('higher'),
    $words->get('very high'),
    $words->get('can\'t wait')
]; ?>
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
                <div class="btn-group col-md-8 mt-2" data-toggle="buttons">
                    <label for="neverask"
                           class="btn btn-light <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'neverask') { echo 'active'; } ?>">
                        <input type="radio" id="neverask" name="Accomodation" value="neverask"
                            <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'neverask') { echo ' checked="checked"'; } ?>
                               class="noradio" >
                        <div class="d-block-inline"><img
                                src="images/icons/neverask.png" alt=""
                                title=""><br><small>
                                <?php echo $words->get('Accomodation_neverask'); ?></small>
                        </div>
                    </label>
                    <label for="anytime"
                           class="btn btn-light <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'anytime') { echo 'active'; } ?>">
                        <input type="radio" id="anytime" name="Accomodation" value="anytime"
                            <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'anytime') { echo ' checked="checked"'; } ?>
                               class="noradio" ><div class="d-block-inline"><img
                                src="images/icons/anytime.png" alt=""
                                title=""><br><small>
                                <?php echo $words->get('Accomodation_anytime'); ?></small>
                        </div>
                    </label>
                    <div class="invalid-feedback">Select one of the above.</div>
                    <?php if (in_array('SignupErrorProvideAccommodation', $vars['errors'])) {
                        echo '<div class="error">'.$words->get('SignupErrorProvideAccommodation').'</div>';
                    }
                    ?>
                </div>
            </div>
            <div id="hi_block" class="form-group row mb-2 <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'neverask') { echo ' d-none'; } ?>">
                <label for="hosting_interest" class="col-md-4 col-form-label">Hosting Interest</label>
                <div class="col-12 col-md-8">
                <input
                    type="range"
                    class="o-input <?php if (in_array('SignupErrorProvideHostingInterest', $vars['errors'])) {
                        echo 'is-invalid';
                    } else {
                        echo 'is-valid';
                    }
                    ?>"
                    id="hosting_interest"
                    name="hosting_interest"
                    min="<?php echo (isset($vars['hosting_interest'])) ? 1 : 0; ?>"
                    max="10"
                    step="-1"
                    value="<?php echo (isset($vars['hosting_interest'])) ? $vars['hosting_interest'] : 0; ?>"
                    required="required"
                    data-orientation="horizontal"
                >
                <div class="range text-center">
                    <p class="rangeslider__value-output"><?php echo (isset($vars['hosting_interest'])) ? $hostingInterest[$vars['hosting_interest']] : $hostingInterest[0]; ?></p>
                </div>
                <div class="invalid-feedback"><?php echo $words->get('SignupErrorProvideHostingInterest'); ?></div>
                </div>
            </div>
            <div class="form-group row">
                <label for="MaxGuests"
                       class="col-md-4 col-form-label"><?php echo $words->get('ProfileNumberOfGuests'); ?></label>
                <div class="col-12 col-md-8">
                    <input type="number" min="1" max="20" name="MaxGuest" class="o-input"
                           value="<?= $vars['MaxGuest']; ?>">
                </div>
            </div>

            <div class="form-group row">
                <label for="MaxLengthOfStay"
                       class="col-md-4 col-form-label"><?= $words->get('ProfileMaxLenghtOfStay') ?></label>
                <div class="col-12 col-md-8">
                    <textarea name="MaxLenghtOfStay" class="o-input"
                              rows="3"><?= $vars['MaxLenghtOfStay'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="ILiveWith" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileILiveWith') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="ILiveWith" name="ILiveWith" class="o-input" rows="3"><?= $vars['ILiveWith'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="PleaseBring" class="col-md-4 col-form-label">
                    <?= $words->get('ProfilePleaseBring') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="PleaseBring" name="PleaseBring" class="o-input"
                                          rows="3"><?= $vars['PleaseBring'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="OfferGuests" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileOfferGuests') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="OfferGuests" name="OfferGuests" class="o-input"
                                          rows="3"><?= $vars['OfferGuests'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="OfferHosts" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileOfferHosts') ?>
                </label>
                <div class="col-12 col-md-8">
                    <textarea id="OfferHosts" name="OfferHosts" class="o-input"
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
                    <textarea id="PublicTransport" name="PublicTransport" class="o-input"
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
                            <textarea id="OtherRestrictions" name="OtherRestrictions" class="o-input"
                                      rows="3"><?= $vars['OtherRestrictions'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="AdditionalAccomodationInfo" class="col-md-4 col-form-label">
                    <?= $words->get('ProfileAdditionalAccomodationInfo') ?>
                </label>
                <div class="col-12 col-md-8">
                            <textarea id="AdditionalAccomodationInfo" name="AdditionalAccomodationInfo" class="o-input"
                                      rows="3"><?= $vars['AdditionalAccomodationInfo'] ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <input type="submit" class="btn btn-primary float-right m-2" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
