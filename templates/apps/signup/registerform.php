<?php
// get current request
$request = PRequest::get()->request;

if (!isset($vars['errors']) || !is_array($vars['errors']))
    $vars['errors'] = array();

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
<form method="post" action="signup/register" class="def-form" id="user-register-form">

    <?php
    if (in_array('inserror', $vars['errors'])) {
        echo '<p class="error">'.$errors['inserror'].'</p>';
    }
    ?>

  <div class="row">
		<label for=""><?php echo $words->get('Location'); ?></label><br/>
		<?php echo $countries; ?>
		<?php
if (in_array('SignupErrorProvideCountry', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorProvideCountry').'</span>';
}
?>
  </div>
  
  <div class="row">
		<label for=""><?php echo $words->get('City'); ?></label><br/>
		<?php echo $city; ?>
  <?php if (in_array('SignupErrorProvideCity', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorProvideCity').'</span>';
}
?>
        <p class="desc"><?php echo $words->get('SignupIdCityDescription '); ?></p>
  </div>
		
  <div class="row">
		<label for=""><?php echo $words->get('SignupHouseNumber'); ?></label><br/>
		<input type="text" id="register-housenumber" name="housenumber" <?php 
// the housenumber may be set
echo isset($vars['housenumber']) ? 'value="'.htmlentities($vars['housenumber'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <p class="desc"><?php echo $words->get('SignupHouseNumberDescription'); ?></p>
        <p class="desc"><?php echo $words->get('SignupProvideHouseNumber'); ?></p>
  </div>
		
  <div class="row">
		<label for=""><?php echo $words->get('SignupStreetName'); ?></label><br/>
		<input type="text" id="register-street" name="street" <?php 
// the street may be set
echo isset($vars['street']) ? 'value="'.htmlentities($vars['street'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
	<?php if (in_array('SignupErrorProvideStreetName', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorProvideStreetName').'</span>';
}
?>      <p class="desc"><?php echo $words->get('SignupStreetNameDescription'); ?></p>
        <p class="desc"><?php echo $words->get('SignupStreetNameDescription'); ?></p>
  </div>
		
  <div class="row">
		<label for=""><?php echo $words->get('SignupZip'); ?></label><br/>
		<input type="text" id="register-zip" name="zip" <?php 
// the zip/postalcode may be set
echo isset($vars['zip']) ? 'value="'.htmlentities($vars['zip'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <p class="desc"><?php echo $words->get('SignupZipDescription'); ?></p>
        <p class="desc"><?php echo $words->get('SignupZipDescriptionShort'); ?></p>
  </div>
		
    <div class="row">
        <label for="register-u"><?php echo $words->get('SignupUsername'); ?></label><br/>
        <input type="text" id="register-u" name="username" <?php 
// the username may be set
echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <?php if (in_array('SignupErrorWrongUsername', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorWrongUsername').'</span>';
}
if (in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorUsernameAlreadyTaken').'</span>';
}
?>
        <p class="desc"><?php echo $words->get('SignupUsernameDescription'); ?></p>
        <p class="desc"><?php echo $words->get('SignupUsernameShortDesc'); ?></p>
    </div>
    
    <div class="row">
        <label for="register-p"><?php echo $words->get('SignupPassword'); ?></label><br/>
        <input type="password" id="register-p" name="password" <?php
echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
?>/>
    <?php if (in_array('SignupErrorPasswordCheck', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorPasswordCheck').'</span>';
}
?></div>
        <p class="desc"><?php echo $words->get('SignupPasswordDescription'); ?></p>
        <p class="desc"><?php echo $words->get('SignupPasswordChoose'); ?></p>
    </div>
    
    <div class="row">
        <label for="register-pc"><?php echo $words->get('SignupCheckPassword'); ?></label><br/>
        <input type="password" id="register-pc" name="passwordcheck" <?php
echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
?>/>
        <div id="bregister-pc" class="statbtn"></div>
        <p class="desc">enter EXACTLY the same password as per above<?php // FIXME: this is taken from signup.php! ?></p>
    </div>
    
    <div class="row">
    	<label for="register-firstname"><?php echo $words->get('SignupName'); ?></label>
    </div>
    
    <div class="row">
    
    	<div>
        <label for="register-firstname"><?php echo $words->get('FirstName'); ?></label><br/>
        <input type="text" id="register-firstname" name="firstname" <?php 
// the firstname may be set
echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
		</div>
		
		<div>
		<label for="register-firstname"><?php echo $words->get('SignupSecondNameOptional'); ?></label><br/>
        <input type="text" id="register-secondname" name="secondname" <?php 
// the secondname may be set
echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
        ?>/>
		</div>
		
		<div>
		<label for="register-lastname"><?php echo $words->get('LastName'); ?></label><br/>
        <input type="text" id="register-lastname" name="lastname" <?php 
// the lastname may be set
echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
		</div>
		
        <?php if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorFullNameRequired').'</span>';
}
?>
        <p class="desc"><?php echo $words->get('SignupNameDescription'); ?></p>
        <p class="desc"><?php echo $words->get('SignupNameGuide'); ?></p>
        
    </div>

    <div class="row">
        <label for="register-gender"><?php echo $words->get('Gender'); ?></label><br/>
        <input type="radio" name="gender" value="female"<?php
if (!isset($vars['gender']) || $vars['gender'] == 'female') { echo ' checked="checked"'; };
        ?>>&nbsp;&nbsp;&nbsp;
        female
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="radio" name="gender" value="male"<?php
if (isset($vars['gender']) && $vars['gender'] == 'male') { echo ' checked="checked"'; };
        ?>>&nbsp;&nbsp;&nbsp;
        male
        <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorProvideGender').'</span>';
        }
?>
        <p class="desc"><?php echo $words->get('SignupGenderDescription'); ?></p>
        <input type="checkbox" name="genderhidden" value="Yes"<?php
// FIXME: somehow not working
//if (isset($vars['genderhidden']) && strcmp($vars['genderhidden'], 'Yes') == 0) { echo ' checked="checked"'; };
        ?>><?php echo $words->get('Hidden'); ?>
    </div>

    <div class="row">
        <label for="register-birthyear"><?php echo $words->get('SignupBirthDate'); ?></label><br/>
        <select id="register-birthyear" name="birthyear">
        	<option value=""><?php echo $words->get('MakeAChoice'); ?></option>
        	<?php echo $birthYearOptions; ?>
        </select>&nbsp;&nbsp;&nbsp;
        <select id="register-birthmonth" name="birthmonth">
        	<option value=""></option>
        <?php for ($i=1; $i<=12; $i++) { ?>
        	<option value="<?php echo $i; ?>"<?php
if (isset($vars['birthmonth']) && $vars['birthmonth'] == $i) { echo ' selected="selected"'; };
        	?>><?php echo $i; ?></option>
        <?php } ?>
        </select>&nbsp;&nbsp;&nbsp;
        <select id="register-birthday" name="birthday">
        	<option value=""></option>
        <?php for ($i=1; $i<=31; $i++) { ?>
        	<option value="<?php echo $i; ?>"<?php
if (isset($vars['birthday']) && $vars['birthday'] == $i) { echo ' selected="selected"'; };
        	?>><?php echo $i; ?></option>
        <?php } ?>
        </select>
        <br/>
        <?php echo $words->get('SignupBirthDateDescription'); ?>
        <?php
if (in_array('SignupErrorBirthDate', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorBirthDate').'</span>';
}
if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorBirthDateToLow').'</span>';
}
?>
        <p class="desc"><?php echo $words->get('SignupBirthDateDescription'); ?></p>
        <p class="desc"><?php echo $words->get('SignupBirthDateShape'); ?></p>
        <input type="checkbox" name="agehidden" value="Yes"<?php
// FIXME: somehow not working
// if (isset($vars['agehidden']) && $vars['agehidden'] === 'Yes') { echo ' checked="checked"'; }
        ?>><?php echo $words->get('AgeHidden'); ?>
    </div>
    
    <div class="row">
        <label for="register-e"><?php echo $words->get('SignupEmail'); ?></label><br/>
        <input type="text" id="register-e" name="email" <?php 
// the email may be set
echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <?php
if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorInvalidEmail').'</span>';
}
?>
        <p class="desc"><?php echo $words->get('SignupEmailDescription'); ?></p>
        <p class="desc"><?php echo $words->get('SignupEmailShortDesc'); ?></p>
    </div>
    
    <div class="row">
        <label for="register-ec"><?php echo $words->get('SignupEmailCheck'); ?></label><br/>
        <input type="text" id="register-ec" name="emailcheck" <?php 
// the email may be set
echo isset($vars['emailcheck']) ? 'value="'.htmlentities($vars['emailcheck'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <?php
if (in_array('SignupErrorEmailCheck', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupErrorEmailCheck').'</span>';
}
?>
        <p class="desc"><?php echo $words->get('SignupRetypeEmailShortDesc'); ?></p>
    </div>
        
    <div class="row">
        <label for="register-feedback"><?php echo $words->get('SignupFeedback'); ?></label><br/>
        <textarea id="register-feedback" name="feedback" rows="10" cols="60" class="signuptexts"><?php 
// the feedback content may be set
echo isset($vars['feedback']) ? htmlentities($vars['feedback'], ENT_COMPAT, 'utf-8') : '';
?></textarea>
	    <p class="desc"><?php echo $words->get('SignupFeedbackDescription'); ?></p>
    </div>

    <div class="row">
        <a href="" target="_blank">
        Please make sure you've read the terms and conditions by clicking this link<?php // FIXME: text is to be included in ww-table ?> 
        </a>
    </div>
    
    <div class="row">
    	<input type="checkbox" id="register-terms" name="terms" value="Yes" /> <?php echo $words->get('IAgreeWithTerms'); ?>
    	<?php
if (in_array('SignupMustacceptTerms', $vars['errors'])) {
    echo '<span class="error">'.$words->get('SignupMustacceptTerms').'</span>';
}
?>
    </div>
    
    <p>
        <input type="submit" value="<?php echo $words->get('SubmitForm'); ?>" class="submit"/>
        <input type="hidden" name="<?php
// IMPORTANT: callback ID for post data 
echo $callbackId; ?>" value="1"/>
    </p>
</form>
<script type="text/javascript">//<!--
// Register.initialize('user-register-form');
//-->
</script>
<?php
} else {
/*
 * FINISHED
 */
?>
<h2><?php echo $regText['finish_title']; // FIXME ?></h2>
<p><?php echo $regText['finish_text']; // FIXME ?></p>
<?php
}
?>