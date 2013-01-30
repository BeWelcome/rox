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
$callback_tag = $formkit->setPostCallback('AdminController', 'treasurerStartDonationCampaignCallback');

$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['donate-needed-per-year'] = $this->amount;
    $vars['donate-start-date'] = $this->date;
}

$words = new MOD_words();
?>
<form method="post">
<fieldset><legend><?php echo $words->get('AdminTreasurerStartDonationCampaign');?></legend>
<?php echo $callback_tag; 
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
<div class="c50l"><label for="donate-needed-per-year"><?php echo $words->get('AdminTreasurerNeededPerYear'); ?></label><br />
<input type="text" id="donate-needed-per-year" name="donate-needed-per-year" 
    value="<?php if (isset($vars['donate-needed-per-year'])) { echo $vars['donate-needed-per-year']; };  ?>" />
</div>
<div class="c33r">
<label for="donate-date"><?php echo $words->get('AdminTreasurerCampaignStartDate'); ?></label><br />
<input type="text" id="donate-start-date" name="donate-start-date" class="date" maxlength="10" <?php
echo isset($vars['donate-start-date']) ? 'value="'.htmlentities($vars['donate-start-date'], ENT_COMPAT, 'utf-8').'" ' : ''; ?> /><script type="text/javascript">
    /*<[CDATA[*/
    var datepicker	= new DatePicker({
    relative	: 'donate-start-date',
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
<div class="float_right"><br /><input class="button" type="submit" name="addDonation" 
        value="<?php echo $words->getBuffered('AdminTreasurerStartCampaign'); ?>" /><?php echo $words->flushBuffer(); ?>
</div>
</fieldset>
</form>