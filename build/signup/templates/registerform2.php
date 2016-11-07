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
<form method="post" action="<?php echo $baseuri.'signup/3'?>" name="signup" id="user-register-form" class="form" role="form" novalidate>
    <?=$callback_tag ?>
    <input type="hidden" name="javascriptactive" value="false" />
    <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<span class="alert alert-danger">'.$errors['inserror'].'</span>';
        }
    ?>

    <!-- Do we need this DIV? -->
    <div class="row invisible">
        <label for="sweet"><?php echo $words->get('SignupSweet'); ?></label>
        <input type="text" class="form-control" id="sweet" name="sweet" placeholder="<?php echo $words->get('SignupSweet'); ?>" value="" title="Leave free of content"/>
    </div>

    <div class="row m-y-1">
        <div class="col-md-4">

            <h4 class="text-xs-center">Step 2/4</h4>

            <progress class="progress progress-striped progress-success" value="50" max="100">
                <div class="progress">
                    <span class="progress-bar" style="width: 50%;">50%</span>
                </div>
            </progress>


            <div class="h4 text-xs-center hidden-md-down mt-1">
                <div><i class="fa fa-user"></i><br><?php echo $words->get('LoginInformation'); ?> <i class="text-success fa fa-check-square"></i></div>
                <div class="text-muted m-y-2"><i class="fa fa-angle-down"></i></div>
                <div><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                <div class="text-muted m-y-2"><i class="fa fa-angle-down"></i></div>
                <div class="text-muted"><i class="fa fa-map-marker"></i><br><?php echo $words->get('Location'); ?></div>
                <div class="text-muted m-y-2"><i class="fa fa-angle-down"></i></div>
                <div class="text-muted"><i class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?></div>
            </div>

        </div>

        <div class="col-md-8">

            <!-- Information on data use -->
            <button type="button" class="btn btn-sm btn-primary pull-xs-right" data-toggle="modal" data-target="#SignupIntroduction">
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

            <!-- Name -->
            <div class="row m-t-2">
                <div class="col-xs-6">
                    <fieldset class="form-group">
                        <label for="register-firstname" class="form-control-label sr-only"><?php echo $words->get('FirstName'); ?></label>
                        <input type="text" required minlength="2" class="form-control" name="firstname" id="register-firstname" placeholder="<?php echo $words->get('FirstName'); ?>"
                            <?php
                            echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : '';
                            ?> />
                        <small class="text-muted"></small>
                        <?php
                        if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
                            echo '<div class="error">'.$words->get('SignupErrorFullNameRequired').'</div>';
                        }
                        ?>
                    </fieldset>
                </div>
                <div class="col-xs-6">
                    <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="What name may people use to contact you? You can hide your name on your profile.">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <fieldset class="form-group">
                        <label for="secondname" class="form-control-label sr-only"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
                        <input type="text" minlength="2" class="form-control" name="secondname" id="secondname" placeholder="<?php echo $words->get('SignupSecondNameOptional'); ?>"
                            <?php
                            echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : '';
                            ?> />
                    </fieldset>
                </div>
                <div class="col-xs-6">

                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <fieldset class="form-group">
                        <label for="lastname" class="control-label sr-only"><?php echo $words->get('LastName'); ?></label>
                        <input type="text" minlength="2" required class="form-control" name="lastname" id="lastname" placeholder="<?php echo $words->get('LastName'); ?>"
                            <?php
                            echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : '';
                            ?> />
                        <small class="text-muted"></small>
                    </fieldset>
                </div>
                <div class="col-xs-6">

                </div>
            </div>

            <!-- Mother tongue(s)-->
            <div class="row mt-1">
                <div class="col-xs-6">
                    <?php
                    $motherTongue = -1;
                    if (isset($vars['mothertongue'])) {
                        $motherTongue = $vars['mothertongue'];
                    }
                    ?>
                    <label for="mothertongue" class="control-label sr-only"><?php echo $words->get('LanguageLevel_MotherLanguage'); ?></label>
                    <select required class="select2 form-control" name="mothertongue" id="mothertongue" data-placeholder="<?= $words->getBuffered('SignupSelectMotherTongue')?>">
                        <option></option>
                        <option value="-1"></option>
                        <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                            <?= $this->getAllLanguages(true, $motherTongue); ?>
                        </optgroup>
                        <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                            <?= $this->getAllLanguages(false, $motherTongue); ?>
                        </optgroup>
                    </select>
                    <small class="text-muted"></small>
                </div>
                <div class="col-xs-6">
                    <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="What is your native language?">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
            </div>

            <!-- Date of birth-->
            <div class="row m-t-2">
                <div class="col-xs-6">
                    <h4><?php echo $words->get('SignupBirthDate'); ?></h4>
                    <div class="form-inline">
                        <select required id="BirthDate" name="birthyear" class="form-control custom-select">
                            <option value=""><?php echo $words->getSilent('SignupBirthYear'); ?></option>
                            <?php echo $birthYearOptions; ?>
                        </select>
                        <select required name="birthmonth" class="form-control custom-select">
                            <option value=""><?php echo $words->getSilent('SignupBirthMonth'); ?></option>
                            <?php for ($i=1; $i<=12; $i++) { ?>
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
                            <?php for ($i=1; $i<=31; $i++) { ?>
                                <option value="<?php echo $i; ?>"<?php
                                if (isset($vars['birthday']) && $vars['birthday'] == $i) {
                                    echo ' selected="selected"';
                                }
                                ?>><?php echo $i; ?>
                                </option>
                            <?php } ?>
                            <?php echo $words->flushBuffer(); ?>
                        </select>
                    </div>
                    <small class="text-muted"></small>
                    <?php
                    if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                        echo '<span class="alert alert-danger">'.$words->get('SignupErrorBirthDate').'</span>';
                    }
                    if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                        echo '<span class="alert alert-danger">'.$words->getFormatted('SignupErrorBirthDateToLow',SignupModel::YOUNGEST_MEMBER).'</span>';
                    }
                    ?>
                </div>
                <div class="col-xs-6">
                    <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="Do you consider yourself female, male, none or something else?">
                        <i class="fa fa-question"></i>
                    </button>
                </div>
            </div>

            <!-- Gender-->
            <div class="row m-t-2">
                <div class="col-xs-6">
                    <div class="btn-group" data-toggle="buttons">

                        <label class="btn btn-primary <?php
                        if (isset($vars['gender']) && $vars['gender'] == 'female') {
                            echo ' active"';
                        }
                        ?>">
                            <input type="radio" required id="gender" name="gender" value="female"<?php
                            if (isset($vars['gender']) && $vars['gender'] == 'female') {
                                echo ' checked="checked"';
                            }
                            ?> ><?php echo $words->get('female'); ?>
                        </label>
                        <label class="btn btn-primary <?php
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
                        <label class="btn btn-primary <?php
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
                    <small class="text-muted"></small>
                    <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
                        echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorProvideGender').'</span>';
                    }
                    ?>
                </div>
                <div class="col-xs-6">
                    <button type="button" class="btn btn-primary" data-trigger="focus" data-container="body" data-toggle="popover" data-placement="right" data-content="You must be at least 18 years old to signup. You can hide your age on your profile.">
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

<script type="text/javascript">
 jQuery(".select2").select2(); // {no_results_text: "<?= htmlentities($words->getSilent('SignupNoLanguageFound'), ENT_COMPAT); ?>"});
</script>