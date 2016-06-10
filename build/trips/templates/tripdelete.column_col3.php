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
$callback_tag = $formKit->setPostCallback('TripController', 'deleteCallback');

$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars = $this->vars;
}

$member = $this->member;
$words = new MOD_words($this->getSession());
$panelTitle = $words->get('TripDelete_title', $vars['trip-title']);
?>
<div class="panel panel-default disabled">
    <div class="panel-heading"><?= $panelTitle ?></div>
    <div class="panel-body">
        <form method="post" class="form" role="form">
            <?= $callback_tag; ?>
            <!-- trip description -->
            <div class="form-group has-feedback">
                <label for="trip-desc" class="control-label sr-only"><?= $words->get('TripLabel_desc'); ?></label>
        <textarea disabled="disabled" class="form-control" id="trip-desc" name="trip-desc" cols="48" rows="7" placeholder="<?= $words->getBuffered('TripLabel_desc') ?>"><?php
            if (!empty($vars['trip-desc'])) {
                echo htmlentities($vars['trip-desc'], ENT_COMPAT, 'utf-8');
            } ?></textarea>
                <?php
                if (in_array('TripErrorDescEmpty', $errors)) {
                    echo '<span class="help-block alert alert-danger">'.$words->get('TripErrorDescEmpty').'</span>';
                }
                ?>
            </div>
            <input type="hidden" name="trip-id" value="<?= $vars['trip-id'] ?>" />
            <input type="submit" name="trip-no" class="btn btn-default btn-lg  pull-right hidden-xs" value="<?php echo $words->getSilent('No'); ?>" onclick="javascript: history.back();"/><?php echo $words->flushBuffer(); ?>
            <input type="submit" name="trip-yes" class="btn btn-default btn-lg  pull-right hidden-xs" value="<?php echo $words->getSilent('Yes');?>"/><?php echo $words->flushBuffer(); ?>
        </form>
    </div>
</div>
