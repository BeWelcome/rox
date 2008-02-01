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


require_once ("menus.php");
// Warning this page is not a good sample for layout
// it contain too much logic/algorithm - May be the signup page is to be an exception ?-

function DisplaySignupFirstStep($Username = "", $FirstName = "", $SecondName = "", $LastName = "", $Email = "", $EmailCheck = "", $pIdCountry = 0, $pIdCity = 0, $HouseNumber = "", $StreetName = "", $Zip = "", $ProfileSummary = "", $SignupFeedback = "", $Gender = "", $password = "", $secpassword = "", $SignupError = "", $BirthDate = "", $HideBirthDate = "No", $HideGender = "No",$CityName="") {
	global $title;
	$title = ww('Signup');

	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu
	
	DisplayHeaderShortUserContent(ww("Signup Page")); // Display the header
  $strconfirm=str_replace("<br />", " ", addslashes(ww("SignupConfirmQuestion"))) ;
  $strconfirm=str_replace("\r\n", " ", $strconfirm) ;
?>
  <script src="lib/select_area.js" type="text/javascript"></script>

<?php

echo "\n<script type=\"text/javascript\">\n" ;
echo "<!--\n" ;
echo "  function check_form() {\n" ;

echo "	   if (!document.signup.Terms.checked) { \n";
echo "        alert(\"",ww("SignupMustacceptTerms"),"\");\n";
echo "        return(false);\n";
echo "    }\n" ;

echo "    if (confirm('", $strconfirm, "')) {\n" ;
echo "        document.signup.submit() ;\n" ;
echo "    }\n" ;
echo "  }\n" ;
echo "// -->\n" ;
echo "</script>\n" ;  




	$IdCountry = $pIdCountry;
	$IdCity = $pIdCity;
	$scountry = ProposeCountry($IdCountry, "signup");
	$scity = ProposeCity($IdCity, 0, "signup",$CityName,$IdCountry);
?>
  
<div>
<div>
<div id="signup">
<!-- signup introduction goes here -->
<h2><?php echo ww('WelcomeToSignup'); ?></h2>
<?php
	if ($SignupError != "") {
		echo "<h4>". ww("SignupPleaseFixErrors")."</h4><p class=\"error\">", $SignupError, "</p>\n";
	} else {
		echo "<p class=\"note\">". ww('SignupIntroduction')."</p>\n";
	}
?>


<form method="post" name="signup" action="signup.php">
  <input type="hidden" name="action" value="SignupFirstStep" />

  <fieldset>
    <legend class="icon world22"><?php echo ww('SignupLocation'); ?></legend>
        
      <ul>
        <li>
          <label for="IdCountry"><?php echo ww('SignupCountry'); ?>* </label><br />
          <?php echo $scountry; ?>
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww('SignupIdCityDescription'); ?></span></a><br />
        </li>
      </ul>
      <ul class="input_float clearfix">
        <li>
          <input type="hidden" name="IdRegion" value="0" />
          <?php
              if ($IdCountry!=0) {
          ?>
            <label for="CityName"><?php echo ww("City") ?>*</label><br />
            <input type="text" id="CityName" name="CityName" size="30" value="<?php echo $CityName ?>" onChange="change_region('signup')">
        </li>
        <li class="number">
          <label for="Zip"><?php echo ww('SignupZip') ?>*</label><br />
          <input name="Zip" type="text" id="Zip" size="6" value="<?php echo $Zip ?>" />
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww("SignupZipDescription") ?></span></a><br />
        </li>
      </ul>
      <?php
        	}
        	echo $scity;
         ?>
        </li>
      </ul>
      
      <ul class="input_float clearfix">
        <li>
          <label for="Street"><?php echo ww('SignupStreetName') ?>*</label><br />
          <input type="text" id="Street" name="StreetName" value="<?php echo $StreetName; ?>" />
        </li>
        <li class="number">
          <label for="HouseNumber"><?php echo ww('SignupHouseNumber'); ?>*</label><br />
          <input type="text" id="HouseNumber" name="HouseNumber" value="<?php echo $HouseNumber; ?>" />
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww('SignupStreetNameDescription'); ?></span></a><br />
        </li>
      </ul>
  </fieldset>  
  
<!-- Login Information -->
  <fieldset>
    <legend class="icon login22"><?php echo ww('SignupLoginInformation'); ?></legend>

      <ul>

    <!-- username -->
        <li>
          <label for="Username"><?php echo ww('SignupUsername') ?>* <span class="small"><?php echo ww("SignupUsernameShortDesc") ?></span></label><br />
          <input type="text" id="Username" name="Username" value="<?php echo $Username; ?>" />
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww('SignupUsernameDescription'); ?></span></a><br />
        </li>

    <!-- password -->
        <li>
          <label for="password"><?php echo ww('SignupPassword') ?>* <span class="small"><?php echo ww('SignupPasswordChoose'); ?></span></label><br />
          <input type="password" id="password" name="password" />
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww('SignupPasswordDescription'); ?></span></a><br />
       </li>

    <!-- confirm password -->
        <li>
          <label for="passwordcheck"><?php echo ww('SignupCheckPassword'); ?>* <span class="small"><?php echo ww('SignupPasswordConfirmShortDesc'); ?></span></label><br />
          <input type="password" id="passwordcheck" name="secpassword" value="<?php echo $secpassword; ?>"/><br />
        </li>

    <!-- email -->
        <li>
          <label for="Email"><?php echo ww('SignupEmail'); ?>* <span class="small"><?php echo ww('SignupEmailShortDesc'); ?></span></label><br />
          <input type="text" id="Email" name="Email" value="<?php echo $Email; ?>" />
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww('SignupEmailDescription'); ?></span></a><br />
        </li>

    <!-- confirm email-->
        <li>
          <label for="Emailcheck"><?php echo ww('SignupEmailCheck'); ?>* <span class="small"><?php echo ww('SignupRetypeEmailShortDesc'); ?></span></label><br />
          <input type="text" id="Emailcheck" name="EmailCheck" value="<?php echo $EmailCheck; ?>" /><br />
        </li>
        
      </ul>
  </fieldset>

<!-- Personal Information -->  
  <fieldset>
    <legend class="icon contact22"><?php echo ww('SignupPersonalInformation'); ?></legend>

      <ul> 

    <!-- First Name -->
        <li>
          <label for="FirstName"><?php echo ww("FirstName"); ?>* </label><br />
          <input type="text" id="FirstName" name="FirstName" value="<?php echo$FirstName; ?>" />
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww('SignupNameDescription'); ?></span></a><br />
        </li>

    <!-- Second Name -->
        <li>
          <label for="SecondName"><?php echo ww("SignupSecondNameOptional"); ?></label><br />
          <input type="text" id="SecondName" name="SecondName" value="<?php echo$SecondName; ?>" /><br />
        </li>

    <!-- Last Name -->
        <li>
          <label for="LastName"><?php echo ww("LastName"); ?>* </label><br />
          <input type="text" id="LastName" name="LastName" value="<?php echo$LastName; ?>" /><br />
        </li>      

    <!-- Birthdate -->      
        <li>
          <label for="BirthDate"><?php echo ww('SignupBirthDate'); ?>* <span class="small"><?php echo ww('SignupBirthDateShape'); ?></span></label><br />
          <input type="text" id="BirthDate" name="BirthDate" value="<?php echo$BirthDate; ?>" />
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww('SignupBirthDateDescription'); ?></span></a><br />
        </li>

    <!-- Gender -->
        <li>
          <label for="Gender"><?php echo ww('gender'); ?>*</label><br />
          <select id="Gender" name="Gender">
            <option value=""></option>
            <option value="male"
            <?php
            if ($Gender == "male")
                echo " selected";
            echo ">", ww("male"), "</option>";
            ?>
            <option value="female"
            <?php
            if ($Gender == "female")
                echo " selected";
            echo ">", ww("female"), "</option>";
            ?>
          </select>
          <a href="#" class="tooltip">
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo ww('SignupGenderDescription'); ?></span></a><br />
        </li>
        
      </ul>
  </fieldset>
  
  <fieldset>
    <legend class="icon info22"><?php echo ww('SignupFeedback'); ?></legend>
    <p><?php echo ww('SignupFeedbackDescription'); ?></p>
    <textarea name="feedback" cols="60" rows="10"></textarea>
  </fieldset>  
  
  <h4><?php echo "<a href='../terms'>".ww('SignupTermsAndConditions')."</a>"; ?></h4>
  <p class="checkbox"><input type="checkbox" name="Terms"
  <?php
	if (GetStrParam("Terms","")!="") echo " checked" ; // if user has already click, we will not bore him again
	echo " />";
  ?>
  <?php echo ww('IAgreeWithTerms'); ?></p>
  <p><input id="signupsubmit" type="submit" class="button" onclick="check_form();"  value="<?php echo ww('SignupSubmit'); ?>"  /></p>
  
  
</form>  
</div> <!-- signup -->

<?php
	require_once "footer.php";
}

function DisplaySignupResult($Message) {
	global $title;
	$title = ww('SignupConfirmedPage');

	require_once "header.php";

	//	Menu1("error.php",ww('MainPage')); // Displays the top menu
	//	Menu2($_SERVER["PHP_SELF"]); // Display the second menu

	Menu1("", ww("SignupConfirmedPage")); // Displays the top menu
	DisplayHeaderShortUserContent(ww("SignupConfirmedPage"));
?>

  <div class="info">
    <p class="note"><?php echo $Message ?></p>
  </div>

<?php
	require_once "footer.php";
	exit (0); // To be sure that the member won't go further after 
}
?>
