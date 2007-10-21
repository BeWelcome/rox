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
// get current request
$request = PRequest::get()->request;

if (!isset($vars['errors']) || !is_array($vars['errors'])) {
    $vars['errors'] = array();
}

$words = new MOD_words();

// don't show the register form, if user is logged in. Redirect to "my" page instead.
if ($User = APP_User::login()) {
    $url = PVars::getObj('env')->baseuri.'user/'.$User->getHandle();
    header('Location: '.$url);
    PPHP::PExit();
}

if (!isset($request[2]) || $request[2] != 'finish') {
/*
 * REGISTER FORM TEMPLATE
 */
?>
<h2><?php echo $words->get('Signup'); ?></h2>
<form method="post" action="signup/register" name="signup">
<input type="hidden" name="javascriptactive" value="false">
  
  <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$errors['inserror'].'</p>';
        }
        ?>
        
  <fieldset>
    <legend><?php echo $words->get('Location'); ?></legend>
        
      <ul>        
        <li>
          <?php
          if (in_array('SignupErrorProvideCountry', $vars['errors'])) {
              echo '<span class="error">'.$words->get('SignupErrorProvideCountry').'</span>';
          }
          ?>
          <label for="Country"><?php echo $words->get('Country'); ?>*</label><br />
          <?php echo $countries; ?>
        </li>       
      </ul>  
      <ul class="floatbox input_float">
        <li>
          <?php
        	if (in_array('SignupErrorProvideCity', $vars['errors'])) {
        	    echo '<span class="error">'.$words->get('SignupErrorProvideCity').'</span>';
        	}
        	?>
          <label for="City"><?php echo $words->get('City'); ?>*</label><br />
          <?php echo $city; ?>
          <span class="small"><?php echo $words->get('SignupIdCityDescription '); ?></span>
        </li>
        <li class="number">
          <label for="zip"><?php echo $words->get('SignupZip'); ?></label>
          <input type="text" id="zip" name="zip" <?php 
        	echo isset($vars['zip']) ? 'value="'.htmlentities($vars['zip'], ENT_COMPAT, 'utf-8').'" ' : ''; 
        	?>/>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupZipDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupZipDescriptionShort'); ?></span>
        </li>        
      </ul>
   
      <ul class="floatbox input_float">
        <li>
          <?php
        	if (in_array('SignupErrorProvideStreetName', $vars['errors'])) {
        	    echo '<span class="error">'.$words->get('SignupErrorProvideStreetName').'</span>';
        	}
        	?>
          <label for="street"><?php echo $words->get('SignupStreetName'); ?>*</label><br />
          <input type="text" id="street" name="street" <?php 
        	echo isset($vars['street']) ? 'value="'.htmlentities($vars['street'], ENT_COMPAT, 'utf-8').'" ' : ''; 
        	?>/>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupStreetNameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupStreetNameDescription'); ?></span>          
        </li>
        <li class="number">
          <label for="housenumber"><?php echo $words->get('SignupHouseNumber'); ?>*</label><br />
          <input type="text" id="housenumber" name="housenumber" <?php 
          echo isset($vars['housenumber']) ? 'value="'.htmlentities($vars['housenumber'], ENT_COMPAT, 'utf-8').'" ' : ''; 
          ?>/>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupHouseNumberDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupProvideHouseNumber'); ?></span>
        </li>
      </ul>
  </fieldset>	

<!-- Login Information -->
  <fieldset>
    <legend><?php echo $words->get('LoginInformation'); ?></legend>    

      <ul>

    <!-- username -->
        <li>
          <?php
          if (in_array('SignupErrorWrongUsername', $vars['errors'])) {
              echo '<span class="error">'.$words->get('SignupErrorWrongUsername').'</span>';
          }
          if (in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) {
              echo '<span class="error">'.
                  $words->getFormatted('SignupErrorUsernameAlreadyTaken', $vars['username']).
                  '</span>';
          }
          ?>
          <label for="username"><?php echo $words->get('SignupUsername'); ?>* </label><br />
          <input type="text" id="username" name="username" <?php 
        	echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : ''; 
        	?>/>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupUsernameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupUsernameShortDesc'); ?></span>          
        </li>

    <!-- password -->
        <li>
          <?php
          if (in_array('SignupErrorPasswordCheck', $vars['errors'])) {
              echo '<span class="error">'.$words->get('SignupErrorPasswordCheck').'</span>';
          }
          ?>
          <label for="password"><?php echo $words->get('SignupPassword'); ?>* </label><br />
          <input type="password" id="password" name="password" <?php
          echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
          ?>/>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupPasswordDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupPasswordChoose'); ?></span>
       </li>

    <!-- confirm password -->
        <li>
          <label for="passwordcheck"><?php echo $words->get('SignupCheckPassword'); ?>* </label><br />
          <input type="password" id="passwordcheck" name="passwordcheck" <?php
        	echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
        	?>/><br />
          <span class="small"><?php echo $words->get('SignupPasswordConfirmShortDesc'); ?></span>
        </li>
        
    <!-- email -->
        <li>
          <?php
          if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
              echo '<span class="error">'.$words->get('SignupErrorInvalidEmail').'</span>';
          }
          ?>
          <label for="email"><?php echo $words->get('SignupEmail'); ?>* </label><br />
          <input type="text" id="email" name="email" <?php 
          echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : ''; 
          ?>/>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupEmailDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupEmailShortDesc'); ?></span>
        </li>
        
    <!-- confirm email -->
        <li>
          <?php
          if (in_array('SignupErrorEmailCheck', $vars['errors'])) {
              echo '<span class="error">'.$words->get('SignupErrorEmailCheck').'</span>';
          }
          ?>
          <label for="Email"><?php echo $words->get('SignupEmailCheck'); ?>* </label><br />
          <input type="text" name="emailcheck" <?php 
        	echo isset($vars['emailcheck']) ? 'value="'.htmlentities($vars['emailcheck'], ENT_COMPAT, 'utf-8').'" ' : ''; 
        	?>/>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupEmailDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupRetypeEmailShortDesc'); ?>></span>
        </li>
        
      </ul>
  </fieldset>
  
