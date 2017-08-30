<div class="card card-block w-100">
<form method="post" novalidate action="<?php echo $baseuri.'signup/2'?>" name="signup" id="user-register-form" class="form" role="form">
<?=$callback_tag ?>
<?php
    if (in_array('inserror', $vars['errors'])) {
        echo '<span class="alert alert-danger">'.$errors['inserror'].'</span>';
    }
?>
    <div class="row">
        <div class="col-12 col-md-3">

            <h4 class="text-center mb-2">Step 1/5</h4>

            <div class="progress mb-2">
                <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"><span class="white">20%</span></div>
            </div>

            <div class="h4 text-center d-none d-md-block mt-1">
                <div class="my-3"><i class="fa fa-user"></i><br><?php echo $words->get('LoginInformation'); ?></div>
                <div class="my-3 text-muted"><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                <div class="my-3 text-muted"><i class="fa fa-map-marker"></i><br><?php echo $words->get('Location'); ?></div>
                <div class="my-3 text-muted"><i class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?></div>
            </div>

        </div>

        <div class="col-12 col-md-6">
            <div class="modal fade" id="SignupIntroduction" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="myModalLabel"><?php echo $words->get('SignupIntroductionTitle'); ?></h4>
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

            <!-- Username -->
            <div class="form-group has-feedback">
                <legend class="m-0"><?php echo $words->get('SignupUsername'); ?></legend>
                <label for="register-username" class="sr-only"><?php echo $words->get('SignupUsername'); ?></label>
                <div class="d-flex">
                <input type="text" required class="form-control" name="username"
                       minlength="4"
                       maxlength="20"
                       data-validation-ajax-ajax="/signup/checkhandle"
                       placeholder="<?php echo htmlentities($words->get('SignupUsername')); ?>" id="register-username"
                    <?php
                    echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
                    ?> >
                <button type="button" class="btn btn-primary ml-1" data-container="body" data-toggle="popover" data-placement="right" data-content="<?=htmlentities($words->get('subline_username'))?>">
                    <i class="fa fa-question"></i>
                </button>
                </div>
                <span class="text-muted small red"></span>
            </div>

            <!-- Password -->
            <div class="form-group has-feedback mb-0">
                <legend class="m-0"><?php echo $words->get('SignupPassword'); ?></legend>
                <label for="register-password" class="sr-only"><?php echo $words->get('SignupPassword'); ?></label>
                <div class="d-flex">
                    <input type="password" required class="form-control" id="register-password" name="password" placeholder="<?php echo $words->get('SignupPassword'); ?>"
                           minlength="6"
                           maxlength="4096"
                           data-validation-minlength-message="Too short"
                        <?php
                        echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
                        ?> >
                    <button type="button" class="btn btn-primary ml-1" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="Please choose a strong password. You may use all letters (a-z A-Z), numbers (0-9) and some special characters (# % ! = + - _)">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
                <span class="text-muted small red"></span>
            </div>

            <!-- Confirm password -->
            <div class="form-group has-feedback mt-1">
                <legend class="m-0"><?php echo $words->get('SignupCheckPassword'); ?></legend>
                <label for="register-passwordcheck" class="sr-only"><?php echo $words->get('SignupCheckPassword'); ?></label>
                <div class="d-flex">
                    <input type="password" class="form-control" id="register-passwordcheck" name="passwordcheck" placeholder="<?php echo $words->get('SignupCheckPassword'); ?>"
                           data-validation-matches-match="password"
                           data-validation-matches-message="Must match password entered above"
                        <?php
                        echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
                        ?> >
                </div>
                <span class="text-muted small red"></span>
            </div>

            <!-- E-mail -->
            <div class="form-group has-feedback mb-0">
                <legend class="m-0"><?php echo $words->get('SignupEmail'); ?></legend>
                <label for="register-email" class="sr-only"><?php echo $words->get('SignupEmail'); ?></label>
                <div class="d-flex">
                    <input type="email" required class="form-control" id="register-email" name="email" placeholder="<?php echo $words->get('SignupEmail'); ?>"
                           data-validation-ajax-ajax="/signup/checkemail"
                        <?php
                        echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : '';
                        ?> />
                    <button type="button" class="btn btn-primary ml-1" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="Your e-mail address is most important: we will forward the messages and request to this e-mail address">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
                <span class="text-muted small red"></span>
            </div>

            <!-- confirm E-mail -->
            <div class="form-group has-feedback mt-1">
                <legend class="m-0"><?php echo $words->get('SignupCheckEmail'); ?></legend>
                <label for="register-emailcheck" class="sr-only"><?php echo $words->get('SignupCheckEmail'); ?></label>
                <div class="d-flex">
                    <input type="email"
                           data-validation-matches-match="email"
                           data-validation-matches-message="Must match email address entered above"
                           class="form-control" id="register-emailcheck" name="emailcheck" placeholder="<?php echo $words->get('SignupCheckEmail'); ?>"
                        <?php
                        echo isset($vars['emailcheck']) ? 'value="'.$vars['emailcheck'].'" ' : '';
                        ?> />
                </div>
                <span class="text-muted small red"></span>
            </div>

            <!-- Accommodation -->

            <fieldset class="form-group">
                <legend class="m-0">
                    <label for="accommodation" class="m-0"><h4><?php echo $words->get('Accommodation'); ?></h4></label>
                    <button type="button" class="btn btn-primary ml-1 float-right" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="Are you able to provide accommodation?">
                        <i class="fa fa-question"></i>
                    </button>
                </legend>

                <div class="form-check">
                    <label>
                        <input type="radio" name="accommodation" id="Anytime" value="anytime" <?php
                        if (isset($vars['accommodation']) && $vars['accommodation'] == 'anytime') { echo ' checked="checked"'; } ?>>
                        <img src="/images/icons/anytime.png">
                        <?php echo $words->get('Accomodation_anytime'); ?>
                    </label>
                </div>
                <div class="form-check">
                    <label>
                        <input type="radio" name="accommodation" id="dependonrequest" value="dependonrequest" <?php
                        if (isset($vars['accommodation']) && $vars['accommodation'] == 'dependonrequest') { echo ' checked="checked"'; } ?>>
                        <img src="/images/icons/dependonrequest.png">
                        <?php echo $words->get('Accomodation_dependonrequest'); ?>
                    </label>
                </div>
                <div class="form-check">
                    <label>
                        <input type="radio" name="accommodation" id="neverask" value="neverask" <?php
                        if (isset($vars['accommodation']) && $vars['accommodation'] == 'neverask') { echo ' checked="checked"'; } ?>>
                        <img src="/images/icons/neverask.png">
                        <?php echo $words->get('Accomodation_neverask'); ?>
                    </label>
                </div>
                <?php if (in_array('SignupErrorProvideAccommodation', $vars['errors'])) {
                    echo '<div class="error">'.$words->get('SignupErrorProvideAccommodation').'</div>';
                }
                ?>
                <span class="text-muted small red"></span>
            </fieldset>

            <!-- Next button -->
            <div class="form-group">
                <div class="d-flex">
                    <button type="submit" class="form-control btn btn-primary"><?php echo $words->getSilent('NextStep'); ?> <i class="fa fa-angle-double-right"></i></button>
                    <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <!-- Information on data use -->
            <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-target="#SignupIntroduction">
                <i class="fa fa-exclamation-circle"></i> <?php // echo $words->get('SignupIntroductionTitle'); ?>Data visibility
            </button>
        </div>

    </div>
</form>
</div>