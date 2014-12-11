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
$formKit = $this->layoutkit->formkit;
$callback_tag = $formKit->setPostCallback('TripController', 'editCreateCallback');

$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars = $this->vars;
}

$member = $this->member;
$words = $this->getWords();
if ($this->_editing == true) {
    $panelTitle = $words->get('Triptitle_edit');
    $buttonTitle = $words->getSilent('TripSubmit_edit');
} else{
    $panelTitle = $words->get('TripTitle_create');  
    $buttonTitle =  $words->getSilent('TripSubmit_create');
}  
?>
<div class="panel panel-default">
    <div class="panel-heading"><?= $panelTitle ?></div>
    <div class="panel-body">
    <form method="post" class="form" role="form">
        <?= $callback_tag; ?>
        <div class="form-group has-feedback">
            <label for="trip-name" class="control-label sr-only"><?php echo $words->get('TripLabel_name'); ?></label>
            <input type="text" class="form-control" name="trip-name" placeholder="<?= $words->getBuffered('TripLabel_name'); ?>" id="trip-name"
                value="<?= htmlentities($vars['trip-name'], ENT_COMPAT, 'utf-8') ?>" />
        <?php
        if (in_array('TripErrorNameEmpty', $errors)) {
            echo '<span class="help-block alert alert-danger">'.$words->get('TripErrorNameEmpty').'</span>';
        }
        ?>
        </div>

        <!-- password -->
        <div class="form-group has-feedback">
            <label for="trip-desc" class="control-label sr-only"><?= $words->get('TripLabel_desc'); ?></label>
            <textarea class="form-control" id="trip-desc" name="trip-desc" cols="48" rows="7" placeholder="<?= $words->getBuffered('TripLabel_desc') ?>"><?php
                if (!empty($vars['trip-desc'])) {
                    echo htmlentities($vars['trip-desc'], ENT_COMPAT, 'utf-8');
                } ?></textarea>
            <?php
            if (in_array('TripErrorDescEmpty', $errors)) {
                echo '<span class="help-block alert alert-danger">'.$words->get('TripErrorDescEmpty').'</span>';
            }
            ?>
            <span class="help-block"><?=$words->get('TripDesc_desc2')?></span>
        </div>
		<input type="hidden" name="trip-id" value="<?= $vars['trip-id'] ?>" />
        <input type="submit" class="btn btn-default btn-lg  pull-right hidden-xs" value="<?= $buttonTitle ?>"/><?php echo $words->flushBuffer(); ?>
    </form>
    </div>
</div>