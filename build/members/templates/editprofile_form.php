<?php
// Error shortcuts
if (in_array('SignupErrorInvalidBirthDate', $vars['errors'])) {
    $errorBirthDate = true;
}
if (in_array('SignupErrorInvalidFirstName', $vars['errors'])) {
    $errorFirstName = true;
}
if (in_array('SignupErrorInvalidLastName', $vars['errors'])) {
    $errorLastName = true;
}
if (in_array('SignupErrorInvalidStreet', $vars['errors'])) {
    $errorStreet = true;
}
if (in_array('SignupErrorInvalidHouseNumber', $vars['errors'])) {
    $errorHouseNumber = true;
}
if (in_array('SignupErrorInvalidZip', $vars['errors'])) {
    $errorZip = true;
}
if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
    $errorEmail = true;
}

?>
<div class="col-12">
    <?php if ($this->adminedit) : ?>
        <?= $words->get('ProfileStatus') ?>:
        <select id="Status" name="Status">
            <?php echo $statusOptions; ?>
        </select>
    <?php endif; ?>
</div>

<div class="col-12 mt-3">
    <ul class="nav nav-tabs flex-column flex-md-row" id="editProfileTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link btn btn-sm btn-primary editprofile-tab p-1" id="basics-tab" data-toggle="tab" href="#basics" role="tab" aria-controls="basics"
               aria-selected="true">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-sm btn-info editprofile-tab p-1" id="aboutme-tab" data-toggle="tab" href="#aboutme" role="tab" aria-controls="aboutme"
               aria-selected="false"><?= $words->get('ProfileSummary') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-sm btn-info editprofile-tab p-1" id="accommodation-tab" data-toggle="tab" href="#accommodation" role="tab"
               aria-controls="contact" aria-selected="false"><?=$words->get('ProfileAccommodation')?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-sm btn-info editprofile-tab p-1" id="myinterests-tab" data-toggle="tab" href="#myinterests" role="tab"
               aria-controls="contact" aria-selected="false"><?=$words->get('ProfileInterests')?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-sm btn-info editprofile-tab p-1" id="languages-tab" data-toggle="tab" href="#languages" role="tab"
               aria-controls="contact" aria-selected="false">Languages</a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-sm btn-info editprofile-tab p-1" id="contactinfo-tab" data-toggle="tab" href="#contactinfo" role="tab"
               aria-controls="contact" aria-selected="false"><?=$words->get('ContactInfo')?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-sm btn-info editprofile-tab p-1" id="travel-tab" data-toggle="tab" href="#travel" role="tab" aria-controls="contact"
               aria-selected="false">Travel Experience</a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-sm btn-info editprofile-tab p-1" id="family-tab" data-toggle="tab" href="#family" role="tab" aria-controls="contact"
               aria-selected="false"><?=$words->get('MyRelations')?></a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active card" id="basics" role="tabpanel" aria-labelledby="home-tab">
            <div class="row align-items-center p-3">
                <div class="col-3 col-lg-2 h5 text-right">
                    <?= $words->get('SignupUsername') ?>:
                </div>
                <div class="col-9 col-lg-3 h4">
                    <?= $member->Username ?>
                </div>
                <div class="col-12 col-lg-7 small mb-3 alert alert-info">
                    <?= $words->get('subline_username_edit') ?>
                </div>

                <div class="col-3 h5">
                    <img src="members/avatar/<?= $member->Username ?>/100"
                         title="Current picture" alt="Current picture" height="100" width="100"><br>
                    <?= $words->get('ProfilePicture') ?>
                </div>

                <div class="col-9 mt-3 h5">
                    <input id="profile_picture" name="profile_picture" class="btn btn-outline-primary" type="file"/>
                    <label for="profile_picture" class="alert-primary mb-0"><?= $words->get('uploadselectpicture'); ?></label><br/>
                    <span class="small"><?= $words->get('Profile_UploadWarning', sprintf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576)); ?></span>
                </div>

                <div class="w-100 mt-3"></div>

                <? if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>
                <div class="col-3 h5">
                    <?= $words->get('FirstName') ?>
                </div>
                <div class="col-9 h5">
                    <input class="<?php if (isset($errorFirstName)) { ?>error-input-text<?php } ?>" type="text"
                           name="FirstName"
                           value="<?php echo htmlentities($vars['FirstName'], ENT_COMPAT, 'UTF-8'); ?>"/>

                    <input type="checkbox" value="Yes" class="pt-1" name="IsHidden_FirstName"
                        <?php if ($vars['IsHidden_FirstName'] === 'Yes')
                            echo 'checked="checked"';
                        ?> />
                    <?= $words->get('hidden') ?>

                    <?php if (isset($errorFirstName)) { ?>
                        <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidFirstName') ?></div>
                    <?php } ?>

                </div>

                <div class="col-3 h5">
                    <?= $words->get('SecondName') ?>
                </div>
                <div class="col-9 h5">
                    <input type="text" name="SecondName"
                           value="<?php echo htmlentities($vars['SecondName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    <input type="checkbox" value="Yes" name="IsHidden_SecondName"
                        <?php if ($vars['IsHidden_SecondName'] === 'Yes')
                            echo 'checked="checked"';
                        ?> />
                    <?= $words->get('hidden') ?>
                </div>

                <div class="col-3 h5">
                    <?= $words->get('LastName') ?>
                </div>
                <div class="col-9 h5">
                    <input class="<?php if (isset($errorLastName)) { ?>error-input-text<?php } ?>" type="text"
                           name="LastName"
                           value="<?php echo htmlentities($vars['LastName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    <input type="checkbox" value="Yes" name="IsHidden_LastName"
                        <?php if ($vars['IsHidden_LastName'] === 'Yes')
                            echo 'checked="checked"';
                        ?> />
                    <?= $words->get('hidden') ?>
                    <?php if (isset($errorLastName)) { ?>
                        <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidLastName') ?></div>
                    <?php } ?>
                </div>
                <div class="w-100 mt-3">
                </div>

                <div class="col-3 h5">
                    <?= $words->get('SignupEmail') ?>
                </div>
                <div class="col-9 h5">
                    <input class="<?php if (isset($errorEmail)) { ?>error-input-text<?php } ?>" type="text"
                           size="25"
                           name="Email" value="<?= str_replace('%40', '@', $vars['Email']) ?>"/>
                    <?= $words->get('EmailIsAlwayHidden') ?>
                    <?php if (isset($errorEmail)) { ?>
                        <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidEmail') ?></div>
                    <?php } ?>
                </div>

                <? } ?>

                <div class="w-100 mt-3"></div>

                <div class="col-3 mt-2 h5">
                    <?= $words->get('SignupBirthDate') ?><br>
                </div>
                <div class="col-4 mt-4 h6">
                    <select id="BirthYear" name="BirthYear">
                        <option value="0"><?php echo $words->getSilent('SignupBirthYear'); ?></option>
                        <?php echo $birthYearOptions; ?>
                    </select>
                    <select name="BirthMonth">
                        <option value="0"><?php echo $words->getSilent('SignupBirthMonth'); ?></option>
                        <?php for ($i = 1; $i <= 12; $i++) { ?>
                            <option value="<?php echo $i; ?>"<?php
                            if (isset($vars['BirthMonth']) && $vars['BirthMonth'] == $i) {
                                echo ' selected="selected"';
                            }
                            ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                    <select name="BirthDay">
                        <option value="0"><?php echo $words->getSilent('SignupBirthDay'); ?></option>
                        <?php for ($i = 1; $i <= 31; $i++) { ?>
                            <option value="<?php echo $i; ?>"<?php
                            if (isset($vars['BirthDay']) && $vars['BirthDay'] == $i) {
                                echo ' selected="selected"';
                            }
                            ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                    <span class="small m-0"><?= $words->get('EmailIsAlwayHidden') ?></span>

                    <?php echo $words->flushBuffer(); ?>
                    <?php
                    if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                        echo '<div class="error">' . $words->get('SignupErrorBirthDate') . '</div>';
                    }
                    if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                        echo '<div class="error">' . $words->getFormatted('SignupErrorBirthDateToLow', SignupModel::YOUNGEST_MEMBER) . '</div>';
                    }
                    ?>

                </div>
                <div class="col-5 mt-3">
                    <input class="mt-3" name="HideBirthDate" value="Yes"
                           type="checkbox" <?= ($vars['HideBirthDate'] == 'Yes') ? 'checked="checked"' : '' ?> />
                    <?= $words->get("Hidden"); ?>
                    <p class="small m-0"><?= $words->get('HiddenAgeInfo'); ?></p>
                </div>

                <div class="col-3 mt-3 h5">
                    <?= $words->get('Gender'); ?>
                </div>
                <div class="col-4 mt-3">
                    <div class="btn-group" data-toggle="buttons">
                        <label for='genderF'
                               class="btn btn-outline-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'female') ? 'active' : '' ?>">
                            <input type="radio" id="genderF" name="gender"
                                   value="female" class="noradio" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'female') ? ' checked="checked"' : ''); ?>/><?= $words->get('female'); ?>
                        </label>
                        <label for='genderM'
                               class="btn btn-outline-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'male') ? 'active' : '' ?>"><input
                                    type="radio" id='genderM' name="gender"
                                    value="male" class="noradio" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'male') ? ' checked="checked"' : ''); ?>/> <?= $words->get('male'); ?>
                        </label>
                        <label for='genderX'
                               class="btn btn-outline-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'other') ? 'active' : '' ?>"><input
                                    type="radio" id='genderX' name="gender"
                                    value="other" class="noradio" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'other') ? ' checked="checked"' : ''); ?>/> <?= $words->get('GenderOther'); ?>
                        </label>
                    </div>
                </div>
                <div class="col-5 mt-3"><input name="HideGender" value="Yes" type="checkbox"
                                          id='HideGender' <?= ((isset($vars['HideGender']) && $vars['HideGender'] == "Yes") ? ' checked="checked"' : ''); ?>/><label
                            for="HideGender" class="ml-1"><?= $words->get("Hidden"); ?></label>
                </div>

                <?php
                if (in_array('SignupErrorInvalidGender', $vars['errors'])) {
                    echo '<div class="error">' . $words->get('SignupErrorInvalidGender') . '</div>';
                }
                ?>

                <div class="col-3 mt-3 h5"><?= $words->get('Location') ?></div>
                <div class="col-9 mt-3">
                        <i class="fa fa-3x fa-map-marker float-left"></i>
                        <div class="float-left ml-2"><span class="font-weight-bold"><?= $member->city ?></span>
                        <br>
                        <?= $member->region ?>, <?= $member->country ?>
                        </div>
                    <a href="setlocation"
                       class="btn btn-outline-primary float-left ml-5"><?= $words->get('UpdateMyLocation') ?></a>
                </div>
            </div>
        </div>
        <div class="tab-pane fade card" id="aboutme" role="tabpanel" aria-labelledby="aboutme-tab">
            <div class="row p-3">
                <div class="col-2 h5"><?= $words->get('ProfileOccupation') ?></div>
                <div class="col-10 h5"><input class="w-100" name="Occupation"
                                          value="<?php echo htmlentities($vars['Occupation'], ENT_COMPAT, 'UTF-8'); ?>"/>
                </div>

                <div class="col-2 h5"><?= $words->get('ProfileSummary') ?></div>
                <div class="col-10 h5"><textarea name="ProfileSummary" id="ProfileSummary" class="w-100"
                                             rows="6"><?php echo htmlentities($vars['ProfileSummary'], ENT_COMPAT, 'UTF-8'); ?></textarea>
                </div>

            </div>
        </div>
        <div class="tab-pane fade card" id="accommodation" role="tabpanel" aria-labelledby="accommodation-tab">

            <div class="row p-3">
                <div class="col-4 h5 pt-2">
                    <?= $words->get('HostingStatus') ?>
                </div>

                <div class="col-8 btn-group" data-toggle="buttons">

                    <?php
                    $syshcvol = PVars::getObj('syshcvol');
                    $tt = $syshcvol->Accomodation;
                    $max = count($tt);
                    for ($ii = 0; $ii < $max; $ii++) {
                    $acctext = $words->get("Accomodation_" . $tt[$ii]);
                        ?>

                    <label for="<?= $tt[$ii] ?>" class="btn btn-light <? if ($tt[$ii] == $vars['Accomodation']) echo "active"; ?>">
                        <input type="radio" id="<?= $tt[$ii] ?>" name="Accomodation" value="<?= $tt[$ii] ?>" class="noradio" <? if ($tt[$ii] == $vars['Accomodation']) echo "checked"; ?>><img src="images/icons/<?= $tt[$ii]; ?>.png" alt="<?= $acctext; ?>" title="<?= $acctext; ?>">
                    </label>

                    <? } ?>
                </div>

                <div class="col-4 h5">
                    <? echo $words->get('ProfileNumberOfGuests'); ?>
                </div>
                <div class="col-8">
                    <input id="rangevalue" name="MaxGuest" class="small maxguestsinput" value="<?= $vars['MaxGuest']; ?>">
                    <input type="range" min="0" max="20" value="<?= $vars['MaxGuest'];?>" step="1" onchange="rangevalue.value=value" />
                </div>

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfileMaxLenghtOfStay') ?>
                </div>
                <div class="col-8 mt-3 h5">
