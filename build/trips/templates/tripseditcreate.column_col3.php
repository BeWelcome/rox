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
$callback_tag = $formKit->setPostCallback('TripsController', 'editCreateCallback');

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
        <div class="panel panel-default">
            <div class="panel-heading"><?= $words->get("TripsAddLocations") ?></div>
            <div class="panel-body">
                <div class="row">
                    <input type="text" name="trip-location"
                    <div class="form-group has-feedback col-md-6">
                        <label for="trip-location-1" class="control-label sr-only"><?php echo $words->get('TripLocation'); ?></label>
                        <div class="input-group">
                            <input type="text" class="form-control location-picker" name="trip-location-1" placeholder="<?= $words->getBuffered('TripLocation'); ?>" id="search-location"
                                   value="" />
                            <label for="trip-location-1" class="control-label input-group-addon btn"><span class="fa fa-map-marker"></span></label>
                        </div>
                    </div>
        <div class="form-group has-feedback col-md-3">
            <label for="trip-startdate-1" class="control-label sr-only"><?php echo $words->get('TripDateStart'); ?></label>
            <div class="input-group">
                <input type="text" class="form-control date-picker" name="trip-startdate-1" placeholder="<?= $words->getBuffered('TripDateStart'); ?>" id="trip-startdate-1"
                       value="" />
                <label for="trip-startdate-1" class="control-label input-group-addon btn"><span class="fa fa-calendar"></span></label>
            </div>
        </div>
        <div class="form-group has-feedback col-md-3">
            <label for="trip-enddate-1" class="control-label sr-only"><?php echo $words->get('TripDateEnd'); ?></label>
                <div class="input-group">
                    <input type="text" class="form-control date-picker" name="trip-enddate-1" placeholder="<?= $words->getBuffered('TripDateEnd'); ?>" id="trip-enddate-1"
                           value="" />
                    <label for="trip-enddate-1" class="control-label input-group-addon btn"><span class="fa fa-calendar"></span></label>
                </div>
        </div>
        <div class="col-md-12" id="trip-map"></div>
        <input type="submit" class="btn btn-default btn-lg pull-left hidden-xs" value="<?= $buttonTitle ?>"/><?php echo $words->flushBuffer(); ?>
            </div>
            </div>
        </div>
        <div class="control-group">
        </div>
            <input type="hidden" name="trip-id" value="<?= $vars['trip-id'] ?>" />
        <input type="submit" class="btn btn-default btn-lg  pull-right hidden-xs" value="<?= $buttonTitle ?>"/><?php echo $words->flushBuffer(); ?>
    </form>
    </div>
</div>