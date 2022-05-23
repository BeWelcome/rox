<div class="card card-block w-100">
    <form method="post" name="signup" id="user-register-form" class="form"
          role="form" novalidate>
        <?= $callback_tag ?>
        <input type="hidden" name="javascriptactive" value="false"/>
        <?php
        $errors = $vars['errors'];
        if (in_array('inserror', $errors)) {
            echo '<span class="alert alert-danger">' . $errors['inserror'] . '</span>';
        }
        ?>

        <!-- Do we need this DIV? -->
        <div class="row invisible d-none">
            <label for="sweet"><?php echo $words->get('SignupSweet'); ?></label>
            <input type="text" class="o-input" id="sweet" name="sweet"
                   placeholder="<?php echo $words->get('SignupSweet'); ?>" value="" title=""/>
        </div>

        <div class="row">
            <div class="col-12 col-md-3">

                <h4 class="text-center mb-2"><?= $words->getFormatted('signup.step', 2); ?></h4>

                <div class="progress mb-2">
                    <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 40%;"
                         aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"><span class="white">40%</span></div>
                </div>

                <div class="h4 text-center d-none d-md-block mt-1">
                    <div class="my-3"><i class="fa fa-user"></i><br><a href="signup/1"><?php echo $words->get('LoginInformation'); ?></a></div>
                    <div class="my-3"><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                    <div class="my-3 text-muted"><i
                                class="fa fa-map-marker-alt"></i><br><?php echo $words->get('Location'); ?></div>
                    <div class="my-3 text-muted"><i
                                class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?></div>
                </div>

            </div>

            <div class="col-12 col-md-6">
                <div class="modal fade" id="SignupIntroduction" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo $words->get('Signup'); ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php echo $words->get('SignupIntroduction'); ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal"><?= $words->get('Close'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-muted"><?= $words->get('signup.names.hidden'); ?></div>

                <!-- First Name -->
                <?php $fullnameMissing = in_array('SignupErrorFullNameRequired', $vars['errors']); ?>
                <div class="o-form-group">
                    <label for="register-firstname"><?php echo $words->get('FirstName'); ?></label>
                    <div class="input-group">
                        <input type="text" required minlength="1" class="o-input <?php if ($fullnameMissing) { echo 'is-invalid'; } ?>" name="firstname"
                               id="register-firstname" placeholder="<?php echo $words->get('FirstName'); ?>"
                            <?php
                            echo isset($vars['firstname']) ? 'value="' . htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('subline_firstname')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                        <div class="invalid-feedback"><?= $words->get('SignupErrorFullNameRequired') ?></div>
                    </div>
                </div>


                <!-- Second name -->
                <div class="o-form-group">
                    <label for="secondname"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
                    <div class="d-flex">
                        <input type="text" minlength="1" class="o-input" name="secondname" id="secondname"
                               placeholder="<?php echo $words->get('SignupSecondNameOptional'); ?>"
                            <?php
                            echo isset($vars['secondname']) ? 'value="' . htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                    </div>
                </div>

                <!-- Last name -->
                <div class="o-form-group">
                    <label for="lastname"><?php echo $words->get('LastName'); ?></label>
                    <div class="input-group">
                        <input type="text" minlength="1" required class="o-input <?php if ($fullnameMissing) { echo 'is-invalid'; } ?>" name="lastname" id="lastname"
                               placeholder="<?php echo $words->get('LastName'); ?>"
                            <?php
                            echo isset($vars['lastname']) ? 'value="' . htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('subline_lastname')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                        <div class="invalid-feedback"><?= $words->get('SignupErrorFullNameRequired') ?></div>
                    </div>
                </div>

                <!-- Mother tongues -->
                <div class="o-form-group">
                    <?php
                    $motherTongueError = in_array('SignupErrorNoMotherTongue', $errors);
                    $motherTongue = "";
                    if (isset($vars['mothertongue'])) {
                        $motherTongue = $vars['mothertongue'];
                    }
                    ?>
                    <label for="mothertongue"><?php echo $words->get('LanguageLevel_MotherLanguage'); ?></label>
                    <div class="input-group">
                        <select required class="o-input <?= ($motherTongueError) ? "is-invalid" : "" ?>" name="mothertongue" id="mothertongue"
                                data-placeholder="<?= $words->getBuffered('SignupSelectMotherTongue') ?>">
                            <option></option>
                            <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                                <?= $this->getAllLanguages(true, $motherTongue); ?>
                            </optgroup>
                            <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                                <?= $this->getAllLanguages(false, $motherTongue); ?>
                            </optgroup>
                        </select>
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('subline_mothertongue')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                        <div class="invalid-feedback" id="mothertongue-invalid"><?= $words->get('signup.error.mothertongue') ?></div>
                    </div>
                </div>

                <!-- Date of birth-->
                <?php $birthdateError = (in_array('SignupErrorBirthDate', $vars['errors'])) ||
                    (in_array('SignupErrorBirthDateToLow', $vars['errors'])) ||
                    (in_array('SignupErrorBirthDateToHigh', $vars['errors'])); ?>
                <div class="o-form-group">
                    <label for="birthdate"><?php echo $words->get('SignupBirthDate'); ?></label>
                    <div class="input-group date" id="datetimepicker1" data-target-input="nearest">
                        <div class="input-group-prepend" data-target="#datetimepicker1"
                                data-toggle="datetimepicker"><div class="input-group-text bg-primary white"><i class="far fa-calendar fa-fw"></i></div></div>
                        <input type="text" class="o-input datetimepicker-input <?php if ($birthdateError) { echo 'is-invalid'; }?>" data-target="#datetimepicker1" id="birthdate"
                               name="birthdate" data-toggle="datetimepicker" <?php
                        echo isset($vars['birthdate']) ? 'value="' . htmlentities($vars['birthdate'], ENT_COMPAT, 'utf-8') . '" ' : '';
                        ?>/>
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('signup.help.birthdate')); ?>">
                            <i class="fa fa-question"></i>
                        </button>
                    <?php
                    if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                        echo '<div class="invalid-feedback">' . $words->get('SignupErrorBirthDate') . '</div>';
                    }
                    if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                        echo '<div class="invalid-feedback">' . $words->getFormatted('SignupErrorBirthDateToLow', SignupModel::YOUNGEST_MEMBER) . '</div>';
                    }
                    if (in_array('SignupErrorBirthDateToHigh', $vars['errors'])) {
                        echo '<div class="invalid-feedback">' . $words->getFormatted('SignupErrorBirthDateToHigh'), SignupModel::OLDEST_MEMBER . '</div>';
                    }
                    ?>
                    </div>
                </div>

                <!-- Gender-->
                <?php $genderError = in_array('SignupErrorProvideGender', $vars['errors']); ?>
                <div class="o-form-group">
                    <span class="d-block o-input-label"><?php echo $words->get('Gender'); ?></span>
                    <div class="o-input <?php if ($genderError) { echo "is-invalid"; } ?> d-none"></div>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-outline-primary" for="female" <?php
                    if (isset($vars['gender']) && $vars['gender'] == 'female') {
                        echo ' active"';
                    }
                    ?>">
                        <input type="radio" id="female" required name="gender" value="female"<?php
                        if (isset($vars['gender']) && $vars['gender'] == 'female') {
                            echo ' checked="checked"';
                        }
                        ?> ><?php echo $words->get('female'); ?>
                    </label>
                    <label class="btn btn-outline-primary for="male" <?php
                    if (isset($vars['gender']) && $vars['gender'] == 'male') {
                        echo ' active"';
                    }
                    ?>">
                        <input type="radio" id="male" required name="gender" value="male"<?php
                        if (isset($vars['gender']) && $vars['gender'] == 'male') {
                            echo ' checked="checked"';
                        }
                        ?> ><?php echo $words->get('male'); ?>
                    </label>
                    <label class="btn btn-outline-primary for="other" <?php
                    if (isset($vars['gender']) && $vars['gender'] == 'other') {
                        echo ' active"';
                    }
                    ?>">
                        <input type="radio" id="other" required name="gender" value="other"<?php
                        if (isset($vars['gender']) && $vars['gender'] == 'other') {
                            echo ' checked="checked"';
                        }
                        ?> ><?php echo $words->get('GenderOther'); ?>
                    </label>
                    </div>
                    <button type="button" class="btn btn-primary float-right" data-trigger="focus" data-container="body"
                            data-toggle="popover" data-placement="right"
                            data-content="<?= htmlentities($words->get('signup.help.gender')); ?>">
                        <i class="fa fa-question"></i>
                    </button>
                    <?php if ($genderError) {
                        echo '<div class="invalid-feedback">' . $words->get('SignupErrorProvideGender') . '</div>';
                    }
                    ?>

                    <!-- Next button -->
                    <div class="o-form-group pt-3">
                        <div class="d-flex">
                            <button type="submit"
                                    class="o-input btn btn-primary"><?php echo $words->getSilent('NextStep'); ?> <i
                                        class="fa fa-angle-double-right"></i></button>
                            <?php echo $words->flushBuffer(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <!-- Information on data use -->
                <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal"
                        data-target="#SignupIntroduction">
                    <i class="fa fa-exclamation-circle"></i><?= $words->get('signup.data.visibility');?>
                </button>
            </div>
    </form>
</div>
