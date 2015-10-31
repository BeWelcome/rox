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
             <h4 class="card-title"><?php echo $words->get('LoginInformation'); ?><small class="pull-right">Bitte fülle alle Felder aus.</small></h4>
        </div>
    <div class="card-block">
        <div>
            <div class="card ">
                <div class="card-header" role="tab" id="headingOne">
                    <h4 class="card-title">
                        <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <?php echo $words->get('SignupIntroductionTitle'); ?>
                        </a>
                    </h4>
                </div>
                <div id="collapseOne" class="card-block collapse" role="tabpanel" aria-labelledby="headingOne">
                    <div class="card-block">
                        <?php echo $words->get('SignupIntroduction'); ?>
                    </div>
                </div>
            </div>
        </div>
<form method="post" action="<?php echo $baseuri.'signup/2'?>" name="signup" id="user-register-form" class="form" role="form">
    <?=$callback_tag ?>
    <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<span class="help-block alert alert-danger">'.$errors['inserror'].'</span>';
        }
    ?>
    <!-- username -->
    <div class="form-group has-feedback">
        <label for="register-username" class="control-label sr-only"><?php echo $words->get('SignupUsername'); ?></label>
        <input type="text" class="form-control" name="username" placeholder="<?php echo $words->get('SignupUsername'); ?>" id="register-username" 
        <?php
        echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
        ?> >

        <?php
        if (in_array('SignupErrorWrongUsername', $vars['errors'])) {
            echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorWrongUsername').'</span>';
        }
        if (in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) {
            echo '<span class="help-block alert alert-danger">'.
                $words->getFormatted('SignupErrorUsernameAlreadyTaken', $vars['username']).
            '</span>';
        }
        ?>
        <span class="help-block"><?=$words->get('subline_username')?></span>
    </div>
    
    <!-- password -->
    <div class="form-group has-feedback">
        <label for="register-password" class="control-label sr-only"><?php echo $words->get('SignupPassword'); ?></label>
        <input type="password" class="form-control" id="register-password" name="password" placeholder="<?php echo $words->get('SignupPassword'); ?>"
        <?php
        echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
        ?> >
    </div>

    <!-- confirm password -->
    <div class="form-group has-feedback">
        <label for="register-passwordcheck" class="control-label sr-only"><?php echo $words->get('SignupCheckPassword'); ?></label>
        <input type="password" class="form-control" id="register-passwordcheck" name="passwordcheck" placeholder="<?php echo $words->get('SignupCheckPassword'); ?>"
        <?php
        echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
        ?> >
        <?php
        if (in_array('SignupErrorPasswordCheck', $vars['errors'])) {
          echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorPasswordCheck').'</span>';
        }
        ?>
    </div>
    
    <!-- email -->
    <div class="form-group has-feedback">
        <label for="register-email" class="control-label sr-only"><?php echo $words->get('SignupEmail'); ?></label>
        <input type="text" class="form-control" id="register-email" name="email" placeholder="<?php echo $words->get('SignupEmail'); ?>"
        <?php
        echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : '';
        ?> />
    </div>

    <!-- confirm email -->
    <div class="form-group has-feedback">
        <label for="register-emailcheck" class="control-label sr-only"><?php echo $words->get('SignupCheckEmail'); ?></label>
        <input type="text" class="form-control" id="register-emailcheck" name="emailcheck" placeholder="<?php echo $words->get('SignupCheckEmail'); ?>"
        <?php
          echo isset($vars['emailcheck']) ? 'value="'.$vars['emailcheck'].'" ' : '';
        ?> />
        <?php
          if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
              echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorInvalidEmail').'</span>';
          } elseif (in_array('SignupErrorEmailCheck', $vars['errors'])) {
              echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorEmailCheck').'</span>';
          } elseif (in_array('SignupErrorEmailAddressAlreadyInUse', $vars['errors'])) {
              echo '<span class="help-block alert alert-danger">'.$words->get('SignupErrorEmailAddressAlreadyInUse').'</span>';
          } else {
                echo '<br>';
          }
          ?>
    </div>
        <input type="submit" class="form-control btn btn-primary" value="<?php echo $words->getSilent('NextStep'); ?>" /><?php echo $words->flushBuffer(); ?>
</form>
</div>
</div>
