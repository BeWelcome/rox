<?php
/**
 * trip createform template
 *
 * @package trip
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
$vars =& PPostHandler::getVars($callbackId);
if (isset($vars['errors']) && is_array($vars['errors']))
    $errors = $vars['errors'];
else
    $errors = array();
$member = $this->_model->getLoggedInMember();
$words = new MOD_words();    
$createText = array();
$errorText = array();
$i18n = new MOD_i18n('apps/trip/create.php');
$createText = $i18n->getText('createText');
$errorText = $i18n->getText('errorText');
?>
<form method="post" action="trip/create" class="def-form">
<?php
if (in_array('not_created', $errors)) {
    echo '<p class="error">'.$words->get('ErrorsTripNot_created').'</p>';
}
?>    
    <fieldset id="trip-main">
        <legend><?php echo $editing ? $words->get('Triptitle_edit') : $words->get('TripTitle_create'); ?></legend>
        <div class="row">
            <label for="trip-name"><?=$words->get('TripLabel_name')?></label><br/>
            <input type="text" id="trip-name" name="n" class="long"<?php
if (isset($vars['n']) && $vars['n'])
    echo ' value="'.htmlentities($vars['n'], ENT_COMPAT, 'utf-8').'"';
            ?>/>
<?php
if (in_array('name', $errors)) {
	echo '<span class="error">'.$words->get('ErrorsTripName').'</span>';
}
?>
            <p class="desc"></p>
        </div>
        <div class="row">
            <label for="trip-desc"><?=$words->get('TripLabel_desc')?></label><br/>
            <textarea id="trip-desc" name="d" cols="40" rows="7"><?php
if (isset($vars['d']) && $vars['d'])
    echo htmlentities($vars['d'], ENT_COMPAT, 'utf-8');
            ?></textarea>
            <p class="desc"><?=$words->get('TripDesc_desc')?></p>
        </div>
    </fieldset>
    <p>
<?php
	if (isset($vars['trip_id']) && $vars['trip_id']) {
		echo '<input type="hidden" name="trip_id" value="'.$vars['trip_id'].'" />';
	}
?>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" value="<?php echo $editing ? $words->get('TripSubmit_edit') : $words->get('TripSubmit_create');?>"/>
    </p>
</form>
<?php
PPostHandler::clearVars($callbackId);
?>
