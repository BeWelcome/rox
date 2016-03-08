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
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?php echo $words->get('LoginInformation'); ?><small class="pull-right">Step 1/4</small></h4>
                    <progress class="progress progress-striped progress-success" value="25" max="100">
                        <div class="progress">
                            <span class="progress-bar" style="width: 25%;">25%</span>
                        </div>
                    </progress>
                    <h4 class="text-xs-center"><small>Please fill out all fields</small></h4>
                </div>
            </div>
        </div>
            <div class="col-md-3"></div>
    </div>


<form method="post" novalidate action="<?php echo $baseuri.'signup/2'?>" name="signup" id="user-register-form" class="form" role="form">
<?=$callback_tag ?>
<?php
    if (in_array('inserror', $vars['errors'])) {
        echo '<span class="alert alert-danger">'.$errors['inserror'].'</span>';
    }
?>

    <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">

            <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#SignupIntroduction">
                <?php echo $words->get('SignupIntroductionTitle'); ?>
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

        </div>
        <div class="col-md-3"></div>
    </div>

    <div class="form-group row m-t-2">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <!-- username -->
            <div class="form-group has-feedback">
                <label for="register-username" class="sr-only"><?php echo $words->get('SignupUsername'); ?></label>
                <input type="text" required class="form-control" name="username"
                       minlength="4"
                       maxlength="20"
                       data-validation-ajax-ajax="/signup/checkhandle"
                       placeholder="<?php echo $words->get('SignupUsername'); ?>" id="register-username"
                    <?php
                    echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
                    ?> >
                    <span class="text-muted small"></span>
            </div>
        </div>
        <div class="col-xs-12 col-md-3"><span class="text-muted small"><?=$words->get('subline_username')?></span></div>
    </div>

    <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <!-- password -->
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
        <div class="col-xs-12 col-md-3"><span class="text-muted small">Please choose a strong password. You may use all letters (a-z A-Z), numbers (0-9) and some special characters (# % ! = + - _)</span></div>
    </div>

    <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <!-- confirm password -->
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
        <div class="col-xs-12 col-md-3"><span class="text-muted small">Please type in your password again, just to be sure you didn't make a typo.</span></div>
    </div>

    <div class="form-group row m-t-1">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <!-- email -->
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
        <div class="col-xs-12 col-md-3"><span class="text-muted small">Your e-mail address is most important: we will forward the messages and request to this e-mail address</span></div>
    </div>

    <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <!-- confirm email -->
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
        <div class="col-md-3"></div>
    </div>

    <div class="form-group row m-t-1">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <!-- Accommodation -->

            <label for="accommodation"><?php echo $words->get('Accommodation'); ?>*</label>

            <div class="radio">
                <label>
                    <input type="radio" name="gridRadios" id="accommodation" value="anytime" <?php
                    if (isset($vars['accommodation']) && $vars['accommodation'] == 'anytime') { echo ' checked="checked"'; } ?>>
                    <?php echo $words->get('Accomodation_anytime'); ?>
                </label>
            </div>

            <div class="radio">
                <label>
                    <input type="radio" name="gridRadios" id="accommodation" value="dependonrequest" <?php
                    if (isset($vars['accommodation']) && $vars['accommodation'] == 'dependonrequest') { echo ' checked="checked"'; } ?>>
                    <?php echo $words->get('Accomodation_dependonrequest'); ?>
                </label>
            </div>

            <div class="radio">
                <label>
                    <input type="radio" name="gridRadios" id="accommodation" value="neverask" <?php
                    if (isset($vars['accommodation']) && $vars['accommodation'] == 'neverask') { echo ' checked="checked"'; } ?>>
                    <?php echo $words->get('Accomodation_neverask'); ?>
                </label>
            </div>

            <?php if (in_array('SignupErrorProvideAccommodation', $vars['errors'])) {
                echo '<div class="error">'.$words->get('SignupErrorProvideAccommodation').'</div>';
            }
            ?>

            </div>
        <div class="col-xs-12 col-md-3"><span class="text-muted small">Are you able to provide accommodation?</span></div>
    </div>

    <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <input type="submit" class="form-control btn btn-primary" value="<?php echo $words->getSilent('NextStep'); ?>" /><?php echo $words->flushBuffer(); ?>
        </div>
        <div class="col-md-3"></div>
    </div>

</form>