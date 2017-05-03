<?php

/*
Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.
*/

/*
 * REGISTER FORM TEMPLATE
 */
 ?>
<div class="card card-block">
<form method="post" novalidate action="<?php echo $baseuri.'signup/2'?>" name="signup" id="user-register-form" class="form" role="form">
<?=$callback_tag ?>
<?php
    if (in_array('inserror', $vars['errors'])) {
        echo '<span class="alert alert-danger">'.$errors['inserror'].'</span>';
    }
?>
    <div class="row m-y-1">
        <div class="col-md-4">

            <h4 class="text-center">Step 1/4</h4>

            <progress class="progress progress-striped progress-success" value="25" max="100">
                <div class="progress">
                    <span class="progress-bar" style="width: 25%;">25%</span>
                </div>
            </progress>


            <div class="h4 text-center hidden-md-down mt-1">
                <div><i class="fa fa-user"></i><br><?php echo $words->get('LoginInformation'); ?></div>
                <div class="text-muted m-y-2"><i class="fa fa-angle-down"></i></div>
                <div class="text-muted"><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                <div class="text-muted m-y-2"><i class="fa fa-angle-down"></i></div>
                <div class="text-muted"><i class="fa fa-map-marker"></i><br><?php echo $words->get('Location'); ?></div>
                <div class="text-muted m-y-2"><i class="fa fa-angle-down"></i></div>
                <div class="text-muted"><i class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?></div>
            </div>

        </div>

        <div class="col-md-8">

            <!-- Information on data use -->
            <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-target="#SignupIntroduction">
                <i class="fa fa-exclamation-circle"></i> <?php // echo $words->get('SignupIntroductionTitle'); ?>Data visibility
            </button>

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

            <h4>Please fill out all fields</h4>

            <!-- Username -->
            <div class="row m-t-2">
                <div class="col-xs-6">
                    <div class="form-group has-feedback">
                        <label for="register-username" class="sr-only"><?php echo $words->get('SignupUsername'); ?></label>
                        <input type="text" required class="form-control" name="username"
                               minlength="4"
                               maxlength="20"
                               data-validation-ajax-ajax="/signup/checkhandle"
                               placeholder="<?php echo htmlentities($words->get('SignupUsername')); ?>" id="register-username"
                            <?php
                            echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
                            ?> >
                        <span class="text-muted small"></span>
                    </div>
                </div>
                <div class="col-xs-6">
                    <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="<?=htmlentities($words->get('subline_username'))?>">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
            </div>

            <!-- Password -->
            <div class="row mt-1">
                <div class="col-xs-6">
                    <div class="form-group has-feedback">
                        <label for="register-password" class="sr-only"><?php echo $words->get('SignupPassword'); ?></label>
                        <input type="password" required class="form-control" id="register-password" name="password" placeholder="<?php echo $words->get('SignupPassword'); ?>"
                               minlength="6"
                               maxlength="4096"
                               data-validation-minlength-message="Too short"
                            <?php
                            echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
                            ?> >
                        <span class="text-muted small"></span>
                    </div>
                </div>
                <div class="col-xs-6">
                    <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="Please choose a strong password. You may use all letters (a-z A-Z), numbers (0-9) and some special characters (# % ! = + - _)">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm password -->
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group has-feedback">
                        <label for="register-passwordcheck" class="sr-only"><?php echo $words->get('SignupCheckPassword'); ?></label>
                        <input type="password" class="form-control" id="register-passwordcheck" name="passwordcheck" placeholder="<?php echo $words->get('SignupCheckPassword'); ?>"
                               data-validation-matches-match="password"
                               data-validation-matches-message="Must match password entered above"
                            <?php
                            echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
                            ?> >
                        <span class="text-muted small"></span>
                    </div>
                </div>
                <div class="col-xs-6">

                </div>
            </div>

            <!-- E-mail -->
            <div class="row mt-1">
                <div class="col-xs-6">
                    <div class="form-group has-feedback">
                        <label for="register-email" class="sr-only"><?php echo $words->get('SignupEmail'); ?></label>
                        <input type="email" required class="form-control" id="register-email" name="email" placeholder="<?php echo $words->get('SignupEmail'); ?>"
                               data-validation-ajax-ajax="/signup/checkemail"
                            <?php
                            echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : '';
                            ?> />
                        <small class="text-muted text-muted"></small>
                    </div>
                </div>
                <div class="col-xs-6">
                    <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="Your e-mail address is most important: we will forward the messages and request to this e-mail address">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
            </div>

            <!-- confirm E-mail -->
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group has-feedback">
                        <label for="register-emailcheck" class="sr-only"><?php echo $words->get('SignupCheckEmail'); ?></label>
                        <input type="email"
                               data-validation-matches-match="email"
                               data-validation-matches-message="Must match email address entered above"
                               class="form-control" id="register-emailcheck" name="emailcheck" placeholder="<?php echo $words->get('SignupCheckEmail'); ?>"
                            <?php
                            echo isset($vars['emailcheck']) ? 'value="'.$vars['emailcheck'].'" ' : '';
                            ?> />
                        <small class="text-muted text-muted"></small>
                    </div>
                </div>
                <div class="col-xs-6">

                </div>
            </div>

            <!-- Accommodation -->
            <div class="row mt-1">
                <div class="col-xs-6">
                    <label for="accommodation"><h4><?php echo $words->get('Accommodation'); ?></h4></label>

                        <div class="radio">
                            <label>
                                <input type="radio" name="accommodation" id="Anytime" value="anytime" <?php
                                if (isset($vars['accommodation']) && $vars['accommodation'] == 'anytime') { echo ' checked="checked"'; } ?>>
                                <img src="/images/icons/anytime.png">
                                <?php echo $words->get('Accomodation_anytime'); ?>
                            </label>
                        </div>

                        <div class="radio">
                            <label>
                                <input type="radio" name="accommodation" id="dependonrequest" value="dependonrequest" <?php
                                if (isset($vars['accommodation']) && $vars['accommodation'] == 'dependonrequest') { echo ' checked="checked"'; } ?>>
                                <img src="/images/icons/dependonrequest.png">
                                <?php echo $words->get('Accomodation_dependonrequest'); ?>
                            </label>
                        </div>

                        <div class="radio">
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
                </div>
                <div class="col-xs-6">
                    <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="Are you able to provide accommodation?">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
            </div>

            <!-- Next button -->
            <div class="row mt-1">
                <div class="col-xs-6">
                    <button type="submit" class="form-control btn btn-primary"><?php echo $words->getSilent('NextStep'); ?> <i class="fa fa-angle-double-right"></i></button>
                    <?php echo $words->flushBuffer(); ?>
                </div>
                <div class="col-xs-6">

                </div>
            </div>

        </div>
    </div>
</form>
</div>