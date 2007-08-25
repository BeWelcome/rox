<?php
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
<table  class="signuptables">

<tr>
<td colspan="3">
    <?php
    if (in_array('inserror', $vars['errors'])) {
        echo '<p class="error">'.$errors['inserror'].'</p>';
    }
    ?>
</td>
</tr>

<tr>
<td>
    <?php echo $words->get('Location'); ?>
</td>
<td>
    <?php echo $countries; ?>
</td>
<td>
    <?php
    if (in_array('SignupErrorProvideCountry', $vars['errors'])) {
        echo '<span class="error">'.$words->get('SignupErrorProvideCountry').'</span>';
    }
    ?>
</td>
</tr>

<tr>
<td>
    <?php echo $words->get('City'); ?>
</td>
<td>
	<?php echo $city; ?>
</td>
<td>
	<?php
	if (in_array('SignupErrorProvideCity', $vars['errors'])) {
	    echo '<span class="error">'.$words->get('SignupErrorProvideCity').'</span>';
	}
	?>
    <p class="desc"><?php echo $words->get('SignupIdCityDescription '); ?></p>
</td>
</tr>
		
<tr>
<td>
	<?php echo $words->get('SignupHouseNumber'); ?>
</td>
<td>
	<input type="text" name="housenumber" <?php 
	    echo isset($vars['housenumber']) ? 'value="'.htmlentities($vars['housenumber'], ENT_COMPAT, 'utf-8').'" ' : ''; 
	?>/>

    <a href="#" onclick="return false;">?<span>
    <?php echo $words->get('SignupHouseNumberDescription'); ?>
    </span></a>
</td>
<td>
    <p class="desc"><?php echo $words->get('SignupProvideHouseNumber'); ?></p>
</td>
</tr>
		
<tr>
<td>
	<?php echo $words->get('SignupStreetName'); ?>
</td>
<td>
	<input type="text" name="street" <?php 
	echo isset($vars['street']) ? 'value="'.htmlentities($vars['street'], ENT_COMPAT, 'utf-8').'" ' : ''; 
	?>/>
	<a href="#" onclick="return false;">?<span>
	<p class="desc"><?php echo $words->get('SignupStreetNameDescription'); ?></p>
	</span></a>
</td>
<td>
	<?php
	if (in_array('SignupErrorProvideStreetName', $vars['errors'])) {
	    echo '<span class="error">'.$words->get('SignupErrorProvideStreetName').'</span>';
	}
	?>
    <p class="desc"><?php echo $words->get('SignupStreetNameDescription'); ?></p>
</td>
</tr>
		
<tr>
<td>
	<?php echo $words->get('SignupZip'); ?>
</td>
<td>
	<input type="text" name="zip" <?php 
	echo isset($vars['zip']) ? 'value="'.htmlentities($vars['zip'], ENT_COMPAT, 'utf-8').'" ' : ''; 
	?>/>
	<a href="#" onclick="return false;">?<span>
    <p class="desc"><?php echo $words->get('SignupZipDescription'); ?></p>
	</span></a>
</td>
<td>
    <p class="desc"><?php echo $words->get('SignupZipDescriptionShort'); ?></p>
</td>
</tr>
		
<tr>
<td>
	<?php echo $words->get('SignupUsername'); ?>
</td>
<td>
	<input type="text" name="username" <?php 
	echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : ''; 
	?>/>
	<a href="#" onclick="return false;">?<span>
	<p class="desc"><?php echo $words->get('SignupUsernameDescription'); ?></p>
	</span></a>
</td>
<td>
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
	<p class="desc"><?php echo $words->get('SignupUsernameShortDesc'); ?></p>
</td>
</tr>
    
<tr>
<td>
    <?php echo $words->get('SignupPassword'); ?>
</td>
<td>
    <input type="password" name="password" <?php
    echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
    ?>/>
	<a href="#" onclick="return false;">?<span>
    <p class="desc"><?php echo $words->get('SignupPasswordDescription'); ?></p>
	</span></a>
</td>
<td>
    <?php
    if (in_array('SignupErrorPasswordCheck', $vars['errors'])) {
        echo '<span class="error">'.$words->get('SignupErrorPasswordCheck').'</span>';
    }
    ?>
    <p class="desc"><?php echo $words->get('SignupPasswordChoose'); ?></p>
</td>
</tr>
    
<tr>
<td>
    <?php echo $words->get('SignupCheckPassword'); ?>
</td>
<td>
	<input type="password" name="passwordcheck" <?php
	echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
	?>/>	
</td>
<td>
	<p class="desc">enter EXACTLY the same password as per above<?php // FIXME: this is taken from signup.php! ?></p>
</td>
</tr>
    
<tr>
<td>
	<?php echo $words->get('SignupName'); ?>
</td>
<td>
	<?php echo $words->get('FirstName'); ?>
	&nbsp;
	<?php echo $words->get('SignupSecondNameOptional'); ?>
	&nbsp;
	<?php echo $words->get('LastName'); ?>
</td>
<td>
</td>
</tr>
    
<tr>
<td>
</td>
<td>
	<input type="text" name="firstname" <?php 
    echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
    ?>/>
	
    <input type="text" name="secondname" <?php 
    echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
    ?>/>
	
	<input type="text" name="lastname" <?php 
	echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : ''; 
	?>/>

	<a href="#" onclick="return false;">?<span>
    <p class="desc"><?php echo $words->get('SignupNameDescription'); ?></p>
	</span></a>

</td>
<td>
	<?php
	if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
	    echo '<span class="error">'.$words->get('SignupErrorFullNameRequired').'</span>';
	}
	?>
	<p class="desc"><?php echo $words->get('SignupNameGuide'); ?></p>
