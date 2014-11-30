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
<div id="signuprox2">
<!-- Custom BeWelcome signup progress bar -->
<div class="progress">
    <div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            1. <?php echo $words->getFormatted('LoginInformation')?>
        </span>
        <span class="bw-progress visible-xs-inline">
            Schritt 1.
        </span>
    </div>
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            2. <?php echo $words->getFormatted('SignupName')?>
        </span>
        <span class="bw-progress visible-xs-inline">
            Schritt 2.
        </span>
    </div>
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            3. <?php echo $words->getFormatted('Location')?>
        </span>
        <span class="bw-progress visible-xs-inline">
            Schritt 3.
        </span>
    </div>
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            4. <?php echo $words->getFormatted('SignupSummary')?>
        </span>
        <span class="bw-progress visible-xs-inline">
            Schritt 4.
        </span>
    </div>
</div>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    <?php echo $words->get('SignupIntroductionTitle'); ?>
                </a>
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
            <?php echo $words->get('SignupIntroduction'); ?>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo $words->get('LoginInformation'); ?><small class="pull-right">Bitte fülle alle Felder aus.</small></h3> 
  </div>
  <div class="panel-body">
<form method="post" action="<?php echo $baseuri.'signup/2'?>" name="signup" id="user-register-form" class="form" role="form">
    <?=$callback_tag ?>
    <input type="hidden" name="javascriptactive" value="false" />
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
    <!-- Next step button -->
    <input type="submit" class="btn btn-default btn-lg pull-right hidden-xs" value="<?php echo $words->getSilent('NextStep'); ?>"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    /><?php echo $words->flushBuffer(); ?>
    <input type="submit" class="btn btn-default btn-lg pull-right btn-block visible-xs-block" value="<?php echo $words->getSilent('NextStep'); ?>"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    /><?php echo $words->flushBuffer(); ?>
</form>
  </div>
</div>
</div> <!-- signuprox2 -->

<script type="text/javascript">
 Register.initialize('user-register-form');
</script>
