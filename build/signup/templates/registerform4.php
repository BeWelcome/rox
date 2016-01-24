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
        <h3 class="card-title"><?php echo $words->getFormatted('SignupSummary')?></h3>
        <progress class="progress progress-success" value="100" max="100">
            <div class="progress">
                <span class="progress-bar" style="width: 100%;">100%</span>
            </div>
        </progress>
    </div>
    <div class="card-block">
        <form method="post" action="<?php echo $baseuri.'signup/4'?>" name="signup" id="user-register-form">
<?=$callback_tag ?>
<?php
    if (in_array('inserror', $vars['errors'])) {
        echo '<small class="text-muted has-danger">'.$errors['inserror'].'</small>';
    }
?>

    <!-- Login information -->
            <div class="card">
                <div class="card-header"><b>Login info</b></div>
            <div class="card-block">
        <!-- username -->
        <div class="form-group">
            <label for="register-username" class="<?=(in_array('SignupErrorWrongUsername', $vars['errors']) || in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) ? 'control-label sr-only' : 'control-label'; ?>"><?php echo $words->get('SignupUsername'); ?></label>
            <?=(in_array('SignupErrorWrongUsername', $vars['errors']) || in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) ? '' : '<p class="form-control-static">'.$vars['username'].'</p>'; ?>
            <input <?=(in_array('SignupErrorWrongUsername', $vars['errors']) || in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) ? 'type="text"' : 'type="hidden"'?> id="register-username" class="form-control" name="username"
            placeholder="<?php echo $words->get('SignupUsername'); ?>"
            <?php
            echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> >
             <?php
          if (in_array('SignupErrorWrongUsername', $vars['errors'])) {
              echo '<small class="text-muted has-danger">'.$words->get('SignupErrorWrongUsername').'</small>';
          }
          if (in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) {
              echo '<small class="text-muted has-danger">'.
                  $words->getFormatted('SignupErrorUsernameAlreadyTaken', $vars['username']).
                  '</small>';
          }
          ?>
        </div>

        <!-- password -->
        <?php if (in_array('SignupErrorPasswordCheck', $vars['errors'])) { ?>
        <div class="form-group">
            <label for="register-password" class="control-label sr-only"><?php echo $words->get('SignupPassword'); ?></label>
            <input type="password" id="register-password" class="form-control" name="password" placeholder="<?php echo $words->get('SignupPassword'); ?>"
            <?php
                echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
            ?>
            >
        </div>

        <!-- confirm password -->
        <div class="form-group">
            <label for="register-passwordcheck" class="control-label sr-only"><?php echo $words->get('SignupCheckPassword'); ?></label>
            <input type="password" id="register-passwordcheck" class="form-control" name="passwordcheck" placeholder="<?php echo $words->get('SignupCheckPassword'); ?>"
            <?php
                echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
            ?>
            >
            <?php
            if (in_array('SignupErrorPasswordCheck', $vars['errors'])) {
                echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorPasswordCheck').'</span>';
            }
            ?>
        </div>
        <?php
            } else {
        ?>
        <div class="form-group">
              <label for="password" class="control-label"><?php echo $words->get('SignupPassword'); ?></label>
              <?php  echo '<p class="form-control-static">********</p>'; ?>
        </div>
        <?php
            }
        ?>

        <!-- email -->
        <div class="form-group">
            <label for="register-email" class="control-label sr-only"><?php echo $words->get('SignupEmail'); ?></label>
            <input class="form-control" <?=(in_array('SignupErrorInvalidEmail', $vars['errors']) || in_array('SignupErrorEmailCheck', $vars['errors']) || in_array('SignupErrorEmailAddressAlreadyInUse', $vars['errors'])) ? 'type="email"':'type="hidden"'?>
            id="register-email" name="email" placeholder="<?php echo $words->get('SignupEmail'); ?>"
            <?php
                echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?>
            >
        </div>

        <!-- confirm email -->
        <div class="form-group">
            <?php
            
            if (in_array('SignupErrorEmailCheck', $vars['errors']) || in_array('SignupErrorInvalidEmail', $vars['errors']) || in_array('SignupErrorEmailAddressAlreadyInUse', $vars['errors'])) { ?>
                <label for="register-emailcheck" class="control-label sr-only"><?php echo $words->get('SignupCheckEmail'); ?></label>
                <input type="text" class="form-control" id="register-emaildcheck" name="emailcheck" placeholder="<?php echo $words->get('SignupCheckEmail'); ?>" <?php
                    echo isset($vars['emailcheck']) ? 'value="'.$vars['emailcheck'].'" ' : ''; ?>
                >
            <?php
                if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
                    echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorInvalidEmail').'</span>';
                } else if (in_array('SignupErrorEmailCheck', $vars['errors'])) {
                    echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorEmailCheck').'</span';
                } else if (in_array('SignupErrorEmailAddressAlreadyInUse', $vars['errors'])) {
                    echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorEmailAddressAlreadyInUse').'</span>';
                }
            ?>
            <?php } else { ?>
                <label for="register-emailcheck" class="control-label"><?php echo $words->get('SignupCheckEmail'); ?></label>
                <?php echo '<p class="form-control-static">'.$vars['email'].'</p>';
            } ?>
        </div>
            </div>
                </div>
            <div class="card">
    <div class="card-header"><b><?php echo $words->get('SignupName'); ?></b></div>
