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

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><?php echo $words->get('SignupName'); ?><small class="pull-right">Bitte fülle alle Felder aus.</small></h3>
      <progress class="progress progress-success" value="50" max="100">
          <div class="progress">
              <span class="progress-bar" style="width: 50%;">50%</span>
          </div>
      </progress>
  </div>
  <div class="card-block">
<form method="post" action="<?php echo $baseuri.'signup/3'?>" name="signup" id="user-register-form" class="form" role="form" novalidate>
    <?=$callback_tag ?>
    <input type="hidden" name="javascriptactive" value="false" />
    <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<span class="help-block alert alert-danger">'.$errors['inserror'].'</span>';
        }
    ?>
    <div class="form-group" style="display:none">
        <label for="sweet"><?php echo $words->get('SignupSweet'); ?></label>
        <input type="text" class="form-control" id="sweet" name="sweet" placeholder="<?php echo $words->get('SignupSweet'); ?>" value="" title="Leave free of content"/>
    </div>
    
    <!-- First name -->
    <div class="form-group">
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
                echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorFullNameRequired').'</span>';
            }
            ?>
    </div>
    
    <!-- Second name -->
    <div class="form-group">
        <label for="secondname" class="form-control-label sr-only"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
            <input type="text" minlength="2" class="form-control" name="secondname" id="secondname" placeholder="<?php echo $words->get('SignupSecondNameOptional'); ?>"
            <?php
            echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
    </div>
    <!-- Last name -->
    <div class="form-group">
        <label for="lastname" class="control-label sr-only"><?php echo $words->get('LastName'); ?></label>
            <input type="text" minlength="2" required class="form-control" name="lastname" id="lastname" placeholder="<?php echo $words->get('LastName'); ?>"
            <?php
            echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
        <small class="text-muted"></small>
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
    <div class="form-group">
        <label class="control-label"><?php echo $words->get('SignupBirthDate'); ?></label>
        <div class="form-inline">
                <select required id="BirthDate" name="birthyear" class="form-control">
                    <option value=""><?php echo $words->getSilent('SignupBirthYear'); ?></option>
                    <?php echo $birthYearOptions; ?>
                </select>
                <select required name="birthmonth" class="form-control">
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
                <select required name="birthday" class="form-control">
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
        </div>
        <?php
            if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorBirthDate').'</span>';
            }
            if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                echo '<span class="help-block alert alert-danger">'.$words->getFormatted('SignupErrorBirthDateToLow',SignupModel::YOUNGEST_MEMBER).'</span>';
            }
        ?>
    <!-- Gender -->
    <div class="form-group">
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
    </div>
    <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
        echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorProvideGender').'</span>';
        }
    ?>    
        <input type="submit" value="<?php echo $words->getSilent('NextStep'); ?>" class="form-control btn btn-primary" >
        <?php echo $words->flushBuffer(); ?>
</form>
  </div>
</div>


<script type="text/javascript">
 jQuery(".select2").select2(); // {no_results_text: "<?= htmlentities($words->getSilent('SignupNoLanguageFound'), ENT_COMPAT); ?>"});
</script>
