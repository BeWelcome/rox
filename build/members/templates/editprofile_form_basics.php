<div class="card">
    <div class="card-header" id="heading-basics">
        <a data-toggle="collapse" href="#collapse-home" aria-expanded="true"
           aria-controls="collapse-home" class="mb-0 d-block">
            <?= $words->get('profile.basics') ?>
        </a>
    </div>
    <div id="collapse-home" class="show" data-parent="#editProfile" aria-labelledby="heading-basics">
        <div class="card-body">
            <div class="form-row mb-2">
                <label for="SignupUsername" class="col-12 col-md-3"><?= $words->get('SignupUsername') ?></label>
                <div class="col-12 col-md-9">
                    <p class="font-weight-bold"><?= $member->Username ?></p>
                    <div class="alert alert-info">
                        <?= $words->get('subline_username_edit') ?>
                    </div>
                </div>
            </div>
            <?php if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>
                <div class="form-row mb-2">
                    <label for="FirstName" class="col-md-3 col-form-label"><?= $words->get('FirstName') ?></label>
                    <div class="col-8 col-md-7">
                        <input class="o-input<?php if (isset($errorFirstName)) { ?> error-input-text<?php } ?>"
                               type="text"
                               name="FirstName"
                               value="<?php echo htmlentities($vars['FirstName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>
                    <div class="col-4 col-md-2 u-flex u-items-center"><div class="o-checkbox">
                        <input type="checkbox" value="Yes" name="IsHidden_FirstName"
                            <?php if ($vars['IsHidden_FirstName'] === 'Yes')
                                echo 'checked="checked"';
                            ?> class="o-checkbox__input"/>
                        <label for="IsHidden_FirstName" class="o-checkbox__label"><?= $words->get('hidden') ?></label>
                        </div>
                    </div>
                    <?php if (isset($errorFirstName)) { ?>
                        <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidFirstName') ?></div>
                    <?php } ?>
                </div>
                <div class="form-row mb-2">
                    <label for="SecondName" class="col-md-3 col-form-label"><?= $words->get('SecondName') ?></label>
                    <div class="col-8 col-md-7">
                        <input type="text" name="SecondName" class="o-input"
                               value="<?php echo htmlentities($vars['SecondName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>
                    <div class="col-4 col-md-2 u-flex u-items-center"><div class="o-checkbox">
                        <input type="checkbox" value="Yes" name="IsHidden_SecondName"
                            <?php if ($vars['IsHidden_SecondName'] === 'Yes')
                                echo 'checked="checked"';
                            ?> class="o-checkbox__input"/>
                        <label for="IsHidden_SecondName" class="o-checkbox__label"><?= $words->get('hidden') ?></label>
                        </div>
                    </div>
                </div>

                <div class="form-row mb-2">
                    <label for="LastName" class="col-md-3 col-form-label"><?= $words->get('LastName') ?></label>
                    <div class="col-8 col-md-7">
                        <input class="o-input <?php if (isset($errorLastName)) { ?>error-input-text<?php } ?>"
                               type="text"
                               name="LastName"
                               value="<?php echo htmlentities($vars['LastName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>
                    <div class="col-4 col-md-2 u-flex u-items-center"><div class="o-checkbox">
                        <input type="checkbox" value="Yes" name="IsHidden_LastName"
                            <?php if ($vars['IsHidden_LastName'] === 'Yes')
                                echo 'checked="checked"';
                            ?> class="o-checkbox__input"/>
                        <label for="IsHidden_LastName" class="o-checkbox__label"><?= $words->get('hidden') ?></label>
                    </div>
                    </div>
                    <?php if (isset($errorLastName)) { ?>
                        <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidLastName') ?></div>
                    <?php } ?>
                </div>
                <div class="form-row mb-2">
                    <label for="SignupEmail"
                           class="col-12 col-md-3 col-form-label"><?= $words->get('SignupEmail') ?></label>
                    <div class="col-8 col-md-7">
                        <input class="o-input<?php if (isset($errorEmail)) { ?> error-input-text<?php } ?>"
                               type="text"
                               name="Email" value="<?= str_replace('%40', '@', $vars['Email']) ?>"/>
                    </div>
                    <div
                        class="col-12 col-md-7 offset-md-3 small text-muted"><?= $words->get('EmailIsAlwayHidden') ?></div>
                    <?php if (isset($errorEmail)) { ?>
                        <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidEmail') ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="form-row mb-2">
                <label for="birth-date"
                       class="col-md-3 col-form-label pb-0"><?= $words->get('SignupBirthDate') ?></label>
                <div class="col-8 col-md-7">
                    <input type="text"
                           id="birth-date"
                           name="birth-date"
                               class="o-input datetimepicker-input"
                           data-toggle="datetimepicker"
                           data-target="#birth-date" value="<?= $vars['BirthDate'] ?>">
                </div>
                <div class="col-12 col-md-7 offset-md-3 small text-muted"><?= $words->get('EmailIsAlwayHidden') ?></div>
            </div>
            <div class="form-row mb-2">
                <div class="col-12 col-md-3">
                    <label for="HideBirthDate" class="col-form-label"><?= $words->get('ShowAge'); ?></label>
                </div>
                <div class="col-12 col-md-9">
                    <div class="o-checkbox">
                        <input id="HideBirthDate" name="HideBirthDate" value="Yes" class="o-checkbox__input"
                               type="checkbox" <?= ($vars['HideBirthDate'] == 'Yes') ? 'checked="checked"' : '' ?> />
                        <label for="HideBirthDate" class="o-checkbox__label"><?= $words->get('HiddenAgeInfo'); ?></label>
                    </div>
                </div>
            </div>

            <div class="form-row mb-2">

                <label for="Gender" class="col-md-3 col-form-label"><?= $words->get('Gender'); ?></label>

                <div class="btn-group" data-toggle="buttons">
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

                <?php
                if (in_array('SignupErrorInvalidGender', $vars['errors'])) {
                    echo '<div class="alert alert-danger">' . $words->get('SignupErrorInvalidGender') . '</div>';
                }
                ?>
            </div>
            <div class="form-row mb-2">
                <div class="col-12 col-md-9 offset-md-3">
                    <div class="o-checkbox">
                        <input name="HideGender" value="Yes" type="checkbox" class="o-checkbox__input"
                               id='HideGender' <?= ((isset($vars['HideGender']) && $vars['HideGender'] == "Yes") ? ' checked="checked"' : ''); ?>>
                        <label for="HideGender" class="o-checkbox__label"><?= $words->get("Hidden"); ?></label>
                    </div>
                </div>
            </div>
            <div class="form-row mb-2">
                <label for="Location" class="col-12 col-md-3 col-form-label"><?= $words->get('Location') ?></label>

                <div class="col-12 col-md-9">
                    <div>
                        <i class="fa fa-3x fa-map-marker-alt float-left mr-2"></i>
                        <div class="float-left mr-2"><span class="font-weight-bold"><?= $member->city ?></span>
                            <br>
                            <?= $member->region ?>, <?= $member->country ?>
                        </div>
                        <a href="/members/<?= $member->Username ?>/location"
                           class="btn btn-outline-primary float-left"><?= $words->get('UpdateMyLocation') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