<textarea name="MaxLenghtOfStay" class="w-100"
          rows="2"><?= $vars['MaxLenghtOfStay'] ?></textarea>
                </div>

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfileILiveWith') ?>
                </div>
                <div class="col-8 mt-3 h5">
                    <textarea name="ILiveWith" class="w-100" rows="2"><?= $vars['ILiveWith'] ?></textarea>
                </div>

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfilePleaseBring') ?>
                </div>
                <div class="col-8 mt-3 h5">
                        <textarea name="PleaseBring" class="w-100"
                                  rows="2"><?= $vars['PleaseBring'] ?></textarea>
                </div>

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfileOfferGuests') ?>
                </div>
                <div class="col-8 mt-3 h5">
                        <textarea name="OfferGuests" class="w-100"
                                  rows="2"><?= $vars['OfferGuests'] ?></textarea>
                </div>

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfileOfferHosts') ?>
                </div>
                <div class="col-8 mt-3 h5">
                        <textarea name="OfferHosts" class="w-100"
                                  rows="2"><?= $vars['OfferHosts'] ?></textarea>
                </div>

                <div class="col-4 mt-3 h4">
                    <?= $words->get('ICanAlsoOffer') ?>
                </div>
                <div class="col-8 mt-3">
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

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfilePublicTransport') ?>
                </div>
                <div class="col-8 mt-3 h5">
