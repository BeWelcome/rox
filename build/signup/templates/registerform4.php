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
<p><?php echo $words->get('SignupCheckIntro'); ?></p>

<form method="post" action="<?php echo $baseuri.'signup/4'?>" name="signup" id="user-register-form">
  <?=$callback_tag ?>
  <input type="hidden" name="javascriptactive" value="false" />

  <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$errors['inserror'].'</p>';
        }
        ?>

  <!-- Login Information -->
  <fieldset>
    <legend><?php echo $words->get('LoginInformation'); ?></legend>

    <!-- username -->
        <div class="signup-row-thin">
          <label for="register-username"><?php echo $words->get('SignupUsername'); ?>* </label>
          <?=(in_array('SignupErrorWrongUsername', $vars['errors']) || in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) ? '' : '<p class="entered">'.$vars['username'].'</p>'; ?>
          <input <?=(in_array('SignupErrorWrongUsername', $vars['errors']) || in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) ? '' : 'type="hidden"'?> id="register-username" name="username" <?php
            echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
             <?php
          if (in_array('SignupErrorWrongUsername', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorWrongUsername').'</div>';
          }
          if (in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) {
              echo '<div class="error"><br/>'.
                  $words->getFormatted('SignupErrorUsernameAlreadyTaken', $vars['username']).
                  '</div>';
          }
          ?>

        </div> <!-- signup-row-thin -->

    <!-- password -->
    <?php if (in_array('SignupErrorPasswordCheck', $vars['errors'])) { ?>
        <div class="signup-row-thin">
          <label for="register-password"><?php echo $words->get('SignupPassword'); ?>* </label>
          <input type="password" id="register-password" name="password"  <?php
          echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
          ?> />
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupPasswordDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupPasswordChoose'); ?></span>
          -->
          <?php
          if (in_array('SignupErrorPasswordCheck', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorPasswordCheck').'</div>';
          }
          ?>
        </div> <!-- signup-row -->

    <!-- confirm password -->
        <div class="signup-row-thin">
          <label for="register-passwordcheck"><?php echo $words->get('SignupCheckPassword'); ?>* </label>
          <input type="password" id="register-passwordcheck" name="passwordcheck"  <?php
            echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
            ?> />
          <!--
          <span class="small"><?php echo $words->get('SignupPasswordConfirmShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->
          <?php
          } else { ?>
        <div class="signup-row-thin">
          <label for="password"><?php echo $words->get('SignupPassword'); ?>* </label>
            <?php  echo '<p class="entered">********</p>'; ?>
        </div>
        <?php 
        } 
        ?>

    <!-- email -->
       <div class="signup-row-thin">
          <label for="register-email"><?php echo $words->get('SignupEmail'); ?>* </label>
          <input <?=(in_array('SignupErrorInvalidEmail', $vars['errors']) || in_array('SignupErrorEmailCheck', $vars['errors'])) ? '':'type="hidden"'?> id="register-email" name="email"  <?php
          echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />  
          <?php
          if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorInvalidEmail').'</div>';
          } else if (in_array('SignupErrorEmailCheck', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorEmailCheck').'</div>';
          } else if (in_array('SignupErrorEmailAddressAlreadyInUse', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorEmailAddressAlreadyInUse').'</div>';
          } else {
            echo '<p class="float_left entered">'.$vars['email'].'</p>';
          }
          ?>

        </div> <!-- signup-row-thin -->
        
        <!-- confirm email -->
        <?php 
        if (in_array('SignupErrorEmailCheck', $vars['errors']) || in_array('SignupErrorInvalidEmail', $vars['errors'])) { ?>
            <div class="signup-row-thin">
                <label for="register-emailcheck"><?php echo $words->get('SignupCheckEmail'); ?>* </label>
                <input type="text" id="register-emaildcheck" name="emailcheck" <?php 
                    echo isset($vars['emailcheck']) ? 'value="'.$vars['emailcheck'].'" ' : '';
                    ?> />
            </div> 
        <?php
        } else {  
        } 
        ?>
        <!-- signup-row -->
  </fieldset>

  <!-- Personal Information -->
  <fieldset>
    <legend><?php echo $words->get('SignupName'); ?></legend>
      <?php
      if (in_array('SignupErrorSomethingWentWrong', $vars['errors'])) :
        echo '<div class="error">'.$words->get('SignupErrorSomethingWentWrong').'</div>';
      ?>
      <div class="signup-row-thin sweet">
          <label for="sweet"><?php echo $words->get('SignupSweet'); ?></label>
          <input type="text" id="sweet" name="sweet" value="" title="Leave free of content"/>
      </div>
       <?php endif;

if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
      echo '<div class="error">'.$words->get('SignupErrorFullNameRequired').'</div>';
?>
    <!-- First Name -->
        <div class="signup-row-thin">
          <label for="firstname"><?php echo $words->get('FirstName'); ?>* </label>
          <input id="firstname" name="firstname" <?php
          echo isset($vars['firstname']) ? 'value="'.htmlentities(strip_tags($vars['firstname']), ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />

        </div> <!-- signup-row-thin -->

    <!-- Second Name -->
        <div class="signup-row-thin">
          <label for="secondname"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
          <input id="secondname" name="secondname" <?php
          echo isset($vars['secondname']) ? 'value="'.htmlentities(strip_tags($vars['secondname']), ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <!--
          <span class="small"><?php echo $words->get('SignupSecondNameShortDesc'); ?></span>
          -->
        </div> <!-- signup-row-thin -->

    <!-- Last Name -->
        <div class="signup-row-thin">
          <label for="lastname"><?php echo $words->get('LastName'); ?>* </label>
          <input id="lastname" name="lastname" <?php
          echo isset($vars['lastname']) ? 'value="'.htmlentities(strip_tags($vars['lastname']), ENT_COMPAT, 'utf-8').'" ' : '';
          ?>/>
          <!--
          <span class="small"><?php echo $words->get('SignupLastNameShortDesc'); ?></span>
          -->
        </div> <!-- signup-row-thin -->
<?php } else { ?>
        <div class="signup-row-thin">
          <label for="firstname"><?php echo $words->get('Name'); ?>* </label>
          <input type="hidden" id="firstname" name="firstname" <?php
          echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <input type="hidden" id="secondname" name="secondname" <?php
          echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <input type="hidden" id="lastname" name="lastname" <?php
          echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?>/>
          <p class="entered"><?=strip_tags($vars['firstname']) .' ' . strip_tags($vars['secondname']) .' '. strip_tags($vars['lastname']) ;?></p>
        </div> <!-- signup-row-thin -->

<?php }
      $disable = true;
        if (in_array('SignupErrorNoMotherTongue', $vars['errors'])) {
            echo '<div class="error">' . $words->get('SignupErrorNoMotherTongue') . '</div>';
            $disable = false;
        }
      ?>

    <!-- Mother tongue(s)-->
    <div class="signup-row-thin">
        <label for="mothertongue"><?php echo $words->get('MotherTongue'); ?>* </label>
        <select name="mothertongue" id="mothertongue" data-placeholder="<?= $words->getBuffered('SignupSelectMotherTongue')?>" style="width: 350px;" class="chosen-select"
            <?= ($disable) ? 'disabled="disabled"' : ''?> >
            <option value=""></option>
            <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                <?= $this->getAllLanguages(true, $vars['mothertongue']); ?>
            </optgroup>
            <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                <?= $this->getAllLanguages(false, $vars['mothertongue']); ?>
            </optgroup>
        </select>
    </div> <!-- signup-row -->

    <!-- Birthdate -->
        <div class="signup-row-thin">
          <label for="BirthDate"><?php echo $words->get('SignupBirthDate'); ?>*</label>
        <?php
        if (in_array('SignupErrorBirthDate', $vars['errors']) || in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
        ?>
          <select id="BirthDate" name="birthyear">
            <option value=""><?php echo $words->get('SignupBirthYear'); ?></option>
            <?php echo $birthYearOptions; ?>
          </select>
          <select name="birthmonth">
            <option value=""><?php echo $words->get('SignupBirthMonth'); ?></option>
            <?php for ($i=1; $i<=12; $i++) { ?>
            <option value="<?php echo $i; ?>"<?php
            if (isset($vars['birthmonth']) && $vars['birthmonth'] == $i) {
                echo ' selected="selected"';
            }
            ?>><?php echo $i; ?></option>
            <?php } ?>
          </select>
          <select name="birthday">
            <option value=""><?php echo $words->get('SignupBirthDay'); ?></option>
            <?php for ($i=1; $i<=31; $i++) { ?>
            <option value="<?php echo $i; ?>"<?php
            if (isset($vars['birthday']) && $vars['birthday'] == $i) {
                echo ' selected="selected"';
            }
            ?>><?php echo $i; ?></option>
            <?php } ?>
            </select>
            <?php
        } else {
        ?>
          <p class="entered">
          <input type="hidden" id="BirthDay" name="birthday" value="<?php
            if (isset($vars['birthday'])) {
                echo $vars['birthday'];
            }
          ?>">
          <?=$vars['birthday'].'. ';?>
          <input type="hidden" id="BirthMonth" name="birthmonth" value="<?php
            if (isset($vars['birthmonth'])) {
                echo $vars['birthmonth'];
            }
          ?>">
          <?=$vars['birthmonth'].'. ';?>
          <input type="hidden" id="BirthYear" name="birthyear" value="<?php
            if (isset($vars['birthyear'])) {
                echo $vars['birthyear'];
            }
          ?>">
          <?=$vars['birthyear'];?>
          </p>
            <?php
        }
          if (in_array('SignupErrorBirthDate', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorBirthDate').'</div>';
          }
          if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
              echo '<div class="error">'.$words->getFormatted('SignupErrorBirthDateToLow',SignupModel::YOUNGEST_MEMBER).'</div>';
          }

          ?>
        </div> <!-- signup-row-thin -->

    <!-- Gender -->

        <div class="signup-row-thin">
          <label for="gender"><?php echo $words->get('Gender'); ?>*</label>

<?php if (in_array('SignupErrorProvideGender', $vars['errors'])) { ?>
              <input class="radio"  type="radio" id="gender" name="gender" value="female"<?php
                 if (isset($vars['gender']) && $vars['gender'] == 'female') {
                     echo ' checked="checked"';
                  }
                  ?> /><?php echo $words->get('female'); ?>&nbsp;
                  <input class="radio" type="radio" name="gender" value="male"<?php
                  if (isset($vars['gender']) && $vars['gender'] == 'male') {
                      echo ' checked="checked"';
                  }
                  ?> /><?php echo $words->get('male');?>&nbsp;
                  <input class="radio" type="radio" name="gender" value="male"<?php
                  if (isset($vars['gender']) && $vars['gender'] == 'other') {
                      echo ' checked="checked"';
                  }
                  ?> /><?php echo $words->get('Genderother');?>
                <div class="error"><?=$words->get('SignupErrorProvideGender')?></div>
<?php } else { ?>
          <input type="hidden" id="gender" name="gender" value="<?php
             if (isset($vars['gender'])) {
                echo $vars['gender'].'" />';
                echo '<p class="entered">'.$vars['gender'].'</p>';
             } else {
                echo '<p class="entered"> - </p>';
             }
 } ?>

        </div> <!-- signup-row-thin -->
  </fieldset>

  <fieldset id="location">
        <legend><?php echo $words->get('Location'); ?></legend>
      <div class="signup-row-thin">
        <label for="geonameid"><?php echo $words->get('Location'); ?></label>
<?php if (in_array('SignupErrorProvideLocation', $vars['errors'])) { ?>
            <a href="signup/3" class="button" title="<?php echo $words->get('label_setlocation'); ?>" ><span><?php echo $words->get('label_setlocation'); ?></span></a>
<br />
            <div class="error"><?=$words->get('SignupErrorProvideLocation')?></div>
<?php } else { ?>
          <input type="hidden" id="geonameid" name="geonameid" <?php
            echo isset($vars['geonameid']) ? 'value="'.htmlentities($vars['geonameid'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
          <p class="entered"><?= urldecode($vars['geonamename']);?><?=isset($vars['countryname']) ? ', ' . urldecode($vars['countryname']) : '' ?><?=isset($vars['admincode']) ? ' / ' . urldecode($vars['admincode']) : '' ?></p>
<?php } ?>
      </div>

    <input type="hidden" name="geonameid" id="geonameid" value="<?php
            echo isset($vars['geonameid']) ? htmlentities($vars['geonameid'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="latitude" id="latitude" value="<?php
            echo isset($vars['latitude']) ? htmlentities($vars['latitude'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="longitude" id="longitude" value="<?php
            echo isset($vars['longitude']) ? htmlentities($vars['longitude'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="geonamename" id="geonamename" value="<?php
            echo isset($vars['geonamename']) ? htmlentities($vars['geonamename'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="countryname" id="countryname" value="<?php
            echo isset($vars['countryname']) ? htmlentities($vars['countryname'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="geonamecountrycode" id="geonamecountrycode" value="<?php
            echo isset($vars['geonamecountrycode']) ? htmlentities($vars['geonamecountrycode'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="admincode" id="admincode" value="<?php
            echo isset($vars['admincode']) ? htmlentities($vars['admincode'], ENT_COMPAT, 'utf-8') : '';
        ?>" />

  </fieldset>

  <!-- feeback -->
  <fieldset>
    <legend><?php echo $words->get('SignupFeedback'); ?></legend>
    <div class="signup-row-thin">
        <p><?php echo $words->get('SignupFeedbackDescription'); ?></p>
        <textarea name="feedback" rows="10" cols="80"><?php
        echo isset($vars['feedback']) ? htmlentities($vars['feedback'], ENT_COMPAT, 'utf-8') : '';
        ?></textarea>
    </div>
  </fieldset>

  <!-- terms -->

  <?php
    if (in_array('SignupMustacceptTerms', $vars['errors'])) {
        // SignupMustacceptTerms contains unknown placeholder
        echo '<div class="error">'.$words->get('SignupTermsAndConditions').'</div>';
    }
    ?>
  <p class="checkbox"><input type="checkbox" name="terms"
  <?php
    if (isset ($vars["terms"])) echo " checked=\"checked\"" ; // if user has already clicked, we will not bore him again
    echo " />";
  ?>
  <?php echo $words->get('IAgreeWithTerms'); ?></p>
  <p>
    <input type="submit" value="<?php echo $words->getSilent('SubmitForm'); ?>" class="submit"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    /><?php echo $words->flushBuffer(); ?><br /><br />
    <a href="signup/3" class="button back" title="<?php echo $words->getSilent('LastStep'); ?>" ><span><?php echo $words->getSilent('Back'); ?></span></a><?php echo $words->flushBuffer(); ?>
  </p>

</form>
<?php //var_dump($vars['errors']); ?>
</div> <!-- signup -->
<script type="text/javascript">
    jQuery(".chosen-select").chosen(); // {no_results_text: "<?= htmlentities($words->getSilent('SignupNoLanguageFound'), ENT_COMPAT); ?>"});
</script>
