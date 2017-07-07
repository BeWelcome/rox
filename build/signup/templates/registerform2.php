<div class="card card-block w-100">
    <form method="post" action="<?php echo $baseuri . 'signup/3' ?>" name="signup" id="user-register-form" class="form"
          role="form" novalidate>
        <?= $callback_tag ?>
        <input type="hidden" name="javascriptactive" value="false"/>
        <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<span class="alert alert-danger">' . $errors['inserror'] . '</span>';
        }
        ?>

        <!-- Do we need this DIV? -->
        <div class="row invisible d-none">
            <label for="sweet"><?php echo $words->get('SignupSweet'); ?></label>
            <input type="text" class="form-control" id="sweet" name="sweet"
                   placeholder="<?php echo $words->get('SignupSweet'); ?>" value="" title="Leave free of content"/>
        </div>

        <div class="d-flex flex-row">
            <div class="d-block mr-3 pr-3">

                <h4 class="text-center mb-2">Step 2/4</h4>

                <div class="progress mb-2">
                    <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 50%;"
                         aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"><span class="white">50%</span></div>
                </div>

                <div class="h4 text-center d-none d-md-block mt-1">
                    <div class="my-3"><i class="fa fa-user"></i><br><?php echo $words->get('LoginInformation'); ?></div>
                    <div class="my-3"><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                    <div class="my-3 text-muted"><i
                                class="fa fa-map-marker"></i><br><?php echo $words->get('Location'); ?></div>
                    <div class="my-3 text-muted"><i
                                class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?></div>
                </div>

            </div>

            <div class="d-block w-50">
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
                <div class="form-group has-feedback">
                    <legend class="m-0"><?php echo $words->get('FirstName'); ?></legend>
                    <label for="register-firstname" class="sr-only"><?php echo $words->get('FirstName'); ?></label>
                    <div class="d-flex">
                        <input type="text" required minlength="2" class="form-control" name="firstname"
                               id="register-firstname" placeholder="<?php echo $words->get('FirstName'); ?>"
                            <?php
                            echo isset($vars['firstname']) ? 'value="' . htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                        <button type="button" class="btn btn-primary ml-1" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('subline_firstname')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                    </div>
                    <span class="text-muted small"></span>
                    <?php
                    if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
                        echo '<span class="error">' . $words->get('SignupErrorFullNameRequired') . '</span>';
                    }
                    ?>
                </div>


                <!-- Second name -->
                <div class="form-group">
                    <legend class="m-0"><?php echo $words->get('SignupSecondNameOptional'); ?></legend>
                    <label for="secondname"
                           class="sr-only"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
                    <div class="d-flex">
                        <input type="text" minlength="2" class="form-control" name="secondname" id="secondname"
                               placeholder="<?php echo $words->get('SignupSecondNameOptional'); ?>"
                            <?php
                            echo isset($vars['secondname']) ? 'value="' . htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                    </div>
                </div>

                <!-- Last name -->
                <div class="form-group has-feedback">
                    <legend class="m-0"><?php echo $words->get('LastName'); ?></legend>
                    <label for="lastname" class="sr-only"><?php echo $words->get('LastName'); ?></label>
                    <div class="d-flex">
                        <input type="text" minlength="2" required class="form-control" name="lastname" id="lastname"
                               placeholder="<?php echo $words->get('LastName'); ?>"
                            <?php
                            echo isset($vars['lastname']) ? 'value="' . htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?> />
                        <button type="button" class="btn btn-primary ml-1" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('subline_lastname')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                    </div>
                    <span class="text-muted small"></span>
                </div>

                <!-- Mother tongues -->
                <div class="form-group has-feedback pt-3">
                    <?php
                    $motherTongue = -1;
                    if (isset($vars['mothertongue'])) {
                        $motherTongue = $vars['mothertongue'];
                    }
                    ?>
                    <legend class="m-0"><?php echo $words->get('LanguageLevel_MotherLanguage'); ?></legend>
                    <label for="mothertongue"
                           class="sr-only"><?php echo $words->get('LanguageLevel_MotherLanguage'); ?></label>
                    <div class="d-flex">
                        <select required class="select2 form-control" name="mothertongue" id="mothertongue"
                                data-placeholder="<?= $words->getBuffered('SignupSelectMotherTongue') ?>">
                            <option></option>
                            <option value="-1"></option>
                            <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                                <?= $this->getAllLanguages(true, $motherTongue); ?>
                            </optgroup>
                            <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                                <?= $this->getAllLanguages(false, $motherTongue); ?>
                            </optgroup>
                        </select>
                        <button type="button" class="btn btn-primary ml-1" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="<?= htmlentities($words->get('subline_mothertongue')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                    </div>
                    <span class="text-muted small"></span>
                </div>


                <!-- Date of birth-->
                <div class="form-group has-feedback pt-3">
                    <legend class="m-0"><?php echo $words->get('SignupBirthDate'); ?></legend>
                    <label for="mothertongue" class="sr-only"><?php echo $words->get('SignupBirthDate'); ?></label>
                    <div class="d-flex">
                        <select required id="BirthDate" name="birthyear" class="form-control custom-select">
                            <option value=""><?php echo $words->getSilent('SignupBirthYear'); ?></option>
                            <?php echo $birthYearOptions; ?>
                        </select>
                        <select required name="birthmonth" class="form-control custom-select">
                            <option value=""><?php echo $words->getSilent('SignupBirthMonth'); ?></option>
                            <?php for ($i = 1; $i <= 12; $i++) { ?>
                                <option value="<?php echo $i; ?>"<?php
                                if (isset($vars['birthmonth']) && $vars['birthmonth'] == $i) {
                                    echo ' selected="selected"';
                                }
                                ?>><?php echo $i; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <select required name="birthday" class="form-control custom-select">
                            <option value=""><?php echo $words->getSilent('SignupBirthDay'); ?></option>
                            <?php for ($i = 1; $i <= 31; $i++) { ?>
                                <option value="<?php echo $i; ?>"<?php
                                if (isset($vars['birthday']) && $vars['birthday'] == $i) {
                                    echo ' selected="selected"';
                                }
                                ?>><?php echo $i; ?>
                                </option>
                            <?php } ?>
                            <?php echo $words->flushBuffer(); ?>
                        </select>
                        <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body"
                                data-toggle="popover" data-placement="right"
                                data-content="Do you consider yourself female, male, none or something else?">
                            <i class="fa fa-question"></i>
                        </button>
                    </div>
                    <span class="text-muted small"></span>
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
                    <legend class="m-0"><?php echo $words->get('SignupGender'); ?></legend>
                    <label for="mothertongue" class="sr-only"><?php echo $words->get('SignupGender'); ?></label>

                    <div class="d-flex" data-toggle="buttons">

                        <label class="btn btn-primary w-100 mr-1 <?php
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
                        <label class="btn btn-primary w-100 mx-1 <?php
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
                        <label class="btn btn-primary w-100 ml-1 <?php
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
                    <span class="text-muted small"></span>
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

            <div class="ml-auto">
                <!-- Information on data use -->
                <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal"
                        data-target="#SignupIntroduction">
                    <i class="fa fa-exclamation-circle"></i> <?php // echo $words->get('SignupIntroductionTitle'); ?>
                    Data visibility
                </button>
            </div>
    </form>
</div>

<script type="text/javascript">
    jQuery(".select2").select2(); // {no_results_text: "<?= htmlentities($words->getSilent('SignupNoLanguageFound'), ENT_COMPAT); ?>"});
</script>