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
$callback_tag = $formkit->setPostCallback('AdminController', 'treasurerEditCreateDonationCallback');

$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['id'] = $this->id;
    $vars['donate-username'] = $this->username;
    $vars['donate-amount'] = $this->amount;
    $vars['donate-date'] = $this->date;
    $vars['donate-country'] = $this->countrycode;
}

$words = new MOD_words();
?>
<form method="post">
<fieldset><legend><?php echo $words->get('AdminTreasurerAddDonation');?></legend>
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
<div class="subcolumns">
<div class="subcl">
<div class="c33l"><label for="donate-username"><?php echo $words->get('AdminTreasurerDonator'); ?></label><br />
<input type="text" id="donate-username" name="donate-username" value="<?php if (isset($vars['donate-username'])) { echo $vars['donate-username']; };  ?>" />
</div>
<div class="c33l"><label for="donate-amount"><?php echo $words->get('AdminTreasurerDonatedAmount'); ?></label><br />
<input type="text" id="donate-amount" name="donate-amount" value="<?php if (isset($vars['donate-amount'])) { echo $vars['donate-amount']; };  ?>" />
</div>
<div class="c33r">
<label for="donate-date"><?php echo $words->get('AdminTreasurerDonatedOn'); ?></label><br />
<input type="text" id="donate-date" name="donate-date" class="date" maxlength="10" <?php
echo isset($vars['donate-date']) ? 'value="'.htmlentities($vars['donate-date'], ENT_COMPAT, 'utf-8').'" ' : ''; ?> /><script type="text/javascript">
    /*<[CDATA[*/
    var datepicker	= new DatePicker({
    relative	: 'donate-date',
    language	: '<?=isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en'?>',
    current_date : '', 
    topOffset   : '25',
    relativeAppend : true
    });
    /*]]>*/
</script>
</div>
</div>
</div>
<div class="subcolumns">
    <label for="donate-country"><?php echo $words->get('AdminTreasurerSelectCountry'); ?></label>
    <select id="donate-country" name="donate-country" style="width:55em;">
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
<div class="float_right"><br /><input class="button" type="submit" name="addDonation" 
        value="<?php 
    if ($vars['id'] == 0) {
        echo $words->getBuffered('AdminTreasurerAddDonation');
    } else {
        echo $words->getBuffered('AdminTreasurerUpdateDonation');
    }
    ?>" /><?php echo $words->flushBuffer(); ?></div>
</fieldset>
</form>