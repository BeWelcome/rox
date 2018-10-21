<div class="tab-pane fade show card active" id="basics" role="tabpanel" aria-labelledby="home-tab">
    <div class="card-header" role="tab" id="heading-home">
        <h5 class="mb-0">
            <a data-toggle="collapse" href="#collapse-home" data-parent="#content" aria-expanded="true" aria-controls="collapse-home">
                <?= $words->get('Home') ?>
            </a>
        </h5>
    </div>
    <div id="collapse-home" class="collapse show" role="tabpanel" aria-labelledby="heading-home">
        <div class="card-body col-12 p-0 p-md-3">
            <div class="form-row align-items-center p-2 p-md-3">
                <label for="SignupUsername" class="col-12 col-md-4 col-lg-2"><?= $words->get('SignupUsername') ?></label>
                <div class="col-12 col-md-8 col-lg-3 h4">
                    <?= $member->Username ?>
                </div>
                <div class="col-12 col-lg-7 small mb-3 alert alert-info">
                    <?= $words->get('subline_username_edit') ?>
                </div>

                <div class="col-4 col-md-3 font-weight-bold">
                    <img src="members/avatar/<?= $member->Username ?>/100"
                         title="Current picture" alt="Current picture" height="100" width="100"><br>
                    <?= $words->get('ProfilePicture') ?>
                </div>

                <div class="col-8 col-md-9 mt-3 form-group">
                    <label for="profile_picture"
                           class="alert-primary mb-0"><?= $words->get('uploadselectpicture'); ?></label>
                    <input id="profile_picture" name="profile_picture" class="form-control btn btn-outline-primary p-0 text-left" type="file"/>
                    <br/>
                    <span class="small text-muted"><?= $words->get('Profile_UploadWarning', sprintf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576)); ?></span>
                </div>

                <? if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>
                    <div class="col-12 form-group row mb-1">
                        <label for="FirstName" class="col-md-3 col-form-label"><?= $words->get('FirstName') ?></label>
                        <div class="col-8 col-md-7">
                        <input class="form-control<?php if (isset($errorFirstName)) { ?> error-input-text<?php } ?>" type="text"
                               name="FirstName"
                               value="<?php echo htmlentities($vars['FirstName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        </div>
                        <div class="col-4 col-md-2 form-inline">
                        <input type="checkbox" value="Yes" name="IsHidden_FirstName"
                            <?php if ($vars['IsHidden_FirstName'] === 'Yes')
                                echo 'checked="checked"';
                            ?> />
                            <label for="IsHidden_FirstName"><?= $words->get('hidden') ?></label>
                        </div>
                        <?php if (isset($errorFirstName)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidFirstName') ?></div>
                        <?php } ?>
                    </div>

                    <div class="col-12 form-group row mb-1">
                        <label for="SecondName" class="col-md-3 col-form-label"><?= $words->get('SecondName') ?></label>
                        <div class="col-8 col-md-7">
                        <input type="text" name="SecondName" class="form-control"
                               value="<?php echo htmlentities($vars['SecondName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        </div>
                        <div class="col-4 col-md-2 form-inline">
                            <input type="checkbox" value="Yes" name="IsHidden_SecondName"
                            <?php if ($vars['IsHidden_SecondName'] === 'Yes')
                                echo 'checked="checked"';
                            ?> />
                            <label for="IsHidden_SecondName"><?= $words->get('hidden') ?></label>
                        </div>
                    </div>

                    <div class="col-12 form-group row mb-1">
                        <label for="LastName" class="col-md-3 col-form-label"><?= $words->get('LastName') ?></label>
                        <div class="col-8 col-md-7">
                        <input class="form-control <?php if (isset($errorLastName)) { ?>error-input-text<?php } ?>" type="text"
                               name="LastName"
                               value="<?php echo htmlentities($vars['LastName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        </div>
                        <div class="col-4 col-md-2 form-inline">
                            <input type="checkbox" value="Yes" name="IsHidden_LastName"
                            <?php if ($vars['IsHidden_LastName'] === 'Yes')
                                echo 'checked="checked"';
                            ?> />
                            <label for="IsHidden_LastName"><?= $words->get('hidden') ?></label>
                        </div>
                        <?php if (isset($errorLastName)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidLastName') ?></div>
                        <?php } ?>
                    </div>

                    <div class="col-12 form-group row mt-2">
                        <label for="SignupEmail" class="col-12 col-md-3 col-form-label"><?= $words->get('SignupEmail') ?></label>
                        <div class="col-8 col-md-7">
                            <input class="form-control<?php if (isset($errorEmail)) { ?> error-input-text<?php } ?>" type="text"
                                   name="Email" value="<?= str_replace('%40', '@', $vars['Email']) ?>"/>
                        </div>
                        <div class="col-12 col-md-7 offset-md-3 small text-muted"><?= $words->get('EmailIsAlwayHidden') ?></div>
                        <?php if (isset($errorEmail)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidEmail') ?></div>
                        <?php } ?>
                    </div>

                <? } ?>

                <div class="col-12 form-group form-row align-items-center mt-2 mb-0">
                        <label for="SignupBirthDate" class="col-md-3 col-form-label pb-0"><?= $words->get('SignupBirthDate') ?></label>
                        <div class="col-12 col-md-7 offset-md-3 small text-muted order-1 order-md-12"><?= $words->get('EmailIsAlwayHidden') ?></div>
                    <div class="col-auto order-2">
                        <select id="BirthYear" name="BirthYear" class="form-control select2">
                        <option value="0"><?php echo $words->getSilent('SignupBirthYear'); ?></option>
                        <?php echo $birthYearOptions; ?>
                    </select>
                    </div>
                    <div class="col-auto order-3">
                    <select name="BirthMonth" class="form-control select2">
                        <option value="0"><?php echo $words->getSilent('SignupBirthMonth'); ?></option>
                        <?php for ($i = 1; $i <= 12; $i++) { ?>
                            <option value="<?php echo $i; ?>"<?php
                            if (isset($vars['BirthMonth']) && $vars['BirthMonth'] == $i) {
                                echo ' selected="selected"';
                            }
                            ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                    </div>
                    <div class="col-auto order-4">
                    <select name="BirthDay" class="form-control select2">
                        <option value="0"><?php echo $words->getSilent('SignupBirthDay'); ?></option>
                        <?php for ($i = 1; $i <= 31; $i++) { ?>
                            <option value="<?php echo $i; ?>"<?php
                            if (isset($vars['BirthDay']) && $vars['BirthDay'] == $i) {
                                echo ' selected="selected"';
                            }
                            ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                    </div>

                    <?php echo $words->flushBuffer(); ?>
                    <?php
                    if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                        echo '<div class="alert alert-danger">' . $words->get('SignupErrorBirthDate') . '</div>';
                    }
                    if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                        echo '<div class="alert alert-danger">' . $words->getFormatted('SignupErrorBirthDateToLow', SignupModel::YOUNGEST_MEMBER) . '</div>';
                    }
                    ?>

                </div>
                <div class="col-12 col-md-9 offset-md-3 mb-2 mb-sm-0 form-check-inline">
                    <input name="HideBirthDate" value="Yes"
                           type="checkbox" <?= ($vars['HideBirthDate'] == 'Yes') ? 'checked="checked"' : '' ?> />
                    <label for="HideBirthDate" class="m-0 ml-2"><?= $words->get('HiddenAgeInfo'); ?></label>
                </div>

                <div class="col-12 form-group row mt-1 mt-md-3 align-items-center">

                    <label for="Gender" class="col-md-3 col-form-label"><?= $words->get('Gender'); ?></label>

                    <div class="col-auto btn-group" data-toggle="buttons">
                        <label for='genderF'
                               class="btn btn-outline-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'female') ? 'active' : '' ?>">
                            <input type="radio" id="genderF" name="gender"
                                   value="female"
                                   class="noradio" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'female') ? ' checked="checked"' : ''); ?>/><?= $words->get('female'); ?>
                        </label>
                        <label for='genderM'
                               class="btn btn-outline-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'male') ? 'active' : '' ?>"><input
                                type="radio" id='genderM' name="gender"
                                value="male"
                                class="noradio" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'male') ? ' checked="checked"' : ''); ?>/> <?= $words->get('male'); ?>
                        </label>
                        <label for='genderX'
                               class="btn btn-outline-primary <?= (isset($vars['Gender']) && $vars['Gender'] == 'other') ? 'active' : '' ?>"><input
                                type="radio" id='genderX' name="gender"
                                value="other"
                                class="noradio" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'other') ? ' checked="checked"' : ''); ?>/> <?= $words->get('GenderOther'); ?>
                        </label>

                    </div>

                    <div class="col-auto">
                    <input name="HideGender" value="Yes" type="checkbox"
                           id='HideGender' <?= ((isset($vars['HideGender']) && $vars['HideGender'] == "Yes") ? ' checked="checked"' : ''); ?> class="ml-2">
                        <label for="HideGender"><?= $words->get("Hidden"); ?></label>
                    </div>

                </div>
                <?php
                if (in_array('SignupErrorInvalidGender', $vars['errors'])) {
                    echo '<div class="alert alert-danger">' . $words->get('SignupErrorInvalidGender') . '</div>';
                }
                ?>

                <div class="col-12 form-group row mt-1 mt-md-3">
                    <label for="Location" class="col-md-3 col-form-label"><?= $words->get('Location') ?></label>

                    <div class="col-auto">
                        <i class="fa fa-25 fa-map-marker-alt float-left mr-2"></i>
                        <div class="float-left"><span class="font-weight-bold"><?= $member->city ?></span>
                        <br>
                        <?= $member->region ?>, <?= $member->country ?>
                         </div>
                    </div>
                    <div class="col-auto">
                        <a href="setlocation"
                       class="btn btn-outline-primary float-left"><?= $words->get('UpdateMyLocation') ?></a>
                    </div>
                </div>

                <div class="col-12">
                    <input type="submit" class="btn btn-primary float-right m-2" id="submit" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>