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
$gallery_selected = false;
$User = new APP_User;
$words = new MOD_words();    
$createText = array();
$errorText = array();
$i18n = new MOD_i18n('apps/trip/create.php');
$createText = $i18n->getText('createText');
$errorText = $i18n->getText('errorText');
?>
<form method="post" action="trip/create" class="def-form">
    <h2><?php echo $editing ? $words->get('Triptitle_edit') : $words->get('TripTitle_create'); ?></h2>
<?php
if (in_array('not_created', $errors)) {
    echo '<p class="error">'.$words->get('ErrorsTripNot_created').'</p>';
}
if (in_array('gallery_not_created', $errors)) {
    echo '<p class="error">'.$words->get('ErrorsGallery_not_created').'</p>';
}
?>    
    <fieldset id="trip-main">
        <legend><?=$words->get('TripLegend_main')?></legend>
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
<?php

$Gallery = new Gallery;
$galleries = $Gallery->getUserGalleries($User->getId());
?>
    <fieldset id="trip-options">
        <legend><?=$words->get('TripLegend_options')?></legend>
        <div class="row">
<?php
if ($galleries) { ?>
<img src="images/icons/picture_go.png"> <?=$words->get('TripAssignGallery')?>
<select name="gallery" size="1" onchange="$('trip-cgallery').checked = false;">
    <option value="">- <?=$words->get('TripAssignGallerySelect')?> -</option>
<?php
    foreach ($galleries as $d) {
    	echo '<option value="'.$d->id.'"';
        if (isset($vars['gallery']) && $vars['gallery'] == $d->id) {
            echo 'selected';
            $gallery_selected = $d->id;
        }
        echo '>'.$d->title.'</option>';
    }
?>
</select>
<?php }
// If it's a new trip and no gallery has been assigned yet, offer to create a new gallery
if (!$editing || !$gallery_selected) {
?>
            <input type="checkbox" id="trip-cgallery" name="cg" value="1"<?php
if (isset($vars['cg']) && $vars['cg'])
    echo ' checked="checked"';
            ?>/> <label for="trip-cgallery"><?=$words->get('TripLabel_create_gallery')?></label>
            <p class="desc"><?=$words->get('TripDesc_create_gallery')?></p>
<?php
}
?>
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
<script type="text/javascript">//<!--
createFieldsetMenu();
setFieldsetMenu('trip-main');
//-->
</script>
<?php
PPostHandler::clearVars($callbackId);
?>