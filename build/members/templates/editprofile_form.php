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

<div class="col-12">
    <div id="editProfile" data-children=".item">
        <div class="item">
            <a data-toggle="collapse" data-parent="#editProfile" href="#editProfile1" aria-expanded="false"
               aria-controls="editProfile1" class="btn btn-outline-primary editbutton text-left">
                <i class="fa fa-angle-down"></i> Basics
            </a>
            <div id="editProfile1" class="collapse editprofilebox" role="tabpanel">
                <div class="row my-2">
                    <div class="col-3 pt-2">
                        <?= $words->get('SignupUsername') ?>
                    </div>
                    <div class="col-9 h4">
                        <?= $member->Username ?>
                    </div>
                    <div class="col-12 small mb-3 alert alert-info">
                        <?= $words->get('subline_username_edit') ?>
                    </div>


                    <div class="col-3">
                        <img src="members/avatar/<?= $member->Username ?>/50"
                             title="Current picture" alt="Current picture" height="50" width="50"><br>
                        <?= $words->get('ProfilePicture') ?>
                    </div>

                    <div class="col-9">
                        <input id="profile_picture" name="profile_picture" type="file"/>
                        <label for="profile_picture"><?= $words->get('uploadselectpicture'); ?></label><br/>
                        <span class="small"><?= $words->get('Profile_UploadWarning', sprintf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576)); ?></span>
                    </div>


                    <div class="col-3">
                        <?= $words->get('SignupBirthDate') ?><br>
                        <span class="small"><?= $words->get('EmailIsAlwayHidden') ?></span>
                    </div>
                    <div class="col-5">
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
                    <div class="col-4">
                        <input name="HideBirthDate" value="Yes"
                               type="checkbox" <?= ($vars['HideBirthDate'] == 'Yes') ? 'checked="checked"' : '' ?> />
                        Hide age
                    </div>
                    <div class="col-3 offset-8 small">
                        <?= $words->get('HiddenAgeInfo'); ?>
                    </div>

                    <div class="col-3 mt-3 pt-2">
                        <?= $words->get('Gender'); ?>

                    </div>
                    <div class="col-9 mt-3">
                        <div class="btn-group" data-toggle="buttons">
                            <label for='genderF'
                                   class="btn btn-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'female') ? 'active' : '' ?>">
                                <input type="radio" id="genderF" name="gender"
                                       value="female" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'female') ? ' checked="checked"' : ''); ?>/><?= $words->get('female'); ?>
                            </label>
                            <label for='genderM'
                                   class="btn btn-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'male') ? 'active' : '' ?>"><input
                                        type="radio" id='genderM' name="gender"
                                        value="male" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'male') ? ' checked="checked"' : ''); ?>/> <?= $words->get('male'); ?>
                            </label>
                            <label for='genderX'
                                   class="btn btn-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'other') ? 'active' : '' ?>"><input
                                        type="radio" id='genderX' name="gender"
                                        value="other" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'other') ? ' checked="checked"' : ''); ?>/> <?= $words->get('GenderOther'); ?>
                            </label>
                        </div>
                        <input name="HideGender" value="Yes" type="checkbox"
                               id='HideGender' <?= ((isset($vars['HideGender']) && $vars['HideGender'] == "Yes") ? ' checked="checked"' : ''); ?>/><label
                                for='HideGender'> <?= $words->get("Hidden"); ?></label>
                    </div>

                    <?php
                    if (in_array('SignupErrorInvalidGender', $vars['errors'])) {
                        echo '<div class="error">' . $words->get('SignupErrorInvalidGender') . '</div>';
                    }
                    ?>

                    <div class="col-3 mt-lg-3"><?= $words->get('Location') ?></div>
                    <div class="col-3 mt-lg-3">
                        <?= $member->city ?>
                        <br>
                        <?= $member->region ?>
                        <br>
                        <?= $member->country ?>
                    </div>
                    <div class="col-6 mt-lg-3"><a href="setlocation"
                                                  class="btn btn-primary"><?= $words->get('UpdateMyLocation') ?></a>
                    </div>

                </div>
            </div>
        </div>
        <div class="item">
            <a data-toggle="collapse" data-parent="#editProfile" href="#editProfile2" aria-expanded="false"
               aria-controls="editProfile2" class="btn btn-outline-primary editbutton text-left">
                <i class="fa fa-angle-down"></i> <?= $words->get('ProfileSummary') ?>
            </a>
            <div id="editProfile2" class="collapse editprofilebox" role="tabpanel">
                <div class="row my-2">
                    <div class="col-3"><?= $words->get('ProfileOccupation') ?></div>
                    <div class="col-9"><input class="w-100" name="Occupation"
                                              value="<?php echo htmlentities($vars['Occupation'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>

                    <div class="col-3"><?= $words->get('ProfileSummary') ?></div>
                    <div class="col-9"><textarea name="ProfileSummary" id="ProfileSummary" class="w-100"
                                                 rows="6"><?php echo htmlentities($vars['ProfileSummary'], ENT_COMPAT, 'UTF-8'); ?></textarea>
                    </div>

                </div>
            </div>
        </div>
        <div class="item">
            <a data-toggle="collapse" data-parent="#editProfile" href="#editProfile3" aria-expanded="false"
               aria-controls="editProfile3" class="btn btn-outline-primary editbutton text-left">
                <i class="fa fa-angle-down"></i> <?= $words->get('ProfileAccommodation') ?>
            </a>
            <div id="editProfile3" class="collapse editprofilebox" role="tabpanel">

                <div class="row my-2">
                    <div class="col-3">
                        <?= $words->get('HostingStatus') ?>
                    </div>
                    <div class="col-9">
                        <select name="Accomodation">
                            <?php
                            $syshcvol = PVars::getObj('syshcvol');
                            $tt = $syshcvol->Accomodation;
                            $max = count($tt);
                            for ($ii = 0; $ii < $max; $ii++) {
                                echo "<option value=\"" . $tt[$ii] . "\"";
                                if ($tt[$ii] == $vars['Accomodation'])
                                    echo " selected=\"selected\"";
                                echo ">", $words->get("Accomodation_" . $tt[$ii]), "</option>\n";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-3">
                        <? echo $words->get('ProfileNumberOfGuests'); ?>
                    </div>
                    <div class="col-9">
                        <select name="MaxGuest">
                            <?php
                            for ($ii = 1; $ii < 21; $ii++) {
                                ?>
                                <option value="<?= $ii ?>" <?= ($vars['MaxGuest'] == $ii) ? 'selected="selected"' : '' ?>><?= $ii ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileMaxLenghtOfStay') ?>
                    </div>
                    <div class="col-9">
<textarea name="MaxLenghtOfStay" class="long" cols="50"
          rows="2"><?= $vars['MaxLenghtOfStay'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileILiveWith') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="ILiveWith" class="long" cols="50" rows="2"><?= $vars['ILiveWith'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfilePleaseBring') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="PleaseBring" class="long" cols="50"
                                  rows="3"><?= $vars['PleaseBring'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileOfferGuests') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="OfferGuests" class="long" cols="50"
                                  rows="3"><?= $vars['OfferGuests'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileOfferHosts') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="OfferHosts" class="long" cols="50"
                                  rows="3"><?= $vars['OfferHosts'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ICanAlsoOffer') ?>
                    </div>
                    <div class="col-9">
                        <ul>
                            <?php
                            $max = count($vars['TabTypicOffer']);
                            for ($ii = 0; $ii < $max; $ii++) {
                                echo "<li><input type=\"checkbox\" name=\"check_" . $member->TabTypicOffer[$ii] . "\" ";
                                if (strpos($member->TypicOffer, $member->TabTypicOffer[$ii]) !== false)
                                    echo "checked=\"checked\"";
                                echo " />";
                                echo "&nbsp;&nbsp;", $words->get("TypicOffer_" . $member->TabTypicOffer[$ii]), "</li>\n";
                            }
                            ?>
                        </ul>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfilePublicTransport') ?>
                    </div>
                    <div class="col-9">
<textarea name="PublicTransport" class="long" cols="50"
          rows="3"><?= $vars['PublicTransport'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileRestrictionForGuest') ?>
                    </div>
                    <div class="col-9">
                        <ul>
                            <?php
                            $max = count($member->TabRestrictions);
                            for ($ii = 0; $ii < $max; $ii++) {
                                echo "<li><input type=\"checkbox\" name=\"check_" . $member->TabRestrictions[$ii] . "\" ";
                                if (strpos($member->Restrictions, $member->TabRestrictions[$ii]) !== false)
                                    echo "checked=\"checked\"";
                                echo " />";
                                echo "&nbsp;&nbsp;", $words->get("Restriction_" . $member->TabRestrictions[$ii]), "</li>\n";
                            }
                            ?>
                        </ul>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileHouseRules') ?>
                    </div>
                    <div class="col-9">
<textarea name="OtherRestrictions" class="long" cols="50"
          rows="3"><?= $vars['OtherRestrictions'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileAdditionalAccomodationInfo') ?>
                    </div>
                    <div class="col-9">
<textarea name="AdditionalAccomodationInfo" class="long" cols="50"
          rows="4"><?= $vars['AdditionalAccomodationInfo'] ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="item">
            <a data-toggle="collapse" data-parent="#editProfile" href="#editProfile4" aria-expanded="false"
               aria-controls="editProfile4" class="btn btn-outline-primary editbutton text-left">
                <i class="fa fa-angle-down"></i> <?= $words->get('ProfileInterests') ?>
            </a>
            <div id="editProfile4" class="collapse editprofilebox" role="tabpanel">
                <div class="row my-2">
                    <div class="col-3">
                        <?= $words->get('ProfileHobbies') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="Hobbies" class="long" cols="50" rows="4"><?= $vars['Hobbies'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileBooks') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="Books" class="long" cols="50" rows="4"><?= $vars['Books'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileMusic') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="Music" class="long" cols="50" rows="4"><?= $vars['Music'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileMovies') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="Movies" class="long" cols="50" rows="4"><?= $vars['Movies'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfileOrganizations') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="Organizations" class="long" cols="50"
                                  rows="4"><?= $vars['Organizations'] ?></textarea>
                    </div>

                </div>

            </div>
        </div>
        <div class="item">
            <a data-toggle="collapse" data-parent="#editProfile" href="#editProfile5" aria-expanded="false"
               aria-controls="editProfile5" class="btn btn-outline-primary editbutton text-left">
                <i class="fa fa-angle-down"></i> <?= $words->get('Languages') ?>
            </a>
            <div id="editProfile5" class="collapse editprofilebox" role="tabpanel">

                <div class="row my-2">
                    <div class="col-12 my-2"><?= $words->get('ProfileLanguagesSpoken') ?></div>
                    <?php
                    $lang_ids = array();
                    for ($ii = 0; $ii < count($vars['languages_selected']); $ii++) {
                        $lang_ids[] = $vars['languages_selected'][$ii]->IdLanguage; ?>

                        <div class="col-4">
                            <input type="hidden" name="memberslanguages[]"
                                   value="<?= $vars['languages_selected'][$ii]->IdLanguage ?>">
                            <input type="text" disabled value="<?= $vars['languages_selected'][$ii]->Name ?>"
                                   class="w-100">
                        </div>
                        <div class="col-8">
                            <select class="mll" name="memberslanguageslevel[]">
                                <?
                                for ($jj = 0; $jj < count($vars['language_levels']); $jj++) {
                                    $selected = $vars['language_levels'][$jj] == $vars['languages_selected'][$ii]->Level ? ' selected="selected"' : '';
                                    ?>
                                    <option value='<?= $vars['language_levels'][$jj] ?>'<?= $selected ?>><?= $words->getSilent("LanguageLevel_" . $vars['language_levels'][$jj]) ?></option>
                                <? } ?>

                            </select>
                            <?= $words->flushBuffer() ?>
                            <a href="#"
                               class="btn btn-outline-secondary p-1 py-0 remove_lang"><?= $words->get('RemoveLanguage') ?></a>
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
                        <input type="button" id="langbutton" class="btn btn-primary mt-1" name="addlang"
                               value="<?= $words->getSilent('AddLanguage') ?>"/>
                        <?= $words->flushBuffer() ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="item">
            <a data-toggle="collapse" data-parent="#editProfile" href="#editProfile6" aria-expanded="false"
               aria-controls="editProfile6" class="btn btn-outline-primary editbutton text-left">
                <i class="fa fa-angle-down"></i> <?= $words->get('ContactInfo') ?>
            </a>
            <div id="editProfile6" class="collapse editprofilebox" role="tabpanel">
                <div class="row my-2">
                    <? if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>
                        <div class="col-2">
                            <?= $words->get('FirstName') ?>
                        </div>
                        <div class="col-3">
                            <input class="<?php if (isset($errorFirstName)) { ?>error-input-text<?php } ?>" type="text"
                                   name="FirstName"
                                   value="<?php echo htmlentities($vars['FirstName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                            <?php if (isset($errorFirstName)) { ?>
                                <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidFirstName') ?></div>
                            <?php } ?>

                        </div>
                        <div class="col-7">
                            <input type="checkbox" value="Yes" name="IsHidden_FirstName"
                                <?php if ($vars['IsHidden_FirstName'])
                                    echo 'checked="checked"';
                                ?> />
                            <?= $words->get('hidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('SecondName') ?>
                        </div>
                        <div class="col-3">
                            <input type="text" name="SecondName"
                                   value="<?php echo htmlentities($vars['SecondName'], ENT_COMPAT, 'UTF-8'); ?>"/></td>
                        </div>
                        <div class="col-7">
                            <input type="checkbox" value="Yes" name="IsHidden_SecondName"
                                <?php if ($vars['IsHidden_SecondName'])
                                    echo 'checked="checked"';
                                ?> />
                            <?= $words->get('hidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('LastName') ?>
                        </div>
                        <div class="col-3">
                            <input class="<?php if (isset($errorLastName)) { ?>error-input-text<?php } ?>" type="text"
                                   name="LastName"
                                   value="<?php echo htmlentities($vars['LastName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                            <?php if (isset($errorLastName)) { ?>
                                <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidLastName') ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-7">

                            <input type="checkbox" value="Yes" name="IsHidden_LastName"
                                <?php if ($vars['IsHidden_LastName'])
                                    echo 'checked="checked"';
                                ?> />
                            <?= $words->get('hidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('SignupEmail') ?>
                        </div>
                        <div class="col-4">
                            <input class="<?php if (isset($errorEmail)) { ?>error-input-text<?php } ?>" type="text"
                                   size="25"
                                   name="Email" value="<?= str_replace('%40', '@', $vars['Email']) ?>"/>
                            <?php if (isset($errorEmail)) { ?>
                                <div class="error-caption"><?= $words->get('SignupErrorInvalidEmail') ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-6">
                            <?= $words->get('EmailIsAlwayHidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('Street') ?>
                        </div>
                        <div class="col-3">
                            <input class="<?php if (isset($errorStreet)) { ?>error-input-text<?php } ?>" type="text"
                                   name="Street"
                                   id="Street"
                                   value="<?php echo htmlentities($vars['Street'], ENT_COMPAT, 'UTF-8'); ?>"/>
                            <?php if (isset($errorStreet)) { ?>
                                <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidStreet') ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-7">
                            <input type="checkbox" value="Yes" name="IsHidden_Address"
                                <?php if ($vars['IsHidden_Address'])
                                    echo 'checked="checked"';
                                ?> />
                            <?= $words->get('hidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('HouseNumber') ?>
                        </div>
                        <div class="col-3">
                            <input class="short<?php if (isset($errorHouseNumber)) { ?> error-input-text<?php } ?>"
                                   type="text"
                                   name="HouseNumber" id="HouseNumber"
                                   value="<?php echo htmlentities($vars['HouseNumber'], ENT_COMPAT, 'UTF-8'); ?>"
                                   size="6"/>
                            <?php if (isset($errorHouseNumber)) { ?>
                                <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidHouseNumber') ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-7">
                        </div>

                        <div class="col-2">
                            <?= $words->get('Post code') ?>
                        </div>
                        <div class="col-3">
                            <input class="short <?php if (isset($errorZip)) { ?> error-input-text<?php } ?>" type="text"
                                   name="Zip"
                                   value="<?php echo htmlentities($vars['Zip'], ENT_COMPAT, 'UTF-8'); ?>" size="6"/>
                            <?php if (isset($errorZip)) { ?>
                                <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidZip') ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-7">
                            <input type="checkbox" value="Yes" name="IsHidden_Zip"
                                <?php if ($vars['IsHidden_Zip'])
                                    echo 'checked="checked"';
                                ?> />
                            <?= $words->get('hidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('ProfileHomePhoneNumber') ?>
                        </div>
                        <div class="col-3">
                            <input type="text" size="15" name="HomePhoneNumber"
                                   value="<?php echo htmlentities($vars['HomePhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        </div>
                        <div class="col-7">
                            <input type="checkbox" value="Yes" name="IsHidden_HomePhoneNumber"
                                <?php if ($vars['IsHidden_HomePhoneNumber'])
                                    echo 'checked="checked"';
                                ?> />
                            <?= $words->get('hidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('ProfileCellPhoneNumber') ?>
                        </div>
                        <div class="col-3">
                            <input type="text" size="15" name="CellPhoneNumber"
                                   value="<?php echo htmlentities($vars['CellPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        </div>
                        <div class="col-7">
                            <input type="checkbox" value="Yes" name="IsHidden_CellPhoneNumber"
                                <?php if ($vars['IsHidden_CellPhoneNumber'])
                                    echo 'checked="checked"';
                                ?> />
                            <?= $words->get('hidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('ProfileWorkPhoneNumber') ?>
                        </div>
                        <div class="col-3">
                            <input type="text" size="15" name="WorkPhoneNumber"
                                   value="<?php echo htmlentities($vars['WorkPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        </div>
                        <div class="col-7">
                            <input type="checkbox" value="Yes" name="IsHidden_WorkPhoneNumber"
                                <?php if ($vars['IsHidden_WorkPhoneNumber'])
                                    echo 'checked="checked"';
                                ?> />
                            <?= $words->get('hidden') ?>
                        </div>

                        <div class="col-2">
                            <?= $words->get('Website') ?>
                        </div>
                        <div class="col-4">
                            <input type="text" size="25" name="WebSite"
                                   value="<?php echo htmlentities($vars['WebSite'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        </div>
                        <div class="col-6">
                        </div>


                        <?php
                        //TODO: Update messengers to current standard
                        if (isset($vars['messengers'])) {
                            foreach ($vars['messengers'] as $me) {
                                $val = 'chat_' . $me['network_raw'];
                                ?>

                                <div class="col-2">
                                    <?= $me["network"] ?>
                                    <?= "<img src='" . PVars::getObj('env')->baseuri . "images/icons/icons1616/" . $me["image"] . "' width='16' height='16' title='" . $me["network"] . "' alt='" . $me["network"] . "' />" ?>
                                </div>
                                <div class="col-3">
                                    <input type="text" size="15" name="<?= $val ?>"
                                           value="<?php echo htmlentities($me["address"], ENT_COMPAT, 'UTF-8'); ?>"/>
                                </div>
                                <div class="col-7">

                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="item">
            <a data-toggle="collapse" data-parent="#editProfile" href="#editProfile7" aria-expanded="false"
               aria-controls="editProfile7" class="btn btn-outline-primary editbutton text-left">
                <i class="fa fa-angle-down"></i> <?= $words->get('ProfileTravelExperience') ?>
            </a>
            <div id="editProfile7" class="collapse editprofilebox" role="tabpanel">
                <div class="row my-2">
                    <div class="col-3">
                        <?= $words->get('ProfilePastTrips') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="PastTrips" class="long" cols="50" rows="2"><?= $vars['PastTrips'] ?></textarea>
                    </div>

                    <div class="col-3">
                        <?= $words->get('ProfilePlannedTrips') ?>
                    </div>
                    <div class="col-9">
                        <textarea name="PlannedTrips" class="long" cols="50"
                                  rows="4"><?= $vars['PlannedTrips'] ?></textarea>
                    </div>

                </div>
            </div>
        </div>

        <?php if (!empty($vars['Relations']) && 1 == 1) : // Disabled     ?>

        <div class="item">
            <a data-toggle="collapse" data-parent="#editProfile" href="#editProfile8" aria-expanded="false"
               aria-controls="editProfile8" class="btn btn-outline-primary editbutton text-left">
                <i class="fa fa-angle-down"></i> <?= $words->get('MyRelations') ?>
            </a>
            <div id="editProfile8" class="collapse editprofilebox" role="tabpanel">
                <div class="row my-2">

                    <?php
                    $Relations = $vars['Relations'];
                    foreach ($Relations as $Relation) {
                        $comment = $words->mInTrad($Relation->Comment, $profile_language);
                        if (is_numeric($comment)) {
                            $comment = '';
                        }
                        ?>

                        <div class="col-2">
                            <img src="members/avatar/<?= $Relation->Username ?>/50" height="50" width="50"
                                 alt="Profile"/><br>
                            <span class="small
                <?php
                            if ($Relation->Confirmed == 'Yes') {
                                echo ' font-weight-bold';
                            }
                            ?>
                "><?= $Relation->Username ?></span>
                        </div>
                        <div class="col-6">
                            <textarea cols="40" rows="2"
                                      name="RelationComment_<?= $Relation->id ?>"><?= $comment ?></textarea>
                        </div>
                        <div class="col-4">
                            <a href="/members/<?php echo $member->Username; ?>/relations/delete/<?php echo $Relation->id; ?>?redirect=editmyprofile#!specialrelations"
                               class="btn btn-outline-secondary p-1 py-0"
                               onclick="return confirm('<?php echo $words->getSilent('Relation_delete_confirmation'); ?>');"><?php echo $words->getFormatted("delrelation", $Relation->Username); ?><?php echo $words->flushBuffer(); ?></a>
                        </div>
                    <?php }
                    endif; ?>
                </div>
            </div>
        </div>

    </div>

    <div>
        <input type="submit" class="btn btn-primary editbutton text-left" id="submit" name="submit"
               value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
    </div>

</div>


<div>The same with tabs...</div>

<div class="col-12">
    <ul class="nav nav-tabs" id="editProfileTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="basics-tab" data-toggle="tab" href="#basics" role="tab" aria-controls="basics"
               aria-selected="true">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="aboutme-tab" data-toggle="tab" href="#aboutme" role="tab" aria-controls="aboutme"
               aria-selected="false"><?= $words->get('ProfileSummary') ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="accommodation-tab" data-toggle="tab" href="#accommodation" role="tab"
               aria-controls="contact" aria-selected="false"><?=$words->get('ProfileAccommodation')?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="myinterests-tab" data-toggle="tab" href="#myinterests" role="tab"
               aria-controls="contact" aria-selected="false"><?=$words->get('ProfileInterests')?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="languages-tab" data-toggle="tab" href="#languages" role="tab"
               aria-controls="contact" aria-selected="false">Languages</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="contactinfo-tab" data-toggle="tab" href="#contactinfo" role="tab"
               aria-controls="contact" aria-selected="false"><?=$words->get('ContactInfo')?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="travel-tab" data-toggle="tab" href="#travel" role="tab" aria-controls="contact"
               aria-selected="false">Travel Experience</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="family-tab" data-toggle="tab" href="#family" role="tab" aria-controls="contact"
               aria-selected="false"><?=$words->get('MyRelations')?></a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="basics" role="tabpanel" aria-labelledby="home-tab">
            <div class="row my-2">
                <div class="col-3 pt-2">
                    <?= $words->get('SignupUsername') ?>
                </div>
                <div class="col-9 h4">
                    <?= $member->Username ?>
                </div>
                <div class="col-12 small mb-3 alert alert-info">
                    <?= $words->get('subline_username_edit') ?>
                </div>


                <div class="col-3">
                    <img src="members/avatar/<?= $member->Username ?>/50"
                         title="Current picture" alt="Current picture" height="50" width="50"><br>
                    <?= $words->get('ProfilePicture') ?>
                </div>

                <div class="col-9">
                    <input id="profile_picture" name="profile_picture" type="file"/>
                    <label for="profile_picture"><?= $words->get('uploadselectpicture'); ?></label><br/>
                    <span class="small"><?= $words->get('Profile_UploadWarning', sprintf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576)); ?></span>
                </div>


                <div class="col-3">
                    <?= $words->get('SignupBirthDate') ?><br>
                    <span class="small"><?= $words->get('EmailIsAlwayHidden') ?></span>
                </div>
                <div class="col-5">
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
                <div class="col-4">
                    <input name="HideBirthDate" value="Yes"
                           type="checkbox" <?= ($vars['HideBirthDate'] == 'Yes') ? 'checked="checked"' : '' ?> />
                    Hide age
                </div>
                <div class="col-3 offset-8 small">
                    <?= $words->get('HiddenAgeInfo'); ?>
                </div>

                <div class="col-3 mt-3 pt-2">
                    <?= $words->get('Gender'); ?>

                </div>
                <div class="col-9 mt-3">
                    <div class="btn-group" data-toggle="buttons">
                        <label for='genderF'
                               class="btn btn-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'female') ? 'active' : '' ?>">
                            <input type="radio" id="genderF" name="gender"
                                   value="female" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'female') ? ' checked="checked"' : ''); ?>/><?= $words->get('female'); ?>
                        </label>
                        <label for='genderM'
                               class="btn btn-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'male') ? 'active' : '' ?>"><input
                                    type="radio" id='genderM' name="gender"
                                    value="male" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'male') ? ' checked="checked"' : ''); ?>/> <?= $words->get('male'); ?>
                        </label>
                        <label for='genderX'
                               class="btn btn-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'other') ? 'active' : '' ?>"><input
                                    type="radio" id='genderX' name="gender"
                                    value="other" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'other') ? ' checked="checked"' : ''); ?>/> <?= $words->get('GenderOther'); ?>
                        </label>
                    </div>
                    <input name="HideGender" value="Yes" type="checkbox"
                           id='HideGender' <?= ((isset($vars['HideGender']) && $vars['HideGender'] == "Yes") ? ' checked="checked"' : ''); ?>/><label
                            for='HideGender'> <?= $words->get("Hidden"); ?></label>
                </div>

                <?php
                if (in_array('SignupErrorInvalidGender', $vars['errors'])) {
                    echo '<div class="error">' . $words->get('SignupErrorInvalidGender') . '</div>';
                }
                ?>

                <div class="col-3 mt-lg-3"><?= $words->get('Location') ?></div>
                <div class="col-3 mt-lg-3">
                    <?= $member->city ?>
                    <br>
                    <?= $member->region ?>
                    <br>
                    <?= $member->country ?>
                </div>
                <div class="col-6 mt-lg-3"><a href="setlocation"
                                              class="btn btn-primary"><?= $words->get('UpdateMyLocation') ?></a>
                </div>

            </div>
        </div>
        <div class="tab-pane fade" id="aboutme" role="tabpanel" aria-labelledby="aboutme-tab">
            <div class="row my-2">
                <div class="col-3"><?= $words->get('ProfileOccupation') ?></div>
                <div class="col-9"><input class="w-100" name="Occupation"
                                          value="<?php echo htmlentities($vars['Occupation'], ENT_COMPAT, 'UTF-8'); ?>"/>
                </div>

                <div class="col-3"><?= $words->get('ProfileSummary') ?></div>
                <div class="col-9"><textarea name="ProfileSummary" id="ProfileSummary" class="w-100"
                                             rows="6"><?php echo htmlentities($vars['ProfileSummary'], ENT_COMPAT, 'UTF-8'); ?></textarea>
                </div>

            </div>
        </div>
        <div class="tab-pane fade" id="accommodation" role="tabpanel" aria-labelledby="accommodation-tab">

            <div class="row my-2">
                <div class="col-3">
                    <?= $words->get('HostingStatus') ?>
                </div>
                <div class="col-9">
                    <select name="Accomodation">
                        <?php
                        $syshcvol = PVars::getObj('syshcvol');
                        $tt = $syshcvol->Accomodation;
                        $max = count($tt);
                        for ($ii = 0; $ii < $max; $ii++) {
                            echo "<option value=\"" . $tt[$ii] . "\"";
                            if ($tt[$ii] == $vars['Accomodation'])
                                echo " selected=\"selected\"";
                            echo ">", $words->get("Accomodation_" . $tt[$ii]), "</option>\n";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-3">
                    <? echo $words->get('ProfileNumberOfGuests'); ?>
                </div>
                <div class="col-9">
                    <select name="MaxGuest">
                        <?php
                        for ($ii = 1; $ii < 21; $ii++) {
                            ?>
                            <option value="<?= $ii ?>" <?= ($vars['MaxGuest'] == $ii) ? 'selected="selected"' : '' ?>><?= $ii ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileMaxLenghtOfStay') ?>
                </div>
                <div class="col-9">
<textarea name="MaxLenghtOfStay" class="long" cols="50"
          rows="2"><?= $vars['MaxLenghtOfStay'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileILiveWith') ?>
                </div>
                <div class="col-9">
                    <textarea name="ILiveWith" class="long" cols="50" rows="2"><?= $vars['ILiveWith'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfilePleaseBring') ?>
                </div>
                <div class="col-9">
                        <textarea name="PleaseBring" class="long" cols="50"
                                  rows="3"><?= $vars['PleaseBring'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileOfferGuests') ?>
                </div>
                <div class="col-9">
                        <textarea name="OfferGuests" class="long" cols="50"
                                  rows="3"><?= $vars['OfferGuests'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileOfferHosts') ?>
                </div>
                <div class="col-9">
                        <textarea name="OfferHosts" class="long" cols="50"
                                  rows="3"><?= $vars['OfferHosts'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ICanAlsoOffer') ?>
                </div>
                <div class="col-9">
                    <ul>
                        <?php
                        $max = count($vars['TabTypicOffer']);
                        for ($ii = 0; $ii < $max; $ii++) {
                            echo "<li><input type=\"checkbox\" name=\"check_" . $member->TabTypicOffer[$ii] . "\" ";
                            if (strpos($member->TypicOffer, $member->TabTypicOffer[$ii]) !== false)
                                echo "checked=\"checked\"";
                            echo " />";
                            echo "&nbsp;&nbsp;", $words->get("TypicOffer_" . $member->TabTypicOffer[$ii]), "</li>\n";
                        }
                        ?>
                    </ul>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfilePublicTransport') ?>
                </div>
                <div class="col-9">
<textarea name="PublicTransport" class="long" cols="50"
          rows="3"><?= $vars['PublicTransport'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileRestrictionForGuest') ?>
                </div>
                <div class="col-9">
                    <ul>
                        <?php
                        $max = count($member->TabRestrictions);
                        for ($ii = 0; $ii < $max; $ii++) {
                            echo "<li><input type=\"checkbox\" name=\"check_" . $member->TabRestrictions[$ii] . "\" ";
                            if (strpos($member->Restrictions, $member->TabRestrictions[$ii]) !== false)
                                echo "checked=\"checked\"";
                            echo " />";
                            echo "&nbsp;&nbsp;", $words->get("Restriction_" . $member->TabRestrictions[$ii]), "</li>\n";
                        }
                        ?>
                    </ul>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileHouseRules') ?>
                </div>
                <div class="col-9">
<textarea name="OtherRestrictions" class="long" cols="50"
          rows="3"><?= $vars['OtherRestrictions'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileAdditionalAccomodationInfo') ?>
                </div>
                <div class="col-9">
<textarea name="AdditionalAccomodationInfo" class="long" cols="50"
          rows="4"><?= $vars['AdditionalAccomodationInfo'] ?></textarea>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="myinterests" role="tabpanel" aria-labelledby="myinterests-tab">
            <div class="row my-2">
                <div class="col-3">
                    <?= $words->get('ProfileHobbies') ?>
                </div>
                <div class="col-9">
                    <textarea name="Hobbies" class="long" cols="50" rows="4"><?= $vars['Hobbies'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileBooks') ?>
                </div>
                <div class="col-9">
                    <textarea name="Books" class="long" cols="50" rows="4"><?= $vars['Books'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileMusic') ?>
                </div>
                <div class="col-9">
                    <textarea name="Music" class="long" cols="50" rows="4"><?= $vars['Music'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileMovies') ?>
                </div>
                <div class="col-9">
                    <textarea name="Movies" class="long" cols="50" rows="4"><?= $vars['Movies'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfileOrganizations') ?>
                </div>
                <div class="col-9">
                        <textarea name="Organizations" class="long" cols="50"
                                  rows="4"><?= $vars['Organizations'] ?></textarea>
                </div>

            </div>

        </div>
        <div class="tab-pane fade" id="languages" role="tabpanel" aria-labelledby="languages-tab">
            <div class="row my-2">
                <div class="col-12 my-2"><?= $words->get('ProfileLanguagesSpoken') ?></div>
                <?php
                $lang_ids = array();
                for ($ii = 0; $ii < count($vars['languages_selected']); $ii++) {
                    $lang_ids[] = $vars['languages_selected'][$ii]->IdLanguage; ?>

                    <div class="col-4">
                        <input type="hidden" name="memberslanguages[]"
                               value="<?= $vars['languages_selected'][$ii]->IdLanguage ?>">
                        <input type="text" disabled value="<?= $vars['languages_selected'][$ii]->Name ?>"
                               class="w-100">
                    </div>
                    <div class="col-8">
                        <select class="mll" name="memberslanguageslevel[]">
                            <?
                            for ($jj = 0; $jj < count($vars['language_levels']); $jj++) {
                                $selected = $vars['language_levels'][$jj] == $vars['languages_selected'][$ii]->Level ? ' selected="selected"' : '';
                                ?>
                                <option value='<?= $vars['language_levels'][$jj] ?>'<?= $selected ?>><?= $words->getSilent("LanguageLevel_" . $vars['language_levels'][$jj]) ?></option>
                            <? } ?>

                        </select>
                        <?= $words->flushBuffer() ?>
                        <a href="#"
                           class="btn btn-outline-secondary p-1 py-0 remove_lang"><?= $words->get('RemoveLanguage') ?></a>
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
                    <input type="button" id="langbutton" class="btn btn-primary mt-1" name="addlang"
                           value="<?= $words->getSilent('AddLanguage') ?>"/>
                    <?= $words->flushBuffer() ?>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="contactinfo" role="tabpanel" aria-labelledby="contactinfo-tab">
            <div class="row my-2">
                <? if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>
                    <div class="col-2">
                        <?= $words->get('FirstName') ?>
                    </div>
                    <div class="col-3">
                        <input class="<?php if (isset($errorFirstName)) { ?>error-input-text<?php } ?>" type="text"
                               name="FirstName"
                               value="<?php echo htmlentities($vars['FirstName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <?php if (isset($errorFirstName)) { ?>
                            <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidFirstName') ?></div>
                        <?php } ?>

                    </div>
                    <div class="col-7">
                        <input type="checkbox" value="Yes" name="IsHidden_FirstName"
                            <?php if ($vars['IsHidden_FirstName'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('SecondName') ?>
                    </div>
                    <div class="col-3">
                        <input type="text" name="SecondName"
                               value="<?php echo htmlentities($vars['SecondName'], ENT_COMPAT, 'UTF-8'); ?>"/></td>
                    </div>
                    <div class="col-7">
                        <input type="checkbox" value="Yes" name="IsHidden_SecondName"
                            <?php if ($vars['IsHidden_SecondName'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('LastName') ?>
                    </div>
                    <div class="col-3">
                        <input class="<?php if (isset($errorLastName)) { ?>error-input-text<?php } ?>" type="text"
                               name="LastName"
                               value="<?php echo htmlentities($vars['LastName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <?php if (isset($errorLastName)) { ?>
                            <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidLastName') ?></div>
                        <?php } ?>
                    </div>
                    <div class="col-7">

                        <input type="checkbox" value="Yes" name="IsHidden_LastName"
                            <?php if ($vars['IsHidden_LastName'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('SignupEmail') ?>
                    </div>
                    <div class="col-4">
                        <input class="<?php if (isset($errorEmail)) { ?>error-input-text<?php } ?>" type="text"
                               size="25"
                               name="Email" value="<?= str_replace('%40', '@', $vars['Email']) ?>"/>
                        <?php if (isset($errorEmail)) { ?>
                            <div class="error-caption"><?= $words->get('SignupErrorInvalidEmail') ?></div>
                        <?php } ?>
                    </div>
                    <div class="col-6">
                        <?= $words->get('EmailIsAlwayHidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('Street') ?>
                    </div>
                    <div class="col-3">
                        <input class="<?php if (isset($errorStreet)) { ?>error-input-text<?php } ?>" type="text"
                               name="Street"
                               id="Street"
                               value="<?php echo htmlentities($vars['Street'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <?php if (isset($errorStreet)) { ?>
                            <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidStreet') ?></div>
                        <?php } ?>
                    </div>
                    <div class="col-7">
                        <input type="checkbox" value="Yes" name="IsHidden_Address"
                            <?php if ($vars['IsHidden_Address'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('HouseNumber') ?>
                    </div>
                    <div class="col-3">
                        <input class="short<?php if (isset($errorHouseNumber)) { ?> error-input-text<?php } ?>"
                               type="text"
                               name="HouseNumber" id="HouseNumber"
                               value="<?php echo htmlentities($vars['HouseNumber'], ENT_COMPAT, 'UTF-8'); ?>"
                               size="6"/>
                        <?php if (isset($errorHouseNumber)) { ?>
                            <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidHouseNumber') ?></div>
                        <?php } ?>
                    </div>
                    <div class="col-7">
                    </div>

                    <div class="col-2">
                        <?= $words->get('Post code') ?>
                    </div>
                    <div class="col-3">
                        <input class="short <?php if (isset($errorZip)) { ?> error-input-text<?php } ?>" type="text"
                               name="Zip"
                               value="<?php echo htmlentities($vars['Zip'], ENT_COMPAT, 'UTF-8'); ?>" size="6"/>
                        <?php if (isset($errorZip)) { ?>
                            <div class="alert alert-danger"><?= $words->get('SignupErrorInvalidZip') ?></div>
                        <?php } ?>
                    </div>
                    <div class="col-7">
                        <input type="checkbox" value="Yes" name="IsHidden_Zip"
                            <?php if ($vars['IsHidden_Zip'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('ProfileHomePhoneNumber') ?>
                    </div>
                    <div class="col-3">
                        <input type="text" size="15" name="HomePhoneNumber"
                               value="<?php echo htmlentities($vars['HomePhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>
                    <div class="col-7">
                        <input type="checkbox" value="Yes" name="IsHidden_HomePhoneNumber"
                            <?php if ($vars['IsHidden_HomePhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('ProfileCellPhoneNumber') ?>
                    </div>
                    <div class="col-3">
                        <input type="text" size="15" name="CellPhoneNumber"
                               value="<?php echo htmlentities($vars['CellPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>
                    <div class="col-7">
                        <input type="checkbox" value="Yes" name="IsHidden_CellPhoneNumber"
                            <?php if ($vars['IsHidden_CellPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('ProfileWorkPhoneNumber') ?>
                    </div>
                    <div class="col-3">
                        <input type="text" size="15" name="WorkPhoneNumber"
                               value="<?php echo htmlentities($vars['WorkPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>
                    <div class="col-7">
                        <input type="checkbox" value="Yes" name="IsHidden_WorkPhoneNumber"
                            <?php if ($vars['IsHidden_WorkPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-2">
                        <?= $words->get('Website') ?>
                    </div>
                    <div class="col-4">
                        <input type="text" size="25" name="WebSite"
                               value="<?php echo htmlentities($vars['WebSite'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>
                    <div class="col-6">
                    </div>


                    <?php
                    //TODO: Update messengers to current standard
                    if (isset($vars['messengers'])) {
                        foreach ($vars['messengers'] as $me) {
                            $val = 'chat_' . $me['network_raw'];
                            ?>

                            <div class="col-2">
                                <?= $me["network"] ?>
                                <?= "<img src='" . PVars::getObj('env')->baseuri . "images/icons/icons1616/" . $me["image"] . "' width='16' height='16' title='" . $me["network"] . "' alt='" . $me["network"] . "' />" ?>
                            </div>
                            <div class="col-3">
                                <input type="text" size="15" name="<?= $val ?>"
                                       value="<?php echo htmlentities($me["address"], ENT_COMPAT, 'UTF-8'); ?>"/>
                            </div>
                            <div class="col-7">

                            </div>
                            <?php
                        }
                    }
                }
                ?>
            </div>
        </div>
        <div class="tab-pane fade" id="travel" role="tabpanel" aria-labelledby="travel-tab">
            <div class="row my-2">
                <div class="col-3">
                    <?= $words->get('ProfilePastTrips') ?>
                </div>
                <div class="col-9">
                    <textarea name="PastTrips" class="long" cols="50" rows="2"><?= $vars['PastTrips'] ?></textarea>
                </div>

                <div class="col-3">
                    <?= $words->get('ProfilePlannedTrips') ?>
                </div>
                <div class="col-9">
                        <textarea name="PlannedTrips" class="long" cols="50"
                                  rows="4"><?= $vars['PlannedTrips'] ?></textarea>
                </div>

            </div>
        </div>

        <div class="tab-pane fade" id="family" role="tabpanel" aria-labelledby="family-tab">
            <div class="row my-2">

                <?php
                $Relations = $vars['Relations'];
                foreach ($Relations as $Relation) {
                    $comment = $words->mInTrad($Relation->Comment, $profile_language);
                    if (is_numeric($comment)) {
                        $comment = '';
                    }
                    ?>

                    <div class="col-2">
                        <img src="members/avatar/<?= $Relation->Username ?>/50" height="50" width="50"
                             alt="Profile"/><br>
                        <span class="small
                <?php
                        if ($Relation->Confirmed == 'Yes') {
                            echo ' font-weight-bold';
                        }
                        ?>
                "><?= $Relation->Username ?></span>
                    </div>
                    <div class="col-6">
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

<div>
    <input type="submit" class="btn btn-primary editbutton text-left" id="submit" name="submit"
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
