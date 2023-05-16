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
<div class="card">
    <div class="card-header" id="heading-accommodation">
        <a data-toggle="collapse" href="#collapse-accommodation" aria-expanded="false"
           aria-controls="collapse-accommodation" class="mb-0 d-block collapsed">
            <?= $words->get('ProfileAccommodation') ?>
        </a>
    </div>
    <div id="collapse-accommodation" class="collapse" data-parent="#editProfile" aria-labelledby="heading-accommodation">
        <div class="card-body">
            <div class="o-form-group row mb-2 align-items-center mb-2">
            <label for="Accommodation" class="col-md-4 col-form-label"><?= $words->get('HostingStatus') ?></label>
            <div class="btn-group col-md-8 mt-2" data-toggle="buttons">
                <label for="neverask"
                       class="btn btn-light <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'neverask') {
                           echo 'active';
                       } ?>">
                    <input type="radio" id="neverask" name="Accomodation" value="neverask"
                        <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'neverask') {
                            echo ' checked="checked"';
                        } ?>
                           class="noradio">
                    <div class="d-block-inline"><img
                            src="images/icons/neverask.png" alt=""
                            title=""><br><small>
                            <?php echo $words->get('Accomodation_neverask'); ?></small>
                    </div>
                </label>
                <label for="anytime"
                       class="btn btn-light <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'anytime') {
                           echo 'active';
                       } ?>">
                    <input type="radio" id="anytime" name="Accomodation" value="anytime"
                        <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'anytime') {
                            echo ' checked="checked"';
                        } ?>
                           class="noradio">
                    <div class="d-block-inline"><img
                            src="images/icons/anytime.png" alt=""
                            title=""><br><small>
                            <?php echo $words->get('Accomodation_anytime'); ?></small>
                    </div>
                </label>
                <div class="invalid-feedback">Select one of the above.</div>
                <?php if (in_array('SignupErrorProvideAccommodation', $vars['errors'])) {
                    echo '<div class="error">' . $words->get('SignupErrorProvideAccommodation') . '</div>';
                }
                ?>
            </div>
        </div>
        <div id="hi_block"
             class="form-group row mb-2 <?php if (isset($vars['Accomodation']) && $vars['Accomodation'] == 'neverask') {
                 echo ' d-none';
             } ?>">
            <label for="hosting_interest"
                   class="col-md-4 col-form-label"><?php echo $words->get('hosting.interest'); ?></label>
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

            <div class="o-form-group row mb-2">
            <label for="MaxLenghtOfStay"
                   class="col-md-4 col-form-label"><?= $words->get('ProfileMaxLenghtOfStay') ?></label>
            <div class="col-12 col-md-8">
                    <textarea id="MaxLenghtOfStay" name="MaxLenghtOfStay" class="o-input"
                              rows="3"><?= $vars['MaxLenghtOfStay'] ?></textarea>
            </div>
        </div>

            <div class="o-form-group row mb-2">
            <label for="ILiveWith" class="col-md-4 col-form-label">
                <?= $words->get('ProfileILiveWith') ?>
            </label>
            <div class="col-12 col-md-8">
                    <textarea id="ILiveWith" name="ILiveWith" class="o-input" rows="3"><?= $vars['ILiveWith'] ?></textarea>
            </div>
        </div>

            <div class="o-form-group row mb-2">
            <label for="PleaseBring" class="col-md-4 col-form-label">
                <?= $words->get('ProfilePleaseBring') ?>
            </label>
            <div class="col-12 col-md-8">
                    <textarea id="PleaseBring" name="PleaseBring" class="o-input"
                              rows="3"><?= $vars['PleaseBring'] ?></textarea>
            </div>
        </div>

            <div class="o-form-group row mb-2">
            <label for="OfferGuests" class="col-md-4 col-form-label">
                <?= $words->get('ProfileOfferGuests') ?>
            </label>
            <div class="col-12 col-md-8">
                    <textarea id="OfferGuests" name="OfferGuests" class="o-input"
                              rows="3"><?= $vars['OfferGuests'] ?></textarea>
            </div>
        </div>

            <div class="o-form-group row mb-2">
            <label for="OfferHosts" class="col-md-4 col-form-label">
                <?= $words->get('ProfileOfferHosts') ?>
            </label>
            <div class="col-12 col-md-8">
                    <textarea id="OfferHosts" name="OfferHosts" class="o-input"
                              rows="3"><?= $vars['OfferHosts'] ?></textarea>
            </div>
        </div>

            <div class="o-form-group row mb-2">

            <label for="ICanAlsoOffer" class="col-md-4 col-form-label">
                <?= $words->get('ICanAlsoOffer') ?>
            </label>
            <div class="col-12 col-md-8">
                <?php
                $max = count($vars['TabTypicOffer']);
                for ($ii = 0; $ii < $max; $ii++) {
                    echo '<div class="o-checkbox">';
                    echo '<input type="checkbox" class="o-checkbox__input" name="check_' . $member->TabTypicOffer[$ii] . '" ';
                    if (strpos($member->TypicOffer, $member->TabTypicOffer[$ii]) !== false)
                        echo 'checked="checked"';
                    echo '><label class="m-0 ml-2" class="o-checkbox__label" for="check_' . $member->TabTypicOffer[$ii] . '">' . $words->get("TypicOffer_" . $member->TabTypicOffer[$ii]) . '</label></div>';
                }
                ?>
            </div>
        </div>

            <div class="o-form-group row mb-2">
            <label for="PublicTransport" class="col-md-4 col-form-label">
                <?= $words->get('ProfilePublicTransport') ?>
            </label>
            <div class="col-12 col-md-8">
                    <textarea id="PublicTransport" name="PublicTransport" class="o-input"
                              rows="3"><?= $vars['PublicTransport'] ?></textarea>
            </div>

        </div>

            <div class="o-form-group row mb-2">
            <label for="ProfileRestrictionsForGuests" class="col-md-4 col-form-label">
                <?= $words->get('ProfileRestrictionForGuest') ?>
            </label>
            <div class="col-12 col-md-8">
                <?php
                $max = count($member->TabRestrictions);
                for ($ii = 0; $ii < $max; $ii++) {
                    echo '<div class="o-checkbox">';
                    echo '<input type="checkbox" class="o-checkbox__input" name="check_' . $member->TabRestrictions[$ii] . '" ';
                    if (strpos($member->Restrictions, $member->TabRestrictions[$ii]) !== false)
                        echo 'checked="checked"';
                    echo '><label class="m-0 ml-2 o-checkbox__label" for="check_' . $member->TabRestrictions[$ii] . '">' . $words->get("Restriction_" . $member->TabRestrictions[$ii]) . '</label>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

            <div class="o-form-group row mb-2">
            <label for="OtherRestrictions" class="col-md-4 col-form-label">
                <?= $words->get('ProfileHouseRules') ?>
            </label>
            <div class="col-12 col-md-8">
                            <textarea id="OtherRestrictions" name="OtherRestrictions" class="o-input"
                                      rows="3"><?= $vars['OtherRestrictions'] ?></textarea>
            </div>
        </div>

            <div class="o-form-group row mb-2">
            <label for="AdditionalAccomodationInfo" class="col-md-4 col-form-label">
                <?= $words->get('ProfileAdditionalAccomodationInfo') ?>
            </label>
            <div class="col-12 col-md-8">
                            <textarea id="AdditionalAccomodationInfo" name="AdditionalAccomodationInfo" class="o-input"
                                      class="o-input"
                                      rows="3"><?= $vars['AdditionalAccomodationInfo'] ?></textarea>
            </div>
        </div>
        </div>
    </div>
</div>
