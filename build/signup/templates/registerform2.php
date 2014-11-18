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

<div id="signuprox2">
<!-- Custom BeWelcome signup progress bar -->
<div class="progress">
    <div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            <a href="signup/1" <?=($step =='1') ? 'onclick="$(\'user-register-form\').action = \'signup/1\'; $(\'user-register-form\').submit(); return false"' : '' ?>>1. <?php echo $words->getFormatted('LoginInformation')?></a>
        </span>
        <span class="bw-progress visible-xs-inline">
            <a href="signup/1" <?=($step =='1') ? 'onclick="$(\'user-register-form\').action = \'signup/1\'; $(\'user-register-form\').submit(); return false"' : '' ?>>Schritt 1.</a>
        </span>
    </div>
    <div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            <a href="signup/2" <?=($step <='2') ? 'onclick="$(\'user-register-form\').action = \'signup/2\'; $(\'user-register-form\').submit(); return false"' : '' ?>>2. <?php echo $words->getFormatted('SignupName')?></a>
        </span>
        <span class="bw-progress visible-xs-inline">
            <a href="signup/2" <?=($step <='2') ? 'onclick="$(\'user-register-form\').action = \'signup/2\'; $(\'user-register-form\').submit(); return false"' : '' ?>>Schritt 2.</a>
        </span>
    </div>
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            <a href="signup/3" <?=($step <='3') ? 'onclick="$(\'user-register-form\').action = \'signup/3\'; $(\'user-register-form\').submit(); return false"' : '' ?>>3. <?php echo $words->getFormatted('Location')?></a>
        </span>
        <span class="bw-progress visible-xs-inline">
            <a href="signup/3" <?=($step <='3') ? 'onclick="$(\'user-register-form\').action = \'signup/3\'; $(\'user-register-form\').submit(); return false"' : '' ?>>Schritt 3.</a>
        </span>
    </div>
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            <a href="signup/4" <?=($step <='4') ? 'onclick="$(\'user-register-form\').action = \'signup/4\'; $(\'user-register-form\').submit(); return false"' : '' ?>>4. <?php echo $words->getFormatted('SignupSummary')?></a>
        </span>
        <span class="bw-progress visible-xs-inline">
            <a href="signup/4" <?=($step <='4') ? 'onclick="$(\'user-register-form\').action = \'signup/4\'; $(\'user-register-form\').submit(); return false"' : '' ?>>Schritt 4.</a>
        </span>
    </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo $words->get('SignupName'); ?><small class="pull-right">Bitte fülle alle Felder aus.</small></h3> 
  </div>
  <div class="panel-body">