<div class="card-block">
    <!-- panel-body: Personal information -->
        <?php
          ?>
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
      </div>

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
        <!-- Sweet -->
        <div class="form-group sweet">
            <label for="sweet" class="control-label sr-only"><?php echo $words->get('SignupSweet'); ?></label>
            <input type="text" id="sweet" name="sweet" value="" title="Leave free of content"/>
          </div>
       <?php endif;
        if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
        ?>
        
        <!-- First Name -->
        <div class="form-group">
            <label for="firstname" class="control-label sr-only"><?php echo $words->get('FirstName'); ?></label>
            <input id="firstname"  class="form-control" name="firstname" placeholder="<?php echo $words->get('FirstName'); ?>"
            <?php
            echo isset($vars['firstname']) ? 'value="'.htmlentities(strip_tags($vars['firstname']), ENT_COMPAT, 'utf-8').'" ' : '';
            ?>
            >
        </div>

        <!-- Second Name -->
        <div class="form-group">
            <label for="secondname" class="control-label sr-only"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
            <input id="secondname" class="form-control" name="secondname" placeholder="<?php echo $words->get('SignupSecondNameOptional'); ?>" 
            <?php
                echo isset($vars['secondname']) ? 'value="'.htmlentities(strip_tags($vars['secondname']), ENT_COMPAT, 'utf-8').'" ' : '';
            ?> 
            >
        </div>

        <!-- Last Name -->
        <div class="form-group">
            <label for="lastname" class="control-label sr-only"><?php echo $words->get('LastName'); ?></label>
            <input id="lastname" class="form-control" name="lastname" placeholder="<?php echo $words->get('LastName'); ?>"
            <?php
                echo isset($vars['lastname']) ? 'value="'.htmlentities(strip_tags($vars['lastname']), ENT_COMPAT, 'utf-8').'" ' : '';
            ?>
            >
        </div>
        <?php echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorFullNameRequired').'</span>';
        
        } else { ?>
        
        <div class="form-group">
            <label for="firstname" class="control-label"><?php echo $words->get('Name'); ?></label>
            <input type="hidden"  class="form-control" id="firstname" name="firstname" placeholder="<?php echo $words->get('SignupCheckEmail'); ?>" 
            <?php
                echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?>
            >
            <input type="hidden" class="form-control" id="secondname" name="secondname" placeholder="<?php echo $words->get('SignupCheckEmail'); ?>"
            <?php
                echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?>
            >
            <input type="hidden" class="form-control" id="lastname" name="lastname" placeholder="<?php echo $words->get('SignupCheckEmail'); ?>" 
            <?php
                echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?>
            >
            <p class="form-control-static"><?=strip_tags($vars['firstname']) .' ' . strip_tags($vars['secondname']) .' '. strip_tags($vars['lastname']) ;?></p>
        </div>

        <?php }
        $disable = true;
        if (in_array('SignupErrorNoMotherTongue', $vars['errors'])) {
            echo '<span class="help-block alert alert-danger">' . $words->get('SignupErrorNoMotherTongue') . '</span>';
            $disable = false;
        }
        ?>
        

        <!-- Mother tongue(s)-->
        <div class="form-group">
          <label for="mothertongue" class="control-label sr-only"><?php echo $words->get('LanguageLevel_MotherLanguage'); ?></label>
          <select class="select2 form-control" name="mothertongue" id="mothertongue" data-placeholder="<?= $words->getBuffered('SignupSelectMotherTongue')?>"
          <?= ($disable) ? 'disabled="disabled"' : ''?> >
              <option></option>
              <option value=""></option>
              <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                <?= $this->getAllLanguages(true, $vars['mothertongue']); ?>
            </optgroup>
            <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                <?= $this->getAllLanguages(false, $vars['mothertongue']); ?>
            </optgroup>
          </select>
    	</div>

        <!-- Birthdate -->
        <div class="form-group">
            <label for="BirthDate" class="control-label sr-only"><?php echo $words->get('SignupBirthDate'); ?></label>
            <?php
            if (in_array('SignupErrorBirthDate', $vars['errors']) || in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
            ?>
            <div class="form-inline">
                <div class="form-group">
                    <select id="BirthDate" name="birthyear" class="form-control">
                        <option value=""><?php echo $words->get('SignupBirthYear'); ?></option>
                        <?php echo $birthYearOptions; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="birthmonth" class="form-control">
                        <option value=""><?php echo $words->get('SignupBirthMonth'); ?></option>
                        <?php for ($i=1; $i<=12; $i++) { ?>
                        <option value="<?php echo $i; ?>"<?php
                        if (isset($vars['birthmonth']) && $vars['birthmonth'] == $i) {
                            echo ' selected="selected"';
                        }
                        ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="birthday" class="form-control">
                        <option value=""><?php echo $words->get('SignupBirthDay'); ?></option>
                        <?php for ($i=1; $i<=31; $i++) { ?>
                        <option value="<?php echo $i; ?>"<?php
                        if (isset($vars['birthday']) && $vars['birthday'] == $i) {
                            echo ' selected="selected"';
                        }
                        ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
                <?php
            } else {
            ?>
              <label for="BirthDate" class="control-label"><?php echo $words->get('SignupBirthDate'); ?></label>
              <p class="form-control-static">
              <input type="hidden" class="form-control" id="BirthDay" name="birthday" value="<?php
                if (isset($vars['birthday'])) {
                    echo $vars['birthday'];
                }
              ?>">
              <?=$vars['birthday'].'.';?>
              <input type="hidden" class="form-control" id="BirthMonth" name="birthmonth" value="<?php
                if (isset($vars['birthmonth'])) {
                    echo $vars['birthmonth'];
                }
              ?>"><?=$vars['birthmonth'].'.';?>
              <input type="hidden" class="form-control" id="BirthYear" name="birthyear" value="<?php
                if (isset($vars['birthyear'])) {
                    echo $vars['birthyear'];
                }
              ?>"><?=$vars['birthyear'];?>
              </p>
                <?php
            }
              if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                  echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorBirthDate').'</span>';
              }
              if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                  echo '<span class="help-block alert alert-danger">'.$words->getFormatted('SignupErrorBirthDateToLow',SignupModel::YOUNGEST_MEMBER).'</span>';
              }
    
              ?>
        </div> <!-- form-group -->

        <!-- Gender -->
        <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) { ?>
        <div class="form-group">
                <label class="radio-inline">
                <input type="radio" id="gender" name="gender" value="female" 
                <?php
                if (isset($vars['gender']) && $vars['gender'] == 'female') {
                    echo ' checked="checked"';
                } ?>
                >
                <?php echo $words->get('female'); ?>
            </label>
            <label class="radio-inline">
                <input type="radio" name="gender" value="male"
                <?php
                if (isset($vars['gender']) && $vars['gender'] == 'male') {
                    echo ' checked="checked"';
                } ?>
                >
                <?php echo $words->get('male');?>
            </label>
            <label class="radio-inline">
                <input class="radio" type="radio" name="gender" value="other"
                <?php
                if (isset($vars['gender']) && $vars['gender'] == 'other') {
                    echo ' checked="checked"';
                } ?>
                >
                <?php echo $words->get('Genderother');?>
            </label>>
        </div>
        <span class="help-block alert alert-danger"><?=$words->get('SignupErrorProvideGender') ?></span>
        <?php } else { ?>
        <div class="form-group">
            <label for="gender" class="control-label"><?php echo $words->get('Gender'); ?></label
            <input type="hidden" id="gender" name="gender" 
            <?php 
            echo isset($vars['gender']) ? 'value="'.$vars['gender'].'" ' : '';
            ?>
            >
            <p class="form-control-static"><?php echo $vars['gender'] ?></p>
            <?php   if  (!isset($vars['gender'])) { ?>
                    <p class="form-control-static"> - </p>
            <?php }?>
        </div>
        <?php } ?>