<textarea name="PublicTransport" class="w-100"
          rows="2"><?= $vars['PublicTransport'] ?></textarea>
                </div>

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfileRestrictionForGuest') ?>
                </div>
                <div class="col-8 mt-3">
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

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfileHouseRules') ?>
                </div>
                <div class="col-8 mt-3">
<textarea name="OtherRestrictions" class="w-100"
          rows="2"><?= $vars['OtherRestrictions'] ?></textarea>
                </div>

                <div class="col-4 mt-3 h5">
                    <?= $words->get('ProfileAdditionalAccomodationInfo') ?>
                </div>
                <div class="col-8 mt-3 h5">
<textarea name="AdditionalAccomodationInfo" class="w-100"
          rows="2"><?= $vars['AdditionalAccomodationInfo'] ?></textarea>
                </div>
            </div>
        </div>
        <div class="tab-pane fade card" id="myinterests" role="tabpanel" aria-labelledby="myinterests-tab">
            <div class="row p-3">
                <div class="col-3 mt-3 h5">
                    <?= $words->get('ProfileHobbies') ?>
                </div>
                <div class="col-9 mt-3 h5">
                    <textarea name="Hobbies" class="w-100" rows="4"><?= $vars['Hobbies'] ?></textarea>
                </div>

                <div class="col-3 mt-3 h5">
                    <?= $words->get('ProfileBooks') ?>
                </div>
                <div class="col-9 mt-3 h5">
                    <textarea name="Books" class="w-100" rows="4"><?= $vars['Books'] ?></textarea>
                </div>

                <div class="col-3 mt-3 h5">
                    <?= $words->get('ProfileMusic') ?>
                </div>
                <div class="col-9 mt-3 h5">
                    <textarea name="Music" class="w-100" rows="4"><?= $vars['Music'] ?></textarea>
                </div>

                <div class="col-3 mt-3 h5">
                    <?= $words->get('ProfileMovies') ?>
                </div>
                <div class="col-9 mt-3 h5">
                    <textarea name="Movies" class="w-100" rows="4"><?= $vars['Movies'] ?></textarea>
                </div>

                <div class="col-3 mt-3 h5">
                    <?= $words->get('ProfileOrganizations') ?>
                </div>
                <div class="col-9 mt-3 h5">
                        <textarea name="Organizations" class="w-100"
                                  rows="4"><?= $vars['Organizations'] ?></textarea>
                </div>

            </div>

        </div>
        <div class="tab-pane fade card" id="languages" role="tabpanel" aria-labelledby="languages-tab">
            <div class="row p-3">
                <div class="col-12 my-2 h5"><?= $words->get('ProfileLanguagesSpoken') ?></div>
                <?php
                $lang_ids = array();
                for ($ii = 0; $ii < count($vars['languages_selected']); $ii++) {
                    $lang_ids[] = $vars['languages_selected'][$ii]->IdLanguage; ?>

                    <div class="col-1">
                        <a href="#" class="btn btn-outline-danger p-1 px-2 remove_lang"><i class="fa fa-trash"></i><span class="sr-only"><?= $words->get('RemoveLanguage') ?></span></a>
                    </div>

                    <div class="col-4">
                        <input type="hidden" name="memberslanguages[]"
                               value="<?= $vars['languages_selected'][$ii]->IdLanguage ?>">
                        <input type="text" disabled value="<?= $vars['languages_selected'][$ii]->Name ?>"
                               class="w-100">

                    </div>
                    <div class="col-7 mt-lg-1">
                        <select class="mll" name="memberslanguageslevel[]">
                            <?
                            for ($jj = 0; $jj < count($vars['language_levels']); $jj++) {
                                $selected = $vars['language_levels'][$jj] == $vars['languages_selected'][$ii]->Level ? ' selected="selected"' : '';
                                ?>
                                <option value='<?= $vars['language_levels'][$jj] ?>'<?= $selected ?>><?= $words->getSilent("LanguageLevel_" . $vars['language_levels'][$jj]) ?></option>
                            <? } ?>

                        </select>
                        <?= $words->flushBuffer() ?>
                    </div>
                    <?
                } ?>

                <div class="col-4" id="lang1">
                    <select class='lang_selector' name="memberslanguages[]">
                        <option selected="selected">-<?= $words->get("ChooseNewLanguage") ?>-</option>
                        <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                            <?
                            for ($jj = 0; $jj < count($vars['languages_all_spoken']); $jj++) {
                                if (in_array($vars['languages_all_spoken'][$jj]->id, $lang_ids)) {
                                    continue;
                                }
                                ?>
                                <option value="<?= $vars['languages_all_spoken'][$jj]->id ?>"><?= $vars['languages_all_spoken'][$jj]->TranslatedName ?>
                                    (<?= $vars['languages_all_spoken'][$jj]->Name ?>)
                                </option>
                                <?
                            }
                            ?>
                        </optgroup>
                        <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                            <?
                            for ($jj = 0; $jj < count($vars['languages_all_signed']); $jj++) {
                                if (in_array($vars['languages_all_signed'][$jj]->id, $lang_ids)) {
                                    continue;
                                }
                                ?>
                                <option value="<?= $vars['languages_all_signed'][$jj]->id ?>"><?= $vars['languages_all_signed'][$jj]->TranslatedName ?></option>
                                <?
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col-8">
                    <select class="mll" name="memberslanguageslevel[]">
                        <?
                        for ($jj = 0; $jj < count($vars['language_levels']); $jj++) {
                            ?>
                            <option value="<?= $vars['language_levels'][$jj] ?>"><?= $words->get("LanguageLevel_" . $vars['language_levels'][$jj]) ?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <input type="button" id="langbutton" class="btn btn-outline-primary mt-1" name="addlang"
                           value="<?= $words->getSilent('AddLanguage') ?>"/>
                    <?= $words->flushBuffer() ?>
                </div>
            </div>
        </div>
        <div class="tab-pane fade card" id="contactinfo" role="tabpanel" aria-labelledby="contactinfo-tab">
            <div class="row justify-content-start p-3">
                <? if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>

                    <div class="col-3 h5">
                        <?= $words->get('Street') ?>
                    </div>
                    <div class="col-9 h5">
                        <input class="<?php if (isset($errorStreet)) { ?>error-input-text<?php } ?>" type="text"
                               name="Street"
                               id="Street"
                               value="<?php echo htmlentities($vars['Street'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_Address"
                            <?php if ($vars['IsHidden_Address'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                        <?php if (isset($errorStreet)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidStreet') ?></div>
                        <?php } ?>
                    </div>

                    <div class="col-3 h5">
                        <?= $words->get('HouseNumber') ?>
                    </div>
                    <div class="col-9 h5">
                        <input class="short<?php if (isset($errorHouseNumber)) { ?> error-input-text<?php } ?>"
                               type="text"
                               name="HouseNumber" id="HouseNumber"
                               value="<?php echo htmlentities($vars['HouseNumber'], ENT_COMPAT, 'UTF-8'); ?>"
                               size="6"/>
                        <?php if (isset($errorHouseNumber)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidHouseNumber') ?></div>
                        <?php } ?>
                    </div>


                    <div class="col-3 h5">
                        <?= $words->get('Post code') ?>
                    </div>
                    <div class="col-9 h5">
                        <input class="short <?php if (isset($errorZip)) { ?> error-input-text<?php } ?>" type="text"
                               name="Zip"
                               value="<?php echo htmlentities($vars['Zip'], ENT_COMPAT, 'UTF-8'); ?>" size="6"/>
                        <input type="checkbox" value="Yes" name="IsHidden_Zip"
                            <?php if ($vars['IsHidden_Zip'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                        <?php if (isset($errorZip)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidZip') ?></div>
                        <?php } ?>
                    </div>

                    <div class="w-100">
                    </div>

                    <div class="col-3 h5">
                        <?= $words->get('ProfileHomePhoneNumber') ?>
                    </div>
                    <div class="col-9 h5">
                        <input type="text" size="15" name="HomePhoneNumber"
                               value="<?php echo htmlentities($vars['HomePhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_HomePhoneNumber"
                            <?php if ($vars['IsHidden_HomePhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-3 h5">
                        <?= $words->get('ProfileCellPhoneNumber') ?>
                    </div>
                    <div class="col-9 h5">
                        <input type="text" size="15" name="CellPhoneNumber"
                               value="<?php echo htmlentities($vars['CellPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_CellPhoneNumber"
                            <?php if ($vars['IsHidden_CellPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-3 h5">
                        <?= $words->get('ProfileWorkPhoneNumber') ?>
                    </div>
                    <div class="col-9 h5">
                        <input type="text" size="15" name="WorkPhoneNumber"
                               value="<?php echo htmlentities($vars['WorkPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_WorkPhoneNumber"
                            <?php if ($vars['IsHidden_WorkPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="w-100 mt-3">
                    </div>

                    <div class="col-3 h5">
                        <?= $words->get('Website') ?>
                    </div>
                    <div class="col-9 h5">
                        <input type="text" class="w-100" name="WebSite"
                               value="<?php echo htmlentities($vars['WebSite'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>

                    <div class="w-100 mt-3 pl-3 h4">
                        Social media
                    </div>

                    <?php
                    if (isset($vars['messengers'])) {
                        foreach ($vars['messengers'] as $me) {
                            $val = 'chat_' . $me['network_raw'];
                            ?>

                            <div class="col-3 h5">
                                <i class="fa fa-<?= $me["image"]; ?> pr-2" alt="<?= $me["image"]; ?>" title="<?= $me["image"]; ?>"></i>
                                <?= $me["network"] ?>
                            </div>
                            <div class="col-9 h5">
                                <input type="text" size="15" name="<?= $val ?>"
                                       value="<?php echo htmlentities($me["address"], ENT_COMPAT, 'UTF-8'); ?>"/>
                            </div>
                            <?php
                        }
                    }
                }
                ?>
            </div>
        </div>
        <div class="tab-pane fade card" id="travel" role="tabpanel" aria-labelledby="travel-tab">
            <div class="row p-3">
                <div class="col-3 h5">
                    <?= $words->get('ProfilePastTrips') ?>
                </div>
                <div class="col-9 h5">
                    <textarea name="PastTrips" class="w-100" rows="3"><?= $vars['PastTrips'] ?></textarea>
                </div>

                <div class="col-3 h5">
                    <?= $words->get('ProfilePlannedTrips') ?>
                </div>
                <div class="col-9 h5">
                        <textarea name="PlannedTrips" class="w-100"
                                  rows="3"><?= $vars['PlannedTrips'] ?></textarea>
                </div>

            </div>
        </div>

        <div class="tab-pane fade card" id="family" role="tabpanel" aria-labelledby="family-tab">
            <div class="row p-3">

                <?php
                $Relations = $vars['Relations'];
                foreach ($Relations as $Relation) {
                    $comment = $words->mInTrad($Relation->Comment, $profile_language);
                    if (is_numeric($comment)) {
                        $comment = '';
                    }
                    ?>

                    <div class="col-2">
                        <a href="members/<?= $Relation->Username ?>"><img src="members/avatar/<?= $Relation->Username ?>/50" height="50" width="50"
                             alt="Profile"/></a><br>
                        <span class="small
                <?php
                        if ($Relation->Confirmed == 'Yes') {
                            echo ' font-weight-bold';
                        }
                        ?>
                "><a href="members/<?= $Relation->Username ?>"><?= $Relation->Username ?></a></span>
                    </div>
                    <div class="col-6 h5">
                            <textarea cols="40" rows="2"
                                      name="RelationComment_<?= $Relation->id ?>"><?= $comment ?></textarea>
                    </div>
                    <div class="col-4">
                        <a href="/members/<?php echo $member->Username; ?>/relations/delete/<?php echo $Relation->id; ?>?redirect=editmyprofile#!specialrelations"
                           class="btn btn-outline-secondary p-1 py-0"
                           onclick="return confirm('<?php echo $words->getSilent('Relation_delete_confirmation'); ?>');"><?php echo $words->getFormatted("delrelation", $Relation->Username); ?><?php echo $words->flushBuffer(); ?></a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

</div>

<div class="col-12">
    <input type="submit" class="btn btn-primary editbutton text-left ml-1" id="submit" name="submit"
           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
</div>

<script type="text/javascript">//<!--
    jQuery.noConflict();
    jQuery(".lang_selector").select2({
        dropdownAutoWidth: true,
        width: 'element'
    });
    jQuery(".mll").select2({
        dropdownAutoWidth: true,
        width: 'element',
        minimumResultsForSearch: -1
    });
    //-->
</script>
