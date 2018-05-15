<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
/** 
 * @author shevek
 */

/** 
 * Tresasurer management overview template
 * 
 * @package Apps
 * @subpackage Admin
 */
$formkit = $this->layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('AdminTreasurerController', 'treasurerEditCreateDonationCallback');

$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['id'] = $this->id;
    $vars['donate-username'] = $this->username;
    $vars['donate-amount'] = $this->amount;
    $vars['donate-date'] = $this->date;
    $vars['donate-comment'] = $this->comment;
    $vars['donate-country'] = $this->countrycode;
}

$words = new MOD_words();
?>
<form method="post" class="yform full">
<?php echo $callback_tag; 
echo '<input type="hidden" id="id" name="id" value="' . $vars['id'] . '" />';
if (!empty($errors))
{
    echo '<div class="error">';
    foreach($errors as $error) {
        echo $words->get($error) . "<br />";
    }
    echo "</div>";
}
?>
<div class="container">
<div class="row">
        <div class="col-12">
            <label for="donate-username"><?php echo $words->get('AdminTreasurerDonor'); ?></label>
            <input type="text" id="donate-username" name="donate-username" class="form-control" value="<?php if (isset($vars['donate-username'])) { echo $vars['donate-username']; };  ?>" />
        </div>
</div>
    <div class="row">
        <div class="col12">
            <label for="donate-amount"><?php echo $words->get('AdminTreasurerDonatedAmount'); ?></label>
            <input type="text" id="donate-amount" name="donate-amount" class="form-control" value="<?php if (isset($vars['donate-amount'])) { echo $vars['donate-amount']; };  ?>" />
        </div>
    </div>
        <div class="row">
            <div class="col-12">
            <label for="donate-date"><?php echo $words->get('AdminTreasurerDonatedOn'); ?></label>
                    <div class="input-group date" id="donate-date-input" data-target-input="nearest">
                        <div class="input-group-prepend" data-target="#donate-date" data-toggle="datetimepicker">
                            <span class="input-group-text">
                                <i class="fa fa-calendar mt-2 mr-1"></i>
                            </span>
                        </div>
                        <input type="text" id="donate-date" name="donate-date" class="form-control datepicker" data-target="#donate-date" data-toggle="datetimepicker"
                               value="<?php if (isset($vars['donate-date'])) { echo $vars['donate-date']; };  ?>" />
                    </div>
            </div>
        </div>
</div>
    <div class="row">
<div class="col-12">
    <label for="donate-comment"><?php echo $words->get('AdminTreasurerComment'); ?></label>
    <input type="text" id="donate-comment" name="donate-comment" class="form-control" maxlength="100" value="<?php echo $vars['donate-comment'];?>" />
</div>
    </div>
<div class="row">
    <div class="col-12">
    <label for="donate-country"><?php echo $words->get('AdminTreasurerSelectCountry'); ?></label>
    <select id="donate-country" name="donate-country" class="select2 form-control">
    <option value="0"><?php echo $words->getBuffered('AdminTreasurerSelectACountry'); ?></option>
    <?php
    foreach($countries as $country) {
        echo '<option value="' . $country->iso_alpha2 . '"';
        if (isset($vars['donate-country']) && ($vars['donate-country'] == $country->iso_alpha2)) {
            echo ' selected="selected"';
        }
        echo '>' . $country->name . '</option>';
    }
    ?>
    </select><?php echo $words->flushBuffer(); ?>
    </div>
</div>
<div class="btn float-right">
          <input type="submit" class="button" name="addDonation" value="<?php 
            if ($vars['id'] == 0) {
            echo $words->getBuffered('AdminTreasurerAddDonation');
            } else {
            echo $words->getBuffered('AdminTreasurerUpdateDonation');
            }
            ?>" /><?php echo $words->flushBuffer(); ?>
</div>
</form>