<form method="post" action="<?php echo $baseuri.'signup/3'?>" name="signup" id="user-register-form" class="form" role="form">
    <?=$callback_tag ?>
    <input type="hidden" name="javascriptactive" value="false" />
    <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<span class="help-block alert alert-danger">'.$errors['inserror'].'</span';
        }
    ?>
    <div class="form-group hidden">
        <label for="sweet"><?php echo $words->get('SignupSweet'); ?></label>
        <input type="text" class="form-control" id="sweet" name="sweet" placeholder="<?php echo $words->get('SignupSweet'); ?>" value="" title="Leave free of content"/>
    </div>
    <!-- First name -->
    <div class="form-group has-feedback">
        <label for="register-firstname" class="control-label sr-only"><?php echo $words->get('FirstName'); ?></label>
            <input type="text" class="form-control" name="firstname" id="register-firstname" placeholder="<?php echo $words->get('FirstName'); ?>" 
            <?php
            echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
            <?php
            if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
                echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorFullNameRequired').'</span>';
            }
            ?>
    </div>
    <!-- Second name -->
    <div class="form-group has-feedback">
        <label for="secondname" class="control-label sr-only"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
            <input type="text" class="form-control" name="secondname" id="secondname" placeholder="<?php echo $words->get('SignupSecondNameOptional'); ?>" 
            <?php
            echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
    </div>
    <!-- Last name -->
    <div class="form-group has-feedback">
        <label for="lastname" class="control-label sr-only"><?php echo $words->get('LastName'); ?></label>
            <input type="text" class="form-control" name="lastname" id="lastname" placeholder="<?php echo $words->get('LastName'); ?>" 
            <?php
            echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
    </div>
    
    <!-- Mother tongue(s)-->
    <?php
        $motherTongue = -1;
        if (isset($vars['mothertongue'])) {
            $motherTongue = $vars['mothertongue'];
        }
    ?>
    <div class="form-group">
          <label for="mothertongue" class="control-label sr-only"><?php echo $words->get('LanguageLevel_MotherLanguage'); ?></label>
          <select class="select2 form-control" name="mothertongue" id="mothertongue" data-placeholder="<?= $words->getBuffered('SignupSelectMotherTongue')?>">
              <option></option>
              <option value="-1"></option>
              <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                  <?= $this->getAllLanguages(true, $motherTongue); ?>
              </optgroup>
              <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                  <?= $this->getAllLanguages(false, $motherTongue); ?>
              </optgroup>
          </select>
    </div>
    <div class="form-group has-feedback">
        <label class="control-label"><?php echo $words->get('SignupBirthDate'); ?></label>
        <div class="form-inline">
            <div class="form-group">
                <select id="BirthDate" name="birthyear" class="form-control">
                    <option value=""><?php echo $words->getSilent('SignupBirthYear'); ?></option>
                    <?php echo $birthYearOptions; ?>
                </select>
            </div>
            <div class="form-group">
                <select name="birthmonth" class="form-control">
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
            </div>
            <div class="form-group">
                <select name="birthday" class="form-control">
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
        </div>
        <?php
            if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorBirthDate').'</span>';
            }
            if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                echo '<span class="help-block alert alert-danger">'.$words->getFormatted('SignupErrorBirthDateToLow',SignupModel::YOUNGEST_MEMBER).'</span>';
            }
        ?>
    </div>
    <!-- Gender -->
    <div class="form-group has-feedback">
      <label for="gender" class="control-label"><?php echo $words->get('Gender'); ?></label>
        <div class="form-inline">
            <div class="form-group">
                <label class="radio-inline">
                    <input type="radio" id="gender" name="gender" value="female"<?php
                     if (isset($vars['gender']) && $vars['gender'] == 'female') {
                         echo ' checked="checked"';
                      }
                      ?> /><?php echo $words->get('female'); ?>
                </label>
                <label class="radio-inline">
                  <input type="radio" name="gender" value="male"<?php
                  if (isset($vars['gender']) && $vars['gender'] == 'male') {
                      echo ' checked="checked"';
                  }
                  ?> /><?php echo $words->get('male'); ?>
                </label>
                <label class="radio-inline">
                  <input type="radio" name="gender" value="other"<?php
                  if (isset($vars['gender']) && $vars['gender'] == 'other') {
                      echo ' checked="checked"';
                  }
                  ?> /><?php echo $words->get('GenderOther'); ?>
                  </label>
            </div>
            <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
                echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorProvideGender').'</span>';
                }
            ?>    
        </div>
    </div>
    <div class="clearfix">
    <a href="signup/1" class="button back pull-left" title="<?php echo $words->getSilent('LastStep'); ?>" ><?php echo $words->get('Back'); ?> </a><?php echo $words->flushBuffer(); ?>
        <input type="submit" value="<?php echo $words->getSilent('NextStep'); ?>" class="button pull-right"
        onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
        /><?php echo $words->flushBuffer(); ?>
    </div> 
</form>
  </div>
</div>
</div> <!-- signup2 -->

<script type="text/javascript">
 Register.initialize('user-register-form');
 jQuery(".select2").select2(); // {no_results_text: "<?= htmlentities($words->getSilent('SignupNoLanguageFound'), ENT_COMPAT); ?>"});
</script>
