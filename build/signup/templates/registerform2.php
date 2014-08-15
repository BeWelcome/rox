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

<div id="signuprox">

<form method="post" action="<?php echo $baseuri.'signup/3'?>" name="signup" id="user-register-form">
  <?=$callback_tag ?>
  <input type="hidden" name="javascriptactive" value="false" />

  <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$errors['inserror'].'</p>';
        }
        ?>



  <!-- Personal Information -->
  <fieldset>
    <legend><?php echo $words->get('SignupName'); ?></legend>

        <div class="signup-row floatbox sweet">
            <label for="sweet"><?php echo $words->get('SignupSweet'); ?></label>
            <input type="text" id="sweet" name="sweet" value="" title="Leave free of content"/>
        </div>

      <!-- First Name -->
        <div class="signup-row floatbox">
          <label for="register-firstname"><?php echo $words->get('FirstName'); ?>* </label>
          <input type="text" id="register-firstname" name="firstname" class="float_left" <?php
          echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <?php
            if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
                echo '<div class="error">'.$words->get('SignupErrorFullNameRequired').'</div>';
            }
            ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupNameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupFirstNameShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->

    <!-- Second Name -->
        <div class="signup-row floatbox">
          <label for="secondname"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
          <input type="text" id="secondname" name="secondname" class="float_left" <?php
          echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <!--
          <span class="small"><?php echo $words->get('SignupSecondNameShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->

      <!-- Last Name -->
      <div class="signup-row floatbox">
          <label for="lastname"><?php echo $words->get('LastName'); ?>* </label>
          <input type="text" id="lastname" name="lastname" class="float_left" <?php
          echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?>/>
          <!--
          <span class="small"><?php echo $words->get('SignupLastNameShortDesc'); ?></span>
          -->
      </div> <!-- signup-row -->

      <!-- Mother tongue(s)-->
      <div>
          <label for="mothertongue"><?php echo $words->get('LanguageLevel_MotherLanguage'); ?>* </label>
          <select name="mothertongue" id="mothertongue" data-placeholder="<?= $words->getBuffered('SignupSelectMotherTongue')?>" style="width: 350px;" class="chosen-select">
              <option value=""></option>
              <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                  <?= $this->getAllLanguages(true); ?>
              </optgroup>
              <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                  <?= $this->getAllLanguages(false); ?>
              </optgroup>
          </select>
      </div> <!-- signup-row -->

      <!-- Birthdate -->
        <div class="signup-row floatbox">
          <label for="BirthDate"><?php echo $words->get('SignupBirthDate'); ?>*</label>
          <select id="BirthDate" name="birthyear">
            <option value=""><?php echo $words->getSilent('SignupBirthYear'); ?></option>
            <?php echo $birthYearOptions; ?>
          </select>
          <select name="birthmonth">
            <option value=""><?php echo $words->getSilent('SignupBirthMonth'); ?></option>
            <?php for ($i=1; $i<=12; $i++) { ?>
            <option value="<?php echo $i; ?>"<?php
            if (isset($vars['birthmonth']) && $vars['birthmonth'] == $i) {
                echo ' selected="selected"';
            }
            ?>><?php echo $i; ?></option>
            <?php } ?>
          </select>
          <select name="birthday">
            <option value=""><?php echo $words->getSilent('SignupBirthDay'); ?></option>
            <?php for ($i=1; $i<=31; $i++) { ?>
            <option value="<?php echo $i; ?>"<?php
            if (isset($vars['birthday']) && $vars['birthday'] == $i) {
                echo ' selected="selected"';
            }
            ?>><?php echo $i; ?></option>
            <?php } ?>
            </select>
            <?php echo $words->flushBuffer(); ?>
            <?php
          if (in_array('SignupErrorBirthDate', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorBirthDate').'</div>';
          }
          if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
              echo '<div class="error">'.$words->getFormatted('SignupErrorBirthDateToLow',SignupModel::YOUNGEST_MEMBER).'</div>';
          }
          ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupBirthDateDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupBirthDateShape'); ?></span>
          -->
        </div> <!-- signup-row -->

    <!-- Gender -->
        <div class="signup-row">
          <label for="gender"><?php echo $words->get('Gender'); ?>*</label>
            <input class="radio" style="float: left" type="radio" id="gender" name="gender" value="female"<?php
             if (isset($vars['gender']) && $vars['gender'] == 'female') {
                 echo ' checked="checked"';
              }
              ?> /><?php echo $words->get('female'); ?>&nbsp;
              <input class="radio" type="radio" name="gender" value="male"<?php
              if (isset($vars['gender']) && $vars['gender'] == 'male') {
                  echo ' checked="checked"';
              }
              ?> /><?php echo $words->get('male'); ?>&nbsp;
              <input class="radio" type="radio" name="gender" value="other"<?php
              if (isset($vars['gender']) && $vars['gender'] == 'other') {
                  echo ' checked="checked"';
              }
              ?> /><?php echo $words->get('GenderOther'); ?>
              <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
                  echo '<div class="error">'.$words->get('SignupErrorProvideGender').'</div>';
                      }
          ?>

          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupGenderDescription'); ?></span></a><br />
          -->
        </div> <!-- signup-row -->
  </fieldset>

  <p class="floatbox">
    <input style="float:left" type="submit" value="<?php echo $words->getSilent('NextStep'); ?>" class="button"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    /><?php echo $words->flushBuffer(); ?><br /><br />
    <a href="signup/1" class="button back" title="<?php echo $words->getSilent('LastStep'); ?>" ><?php echo $words->get('Back'); ?> </a><?php echo $words->flushBuffer(); ?>
  </p>

</form>
</div> <!-- signup -->

<script type="text/javascript">
 Register.initialize('user-register-form');
 jQuery(".chosen-select").select2(); // {no_results_text: "<?= htmlentities($words->getSilent('SignupNoLanguageFound'), ENT_COMPAT); ?>"});
</script>
