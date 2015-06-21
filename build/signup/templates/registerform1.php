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
<p><?php echo $words->get('SignupIntroduction'); ?></p>

<form method="post" action="<?php echo $baseuri.'signup/2'?>" name="signup" id="user-register-form">
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
        <div class="signup-row floatbox">
          <label for="register-username"><?php echo $words->get('SignupUsername'); ?>* </label>
          <input type="text" id="register-username" name="username" class="float_left" <?php
            echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
             <?php
          // DEACTIVATED NOW
          // if (in_array('SignupErrorWrongUsername', $vars['errors'])) {
              // $err = 1;
          // } else $err = 0;
          if (in_array('SignupErrorWrongUsername', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorWrongUsername').'</div>';
          }
          if (in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) {
              echo '<div class="error"><br/>'.
                  $words->getFormatted('SignupErrorUsernameAlreadyTaken', $vars['username']).
                  '</div>';
          }
          ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupUsernameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupUsernameShortDesc'); ?></span>
          -->
          <p class="desc" style="margin-left:11.3em; display:inline-block"><?=$words->get('subline_username')?></p>
        </div> <!-- signup-row -->

    <!-- password -->
        <div class="signup-row floatbox">
          <label for="register-password"><?php echo $words->get('SignupPassword'); ?>* </label>
          <input type="password" id="register-password" name="password" class="float_left" <?php
          echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
          ?> />
          <?php
          if (in_array('SignupErrorPasswordCheck', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorPasswordCheck').'</div>';
          }
          ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupPasswordDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupPasswordChoose'); ?></span>
          -->
       </div> <!-- signup-row -->

    <!-- confirm password -->
        <div class="signup-row floatbox">
          <label for="register-passwordcheck"><?php echo $words->get('SignupCheckPassword'); ?>* </label>
          <input type="password" id="register-passwordcheck" name="passwordcheck" class="float_left" <?php
            echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
            ?> />
          <!--
          <span class="small"><?php echo $words->get('SignupPasswordConfirmShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->

    <!-- email -->
        <div class="signup-row floatbox">
          <label for="register-email"><?php echo $words->get('SignupEmail'); ?>* </label>
          <input type="text" id="register-email" name="email" class="float_left" <?php
          echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <?php
          if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorInvalidEmail').'</div>';
          } elseif (in_array('SignupErrorEmailCheck', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorEmailCheck').'</div>';
          } elseif (in_array('SignupErrorEmailAddressAlreadyInUse', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorEmailAddressAlreadyInUse').'</div>';
          } else {
            echo '<br>';
          }
          ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupEmailDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupEmailShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->
        
        <!-- confirm email -->
        <div class="signup-row floatbox">
          <label for="register-emailcheck"><?php echo $words->get('SignupCheckEmail'); ?>* </label>
          <input type="text" id="register-emailcheck" name="emailcheck" class="float_left" <?php
            echo isset($vars['emailcheck']) ? 'value="'.$vars['emailcheck'].'" ' : '';
            ?> />
        </div> <!-- signup-row -->

      <!-- Accommodation -->
      <div class="signup-row">
          <label for="accommodation"><?php echo $words->get('Accommodation'); ?>*</label>
          <input class="radio" type="radio" id="accommodation" name="accommodation" value="anytime"<?php
          if (isset($vars['accommodation']) && $vars['accommodation'] == 'anytime') {
              echo ' checked="checked"';
          }
          ?> /><?php echo $words->get('Accomodation_anytime'); ?>&nbsp;
          <input class="radio" type="radio" id="accommodation" name="accommodation" value="dependonrequest"<?php
          if (isset($vars['accommodation']) && $vars['accommodation'] == 'dependonrequest') {
              echo ' checked="checked"';
          }
          ?> /><?php echo $words->get('Accomodation_dependonrequest'); ?>&nbsp;
          <input class="radio" type="radio" id="accommodation" name="accommodation" value="neverask"<?php
          if (isset($vars['accommodation']) && $vars['accommodation'] == 'neverask') {
              echo ' checked="checked"';
          }
          ?> /><?php echo $words->get('Accomodation_neverask'); ?>&nbsp;
          <?php if (in_array('SignupErrorProvideAccommodation', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorProvideAccommodation').'</div>';
          }
          ?>
      </div>

  </fieldset>

  <p>
    <input type="submit" value="<?php echo $words->getSilent('NextStep'); ?>" class="button"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    /><?php echo $words->flushBuffer(); ?>
  </p>

</form>
</div> <!-- signup -->

<script type="text/javascript">
 Register.initialize('user-register-form');

</script>
