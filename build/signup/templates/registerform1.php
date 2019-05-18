
<div class="card card-block w-100">
    <form method="post" novalidate name="signup" id="user-register-form" class="needs-validation" role="form" autocomplete="off">
        <?=$callback_tag ?>
        <?php
        $errors = $vars['errors'];
        if (in_array('inserror', $errors)) {
            echo '<span class="alert alert-danger">'.$errors['inserror'].'</span>';
        }
        ?>
        <div class="row">
            <div class="col-12 col-md-3">

                <h4 class="text-center mb-2"><?= $words->getFormatted('signup.step', 1); ?></h4>

                <div class="progress mb-2">
                    <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"><span class="white">20%</span></div>
                </div>

                <div class="h4 text-center d-none d-md-block mt-1">
                    <div class="my-3"><i class="fa fa-user"></i><br><?php echo $words->get('LoginInformation'); ?></div>
                    <div class="my-3 text-muted"><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                    <div class="my-3 text-muted"><i class="fa fa-map-marker-alt"></i><br><?php echo $words->get('Location'); ?></div>
                    <div class="my-3 text-muted"><i class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?></div>
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

                <!-- Username -->
                <div class="form-group">
                    <?php $usernameError = in_array('SignupErrorUsernameAlreadyTaken', $errors); ?>
                    <label for="register-username" ><?php echo $words->get('SignupUsername'); ?></label>
                    <div class="input-group">
                        <input type="text" required class="form-control <?php if ($usernameError) { echo 'is-invalid'; }?>" name="username"
                               minlength="4"
                               maxlength="20"
                               pattern="[A-Za-z](?!.*[-_.][-_.])[A-Za-z0-9-._]{2,18}[A-Za-z0-9]"
                               placeholder="<?php echo htmlentities($words->get('SignupUsername')); ?>" id="register-username"
                            <?php
                            echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
                            ?>
                        >
                        <button type="button" class="input-group-append btn btn-primary" data-container="body" data-toggle="popover" data-placement="right" data-content="<?= htmlentities($words->getSilent('subline_username')) ?>">
                            <i class="fa fa-question"></i>
                        </button>
                        <div class="valid-feedback"><?= $words->get('signup.username.looks.good') ?></div>
                        <?php
                            if ($usernameError) { ?>
                                <div class="invalid-feedback" id="username-invalid"><?= $words->get('signup.error.username.taken') ?></div>
                         <?php } else { ?>
                            <div class="invalid-feedback" id="username-invalid"><?= $words->get('signup.error.username') ?></div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group mb-0">
                    <label for="register-password"><?php echo $words->get('SignupPassword'); ?></label>
                    <div class="input-group">
                        <input type="password" required class="form-control" id="register-password" name="password" placeholder="<?php echo $words->get('SignupPassword'); ?>"
                               minlength="6"
                               maxlength="4096"
                            <?php
                            echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
                            ?> >
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="<?= $words->get('signup.popover.password'); ?>">
                            <i class="fa fa-question"></i>
                        </button>
                        <div class="valid-feedback"><?= $words->getSilent('signup.password.looks.good') ?></div>
                        <div class="invalid-feedback" id="username-invalid"><?= $words->getSilent('signup.password.too.short') ?></div>
                    </div>
                </div>

                <!-- Confirm password -->
                <div class="form-group mt-1">
                    <label for="register-passwordcheck"><?php echo $words->get('SignupCheckPassword'); ?></label>
                    <input type="password" class="form-control" id="register-passwordcheck" name="passwordcheck" placeholder="<?php echo $words->get('SignupCheckPassword'); ?>"
                        <?php
                        echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
                        ?> >
                    <div class="valid-feedback"><?= $words->getSilent('signup.password.confirm.match') ?></div>
                    <div class="invalid-feedback"><?= $words->getSilent('signup.password.confirm.mismatch') ?></div>
                </div>

                <!-- E-mail -->
                <div class="form-group mb-0">
                    <label for="register-email"><?php echo $words->get('SignupEmail'); ?></label>
                    <div class="input-group">
                        <input type="email" required class="form-control" id="register-email" name="email" placeholder="<?php echo $words->get('SignupEmail'); ?>"
                               data-validation-ajax-ajax="/signup/checkemail"
                            <?php
                            echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : '';
                            ?> />
                        <button type="button" class="input-group-append btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="<?= $words->get('signup.help.email'); ?>">
                            <i class="fa fa-question"></i>
                        </button>
                        <div class="valid-feedback"><?= $words->getSilent('signup.email.wellformed'); ?></div>
                        <div class="invalid-feedback" id="email-invalid"><?= $words->getSilent('signup.error.email'); ?></div>
                    </div>
                </div>

                <!-- confirm E-mail -->
                <div class="form-group mt-1">
                    <label for="register-emailcheck"><?php echo $words->get('SignupCheckEmail'); ?></label>
                    <input type="email"
                           class="form-control" id="register-emailcheck" name="emailcheck" placeholder="<?php echo $words->get('SignupCheckEmail'); ?>"
                        <?php
                        echo isset($vars['emailcheck']) ? 'value="'.$vars['emailcheck'].'" ' : '';
                        ?> />
                    <div class="valid-feedback"><?= $words->getSilent('signup.email.confirm.match') ?></div>
                    <div class="invalid-feedback"><?= $words->getSilent('signup.email.confirm.mismatch') ?></div>
                </div>

                <!-- Accommodation -->

                <div class="form-group">
                    <span class="form-control-label"><?php echo $words->get('Accommodation'); ?></span>
                    <button type="button" class="btn btn-primary float-right" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="<?= $words->get('signup.help.accommodation'); ?>">
                        <i class="fa fa-question"></i>
                    </button>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" required name="accommodation" id="anytime" value="anytime" <?php
                        if (isset($vars['accommodation']) && $vars['accommodation'] == 'anytime') { echo ' checked="checked"'; } ?>>
                        <label class="form-check-label" for="anytime">
                            <img src="/images/icons/anytime.png">
                            <?php echo $words->get('Accomodation_anytime'); ?>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" required name="accommodation" id="dependonrequest" value="dependonrequest" <?php
                        if (isset($vars['accommodation']) && $vars['accommodation'] == 'dependonrequest') { echo ' checked="checked"'; } ?>>
                        <label class="form-check-label" for="dependonrequest">
                            <img src="/images/icons/dependonrequest.png">
                            <?php echo $words->get('Accomodation_dependonrequest'); ?>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" required name="accommodation" id="neverask" value="neverask" <?php
                        if (isset($vars['accommodation']) && $vars['accommodation'] == 'neverask') { echo ' checked="checked"'; } ?>>
                        <label class="form-check-label" for="neverask">
                            <img src="/images/icons/neverask.png">
                            <?php echo $words->get('Accomodation_neverask'); ?>
                        </label>
                    </div>
                    <div class="invalid-feedback">Select one of the above.</div>
                    <?php if (in_array('SignupErrorProvideAccommodation', $vars['errors'])) {
                        echo '<div class="error">'.$words->get('SignupErrorProvideAccommodation').'</div>';
                    }
                    ?>
                </div>
                <!-- Next button -->
                <div class="form-group">
                        <button type="submit" class="btn btn-primary w-100"><?php echo $words->getSilent('NextStep'); ?> <i class="fa fa-angle-double-right"></i></button>
                        <?php echo $words->flushBuffer(); ?>
                    </div>
            </div>

            <div class="col-12 col-md-3">
                <!-- Information on data use -->
                <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-target="#SignupIntroduction">
                    <i class="fa fa-exclamation-circle"></i> <?php // echo $words->get('SignupIntroductionTitle'); ?><?= $words->get('signup.data.visibility'); ?>
                </button>
            </div>

        </div>
    </form>
</div>
<script>
    $( "#register-username" ).change(function() {
        // force validity check
        let username = $( "#register-username" );
        username[0].setCustomValidity('');
        var valid = username[0].checkValidity();
        if (valid) {
            // Check if username is not in use
            $.get('/signup/checkhandle', {'field': '', 'value': $(this).val()}, function(data) {
                let username = $( "#register-username" );
                data = JSON.parse(data);
                // only apply if username hasn't changed since
                if (data.value === username.val()) {
                    if (data.valid === true) {
                        username[0].setCustomValidity('');
                        $("#username-invalid").html("<?= $words->getSilent("signup.error.username"); ?>");
                        $(username).addClass('is-valid').removeClass('is-invalid');
                    } else {
                        username[0].setCustomValidity("<?= $words->getSilent("signup.error.username.taken"); ?>");
                        $("#username-invalid").html("<?= $words->getSilent("signup.error.username.taken"); ?>");
                        $(username).addClass('is-invalid').removeClass('is-valid');
                    }
                }
            });
        } else {
            username[0].setCustomValidity('Format wrong');
            $("#username-invalid").html("<?= $words->getSilent("signup.error.username"); ?>");
            $(username).addClass('is-invalid').removeClass('is-valid');
        }
    });

    let passwordCheck = $( "#register-passwordcheck" );
    $( "#register-password" ).change(function() {
        let password = $( "#register-passwordcheck" );
        password[0].setCustomValidity('');
        var valid = password[0].checkValidity();
        if (valid) {
            $(password).addClass('is-valid').removeClass('is-invalid');
            $(passwordCheck).change();
        } else {
            $(password).addClass('is-invalid').removeClass('is-valid');
        }
    });

    $( passwordCheck ).change(function() {
        passwordCheck[0].setCustomValidity('');
        var valid = passwordCheck[0].checkValidity();
        if (valid) {
            $(passwordCheck).addClass('is-valid').removeClass('is-invalid');
        } else {
            $(passwordCheck).addClass('is-invalid').removeClass('is-valid');
        }
    });

    $( "#register-email" ).change(function() {
        let email = $( "#register-email" );
        email[0].setCustomValidity('');
        var valid = email[0].checkValidity();
        if (valid) {
            email[0].setCustomValidity('Checking email');
            $("#email-invalid").html("<?= $words->getSilent('signup.email.checking'); ?>");
            $(email).addClass('is-invalid').removeClass('is-valid');
            // Check if email is not in use
            $.get('/signup/checkemail', {'field': '', 'value': $(this).val()}, function(data) {
                let email = $( "#register-email" );
                data = JSON.parse(data);
                // only apply if email hasn't changed since
                if (data.value === email.val()) {
                    if (data.valid === true) {
                        email[0].setCustomValidity('');
                        $(email).addClass('is-valid').removeClass('is-invalid');
                        $("#email-invalid").html("<?= $words->getSilent('signup.error.email'); ?>");
                        $("#register-emailcheck").change();
                    } else {
                        email[0].setCustomValidity("<?= $words->getSilent('signup.error.email.taken'); ?>");
                        $(email).addClass('is-invalid').removeClass('is-valid');
                        $("#email-invalid").html("<?= $words->getSilent('signup.error.email.taken'); ?>");
                    }
                } else {
                    $(email).addClass('is-invalid').removeClass('is-valid');
                }
            });
        } else {
            $("#email-invalid").html("<?= $words->getSilent('signup.error.email'); ?>");
            $(email).addClass('is-invalid').removeClass('is-valid');
        }
    });

    $( "#register-emailcheck" ).change(function() {
        let check = $( "#register-emailcheck" );
        check[0].setCustomValidity('');
        var valid = check[0].checkValidity();
        if (valid) {
            let password = $("#register-email");
            if (check.val() === password.val()) {
                $(check).addClass('is-valid').removeClass('is-invalid');
            } else {
                $(check).addClass('is-invalid').removeClass('is-valid');
            }
        } else {
            $(check).addClass('is-invalid').removeClass('is-valid');
        }
    });

    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function(){
        'use strict';

        $(document).ready(function() {
            var form = $('#user-register-form');
            $(form).submit(function (e) {
                if (this.checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });
        });
    })();
</script>