<!-- Personal Information -->  
  <fieldset>
    <legend><?php echo $words->get('SignupName'); ?></legend>

      <ul>

    <!-- First Name -->
        <li>
          <?php
        	if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
        	    echo '<span class="error">'.$words->get('SignupErrorFullNameRequired').'</span>';
        	}
        	?>
          <label for="firstname"><?php echo $words->get('FirstName'); ?>* </label><br />
          <input type="text" id="firstname" name="firstname" <?php 
          echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
          ?>/>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupNameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupFirstNameShortDesc'); ?></span>        
        </li>

    <!-- Second Name -->
        <li>
          <label for="SecondName"><?php echo $words->get('SignupSecondNameOptional'); ?></label><br />
          <input type="text" name="secondname" <?php 
          echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
          ?>/><br />
          <span class="small"><?php echo $words->get('SignupSecondNameShortDesc'); ?></span>
        </li>

    <!-- Last Name -->
        <li>
          <label for="lastname"><?php echo $words->get('LastName'); ?>* </label><br />
          <input type="text" id="lastname" name="lastname" <?php 
          echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
          ?>/><br />
          <span class="small"><?php echo $words->get('SignupLastNameShortDesc'); ?></span>
        </li>      

    <!-- Birthdate -->      
        <li>
          <?php
          if (in_array('SignupErrorBirthDate', $vars['errors'])) {
              echo '<span class="error">'.$words->get('SignupErrorBirthDate').'</span>';
          }
          if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
              echo '<span class="error">'.$words->get('SignupErrorBirthDateToLow').'</span>';
          }
          ?>
          <label for="BirtDdate"><?php echo $words->get('SignupBirthDate'); ?>*</label><br>
          <select name="birthyear">
            <option value=""><?php echo $words->get('MakeAChoice'); ?></option>
            <?php echo $birthYearOptions; ?>
          </select>
          <select name="birthmonth">
            <option value=""></option>
            <?php for ($i=1; $i<=12; $i++) { ?>
            <option value="<?php echo $i; ?>"<?php
            if (isset($vars['birthmonth']) && $vars['birthmonth'] == $i) {
                echo ' selected="selected"';
            }
            ?>><?php echo $i; ?></option>
            <?php } ?>
          </select>    
          <select name="birthday">
            <option value=""></option>
            <?php for ($i=1; $i<=31; $i++) { ?>
            <option value="<?php echo $i; ?>"<?php
            if (isset($vars['birthday']) && $vars['birthday'] == $i) {
                echo ' selected="selected"';
            }
            ?>><?php echo $i; ?></option>
            <?php } ?>
	        </select>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupBirthDateDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupBirthDateShape'); ?></span>
        </li>

    <!-- Gender -->
        <li>
          <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
          echo '<span class="error">'.$words->get('SignupErrorProvideGender').'</span>';
          }
          ?>
          <label for="Gender"><?php echo $words->get('Gender'); ?>*</label><br>
          <input type="radio" name="gender" value="female"<?php
          	if (!isset($vars['gender']) || $vars['gender'] == 'female') {
          	    echo ' checked="checked"';
          	}
              ?>>
              <?php echo $words->get('female'); ?>
              <input type="radio" name="gender" value="male"<?php
              if (isset($vars['gender']) && $vars['gender'] == 'male') {
                  echo ' checked="checked"';
              }
              ?>>
              <?php echo $words->get('male'); ?>
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupGenderDescription'); ?></span></a><br />
        </li>
        
      </ul>
  </fieldset>
  
  <fieldset>
    <legend><?php echo $words->get('SignupFeedback'); ?></legend>
    <p><?php echo $words->get('SignupFeedbackDescription'); ?></p>
    <textarea name="feedback" ><?php 
    echo isset($vars['feedback']) ? htmlentities($vars['feedback'], ENT_COMPAT, 'utf-8') : '';
    ?></textarea>
  </fieldset>
  
  <h4><?php echo $words->get('SignupTermsAndConditions'); ?></h4>
  <?php
    if (in_array('SignupMustacceptTerms', $vars['errors'])) {
        // SignupMustacceptTerms contains unknown placeholder
        echo '<span class="error">'.$words->get('SignupMustacceptTerms').'</span>';
    }
    ?>
  <p class="checkbox"><input type="checkbox" name="Terms"
  <?php
	if (GetStrParam("Terms","")!="") echo " checked" ; // if user has already click, we will not bore him again
	echo ">";
  ?>
  <?php echo $words->get('IAgreeWithTerms'); ?></p>
  <p>
    <input type="submit" value="<?php echo $words->get('SubmitForm'); ?>" class="submit"
    onClick="javascript:document.signup.javascriptactive.value = 'true'; return true;";
    />
    
    <input type="hidden" name="<?php
    // IMPORTANT: callback ID for post data 
    echo $callbackId;
    ?>" value="1"/>      
  </p>

</form>  
</div> <!-- signup -->

<script type="text/javascript">//<!--
// Register.initialize('user-register-form');
//-->
</script>
<?php
} else {
/*
 * FINISHED
 */

$title = $words->get('SignupConfirmedPage');
// FIXME: set page title to $title

// TODO: typo in key: SignupResutlTextConfimation
$message = $words->getFormatted('SignupResutlTextConfimation', $vars['username'], $vars['email']);
echo '<h2>' . $title . '</h2>' . $message;
}
?>