</td>
</tr>

<tr>
<td>
    <?php echo $words->get('Gender'); ?>
</td>
<td>
	<input type="radio" name="gender" value="female"<?php
	if (!isset($vars['gender']) || $vars['gender'] == 'female') {
	    echo ' checked="checked"';
	}
    ?>>&nbsp;&nbsp;&nbsp;
	female
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="radio" name="gender" value="male"<?php
    if (isset($vars['gender']) && $vars['gender'] == 'male') {
        echo ' checked="checked"';
    }
    ?>>&nbsp;&nbsp;&nbsp;
    male
    
    <?php echo $words->get('Hidden'); ?>

	<input type="checkbox" name="genderhidden" value="Yes"<?php
	if (isset($vars['genderhidden']) && strcmp($vars['genderhidden'], 'Yes') == 0) { echo ' checked="checked"'; };
	?>>
    
	<a href="#" onclick="return false;">?<span>
	<?php echo $words->get('SignupGenderDescription'); ?>
	</span></a>
</td>
<td>
    <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
        echo '<span class="error">'.$words->get('SignupErrorProvideGender').'</span>';
    }
    ?>
</td>
</tr>

<tr>
<td>
    <?php echo $words->get('SignupBirthDate'); ?>
</td>
<td>
	<select name="birthyear">
    	<option value=""><?php echo $words->get('MakeAChoice'); ?></option>
        <?php echo $birthYearOptions; ?>
	</select>
	&nbsp;&nbsp;&nbsp;
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
    &nbsp;&nbsp;&nbsp;
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

	<?php echo $words->get('AgeHidden'); ?>
        <input type="checkbox" name="agehidden" value="Yes"<?php
        if (isset($vars['agehidden']) && $vars['agehidden'] === 'Yes') { echo ' checked="checked"'; }
        ?>>


	<a href="#" onclick="return false;">?<span>
	<?php echo $words->get('SignupBirthDateDescription'); ?>
	</span></a>

</td>
<td>
    <?php
    if (in_array('SignupErrorBirthDate', $vars['errors'])) {
        echo '<span class="error">'.$words->get('SignupErrorBirthDate').'</span>';
    }
    if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
        echo '<span class="error">'.$words->get('SignupErrorBirthDateToLow').'</span>';
    }
    ?>
    <p class="desc"><?php echo $words->get('SignupBirthDateShape'); ?></p>
</td>
</tr>

<tr>
<td>
    <?php echo $words->get('SignupEmail'); ?>
</td>
<td>
    <input type="text" name="email" <?php 
    echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : ''; 
    ?>/>
	<a href="#" onclick="return false;">?<span>
    <p class="desc"><?php echo $words->get('SignupEmailDescription'); ?></p>
	</span></a>
</td>
<td>
    <?php
    if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
        echo '<span class="error">'.$words->get('SignupErrorInvalidEmail').'</span>';
    }
    ?>
    <p class="desc"><?php echo $words->get('SignupEmailShortDesc'); ?></p>
</td>
</tr>
    
<tr>
<td>
    <?php echo $words->get('SignupEmailCheck'); ?>
</td>
<td>
	<input type="text" name="emailcheck" <?php 
	echo isset($vars['emailcheck']) ? 'value="'.htmlentities($vars['emailcheck'], ENT_COMPAT, 'utf-8').'" ' : ''; 
	?>/>
</td>
<td>
    <?php
    if (in_array('SignupErrorEmailCheck', $vars['errors'])) {
        echo '<span class="error">'.$words->get('SignupErrorEmailCheck').'</span>';
    }
    ?>
    <p class="desc"><?php echo $words->get('SignupRetypeEmailShortDesc'); ?></p>
</td>
</tr>
        
<tr>
<td>
    <?php echo $words->get('SignupFeedback'); ?>
</td>
<td colspan="2">
    <textarea name="feedback" rows="10" cols="60" class="signuptexts"><?php 
    echo isset($vars['feedback']) ? htmlentities($vars['feedback'], ENT_COMPAT, 'utf-8') : '';
    ?></textarea>
	<a href="#" onclick="return false;">?<span>
	<p class="desc"><?php echo $words->get('SignupFeedbackDescription'); ?></p>
	</span></a>
</td>
</tr>

<tr>
<td colspan="3">
    <a href="/signup/termsandconditions" target="_blank">
        Please make sure you've read the terms and conditions by clicking this link<?php // FIXME: text is to be included in ww-table ?> 
    </a>
</td>
</tr>
    
<tr>
<td>
	<?php echo $words->get('IAgreeWithTerms'); ?>
</td>
<td>
	<input type="checkbox" name="terms" value="Yes" />
</td>
<td>
    <?php
    if (in_array('SignupMustacceptTerms', $vars['errors'])) {
        // SignupMustacceptTerms contains unknown placeholder
        echo '<span class="error">'.$words->get('SignupMustacceptTerms').'</span>';
    }
    ?>
</td>
</tr>

<tr>
<td colspan="3">
    <p>
        <input type="submit" value="<?php echo $words->get('SubmitForm'); ?>" class="submit"
        onClick="javascript:document.signup.javascriptactive.value = 'true'; return true;";
        />
        <input type="hidden" name="<?php
            // IMPORTANT: callback ID for post data 
        echo $callbackId; ?>" value="1"/>
    </p>
</td>
</tr>
</table>
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

$title = $words->get('SignupConfirmedPage');
// FIXME: set page title to $title

// TODO: typo in key: SignupResutlTextConfimation
$message = $words->getFormatted('SignupResutlTextConfimation', $vars['username'], $vars['email']);
PPostHandler::clearVars($callbackId);
echo '<h2>' . $title . '</h2>' . $message;
}
?>