</div>

</div>
        <div class="card">
            <div class="card-header"><b><?php echo $words->get('Location'); ?></b></div>
            <div class="card-block">
        <div class="form-group">
            <label for="location-geoname-id" class="control-label sr-only"><?php echo $words->get('Location'); ?></label>
    <?php if (in_array('SignupErrorProvideLocation', $vars['errors'])) { ?>
            <p class="form-control-static"><a href="signup/3" class="button" title="<?php echo $words->get('label_setlocation'); ?>" ><?php echo $words->get('label_setlocation'); ?></a></p>
            <span class="text-muted alert alert-danger"><?=$words->get('SignupErrorProvideLocation')?></span>
    <?php } else { ?>
            <input type="hidden" id="location-geoname-id" name="location-geoname-id" <?php
                echo isset($vars['location-geoname-id']) ? 'value="'.htmlentities($vars['location-geoname-id'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?>
            >
            <p class="form-control-static"><?= urldecode($vars['location']);?></p>
    <?php } ?>
    
            <input type="hidden" name="location-latitude" id="location-latitude" value="<?php
                    echo isset($vars['location-latitude']) ? htmlentities($vars['location-latitude'], ENT_COMPAT, 'utf-8') : '';
                ?>" >
            <input type="hidden" name="location-longitude" id="location-longitude" value="<?php
                    echo isset($vars['location-longitude']) ? htmlentities($vars['location-longitude'], ENT_COMPAT, 'utf-8') : '';
                ?>" >
        </div>
</div>
            </div>

                <div class="card">
        <div class="card-header"><b><?php echo $words->get('SignupFeedback'); ?></b></div>
                    <div class="card-block">
        <!-- Feedback -->
        <div class="form-group">
            <p><?php echo $words->get('SignupFeedbackDescription'); ?></p>
            <textarea class="form-control" name="feedback" rows="10"><?php
            echo isset($vars['feedback']) ? htmlentities($vars['feedback'], ENT_COMPAT, 'utf-8') : '';
            ?></textarea>
        </div>

        <!-- terms -->
        <div class="checkbox">
            <label>
            <input type="checkbox" id="terms" name="terms" required
            <?php
            if (isset ($vars["terms"])) echo " checked=\"checked\"" ; // if user has already clicked, we will not bore him again
                echo " >";
            ?>
            <?php echo $words->get('IAgreeWithTerms'); ?>
            </label>
        </div>
        <?php
        if (in_array('SignupMustAcceptTerms', $vars['errors'])) {
                echo '<small class="text-muted alert alert-danger">'.$words->get('SignupTermsAndConditions').'</small>';
        }
        ?>
                    </div>
                    </div>
            <input type="submit" class="form-control btn btn-primary" value="<?php echo $words->getSilent('SubmitForm'); ?>">
            <?php echo $words->flushBuffer(); ?>

</form>
    </div>
</div> <!-- signup2 -->
<script type="text/javascript">
    jQuery(".select2").select2(); // {no_results_text: "<?= htmlentities($words->getSilent('SignupNoLanguageFound'), ENT_COMPAT); ?>"});
</script>
