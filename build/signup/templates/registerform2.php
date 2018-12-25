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
            <input type="text" class="form-control" id="sweet" name="sweet"
                   placeholder="<?php echo $words->get('SignupSweet'); ?>" value="" title="Leave free of content"/>
        </div>

        <div class="row">
            <div class="col-12 col-md-3">

                <h4 class="text-center mb-2">Step 2/5</h4>

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
                <div class="modal fade" id="SignupIntroduction" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title"
                                    id="myModalLabel"><?php echo $words->get('SignupIntroductionTitle'); ?></h4>
                            </div>
                            <div class="modal-body">
                                <?php echo $words->get('SignupIntroduction'); ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mb-3">Please fill out all fields</h3>

                <!-- First Name -->
                <div class="form-group">
                    <label for="register-firstname"><?php echo $words->get('FirstName'); ?></label>
                    <div class="input-group">
                        <input type="text" required minlength="1" class="form-control" name="firstname"
                               id="register-firstname" placeholder="<?php echo $words->get('FirstName'); ?>"
                            <?php
                            echo isset($vars['firstname']) ? 'value="' . htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('subline_firstname')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                        <div class="invalid-feedback">The firstname must at least be 1 characters long</div>
                    </div>
                    <?php
                    if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
                        echo '<span class="error">' . $words->get('SignupErrorFullNameRequired') . '</span>';
                    }
                    ?>
                </div>


                <!-- Second name -->
                <div class="form-group">
                    <label for="secondname"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
                    <div class="d-flex">
                        <input type="text" minlength="1" class="form-control" name="secondname" id="secondname"
                               placeholder="<?php echo $words->get('SignupSecondNameOptional'); ?>"
                            <?php
                            echo isset($vars['secondname']) ? 'value="' . htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                    </div>
                </div>

                <!-- Last name -->
                <div class="form-group">
                    <label for="lastname"><?php echo $words->get('LastName'); ?></label>
                    <div class="input-group">
                        <input type="text" minlength="1" required class="form-control" name="lastname" id="lastname"
                               placeholder="<?php echo $words->get('LastName'); ?>"
                            <?php
                            echo isset($vars['lastname']) ? 'value="' . htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('subline_lastname')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                        <div class="invalid-feedback">The lastname must at least be 1 characters long</div>
                    </div>
                </div>

                <!-- Mother tongues -->
                <div class="form-group pt-3">
                    <?php
                    $motherTongueError = in_array('SignupErrorNoMotherTongue', $errors);
                    $motherTongue = "";
                    if (isset($vars['mothertongue'])) {
                        $motherTongue = $vars['mothertongue'];
                    }
                    ?>
                    <label for="mothertongue"><?php echo $words->get('LanguageLevel_MotherLanguage'); ?></label>
                    <div class="input-group <?= ($motherTongueError) ? "was-validated" : "" ?>">
                        <select required class="form-control" name="mothertongue" id="mothertongue"
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
                        <?php
                        if ($motherTongueError) { ?>
                            <div class="invalid-feedback" id="mothertongue-invalid"><?= $words->get('signup.error.mothertongue') ?></div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Date of birth-->
                <div class="form-group pt-3">
                    <label for="birthdate"><?php echo $words->get('SignupBirthDate'); ?></label>
                    <div class="input-group date" id="datetimepicker1" data-target-input="nearest">
                        <div class="input-group-prepend" data-target="#datetimepicker1"
                                data-toggle="datetimepicker"><i class="input-group-text bg-primary white far fa-calendar"></i></div>
                        <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" id="birthdate"
                               name="birthdate"/>
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="To use BeWelcome you need to be at least 18 years old.<br><br>Your birth date is never shown on the site. You might also decide to hide your age on your profile.">
                            <i class="fa fa-question"></i>
                        </button>
                    </div>
                    <?php
                    if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                        echo '<span class="alert alert-danger">' . $words->get('SignupErrorBirthDate') . '</span>';
                    }
                    if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                        echo '<span class="alert alert-danger">' . $words->getFormatted('SignupErrorBirthDateToLow', SignupModel::YOUNGEST_MEMBER) . '</span>';
                    }
                    ?>
                </div>

                <!-- Gender-->
                <div class="form-group has-feedback pt-3">
                    <span class="d-block form-control-label"><?php echo $words->get('Gender'); ?></span>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-outline-primary" <?php
                    if (isset($vars['gender']) && $vars['gender'] == 'female') {
                        echo ' active"';
                    }
                    ?>">
                        <input type="radio" id="gender" required name="gender" value="female"<?php
                        if (isset($vars['gender']) && $vars['gender'] == 'female') {
                            echo ' checked="checked"';
                        }
                        ?> ><?php echo $words->get('female'); ?>
                    </label>
                    <label class="btn btn-outline-primary <?php
                    if (isset($vars['gender']) && $vars['gender'] == 'male') {
                        echo ' active"';
                    }
                    ?>">
                        <input type="radio" required name="gender" value="male"<?php
                        if (isset($vars['gender']) && $vars['gender'] == 'male') {
                            echo ' checked="checked"';
                        }
                        ?> ><?php echo $words->get('male'); ?>
                    </label>
                    <label class="btn btn-outline-primary <?php
                    if (isset($vars['gender']) && $vars['gender'] == 'other') {
                        echo ' active"';
                    }
                    ?>">
                        <input type="radio" required name="gender" value="other"<?php
                        if (isset($vars['gender']) && $vars['gender'] == 'other') {
                            echo ' checked="checked"';
                        }
                        ?> ><?php echo $words->get('GenderOther'); ?>
                    </label>
                    </div>
                    <button type="button" class="btn btn-primary float-right" data-trigger="focus" data-container="body"
                            data-toggle="popover" data-placement="right"
                            data-content="Do you consider yourself female, male, none or something else?">
                        <i class="fa fa-question"></i>
                    </button>
                    <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
                        echo '<span class="help-block alert alert-danger">' . $words->get('SignupErrorProvideGender') . '</span>';
                    }
                    ?>

                    <!-- Next button -->
                    <div class="form-group pt-3">
                        <div class="d-flex">
                            <button type="submit"
                                    class="form-control btn btn-primary"><?php echo $words->getSilent('NextStep'); ?> <i
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
                    <i class="fa fa-exclamation-circle"></i> <?php // echo $words->get('SignupIntroductionTitle'); ?>
                    Data visibility
                </button>
            </div>
    </form>
</div>
<script>
    $( document ).ready(function() {
        // Rough calculation of 18 years ago
        let maxDate = new Date();
        maxDate.setDate(maxDate.getDate() - 18 * 365 - 4);
        $("#datetimepicker1").datetimepicker({
            format: 'YYYY-MM-DD',
            maxDate: maxDate
        });
    });
</script>