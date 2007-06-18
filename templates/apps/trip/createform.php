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

$createText = array();
$errorText = array();
$i18n = new MOD_i18n('apps/trip/create.php');
$createText = $i18n->getText('createText');
$errorText = $i18n->getText('errorText');
?>
<form method="post" action="trip/create" class="def-form">
    <h2><?php echo $editing ? $createText['title_edit'] : $createText['title_create']; ?></h2>
<?php
if (in_array('not_created', $errors)) {
    echo '<p class="error">'.$errorText['not_created'].'</p>';
}
if (in_array('gallery_not_created', $errors)) {
    echo '<p class="error">'.$errorText['gallery_not_created'].'</p>';
}
?>    
    <fieldset id="trip-main">
        <legend><?=$createText['legend_main']?></legend>
        <div class="row">
            <label for="trip-name"><?=$createText['label_name']?></label><br/>
            <input type="text" id="trip-name" name="n" class="long"<?php
if (isset($vars['n']) && $vars['n'])
    echo ' value="'.htmlentities($vars['n'], ENT_COMPAT, 'utf-8').'"';
            ?>/>
<?php
if (in_array('name', $errors)) {
	echo '<span class="error">'.$errorText['name'].'</span>';
}
?>
            <p class="desc"></p>
        </div>
        <div class="row">
            <label for="trip-desc"><?=$createText['label_desc']?></label><br/>
            <textarea id="trip-desc" name="d" cols="40" rows="7"><?php
if (isset($vars['d']) && $vars['d'])
    echo htmlentities($vars['d'], ENT_COMPAT, 'utf-8');
            ?></textarea>
            <p class="desc"><?=$createText['desc_desc']?></p>
        </div>
    </fieldset>
<?php
if (!$editing) {
?>
    <fieldset id="trip-options">
        <legend><?=$createText['legend_options']?></legend>
        <div class="row">
            <input type="checkbox" id="trip-cgallery" name="cg" value="1"<?php
if (isset($vars['cg']) && $vars['cg'])
    echo ' checked="checked"';
            ?>/> <label for="trip-cgallery"><?=$createText['label_create_gallery']?></label>
            <p class="desc"><?=$createText['desc_create_gallery']?></p>
        </div>
    </fieldset>
<?php
}
?>
    <p>
<?php
	if (isset($vars['trip_id']) && $vars['trip_id']) {
		echo '<input type="hidden" name="trip_id" value="'.$vars['trip_id'].'" />';
	}
?>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" value="<?php echo $editing ? $createText['submit_edit'] : $createText['submit_create'];?>"/>
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