<script type="text/Javascript">
    var noMatchesFound = "<?php echo $words->getSilent('SearchNoMatchesFound');?>";
</script><?php
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
$tripInfo = $this->getRedirectedMem('tripInfo');
if (empty($tripInfo)) {
    $tripInfo = $this->vars;
}

$member = $this->member;
$words = $this->getWords();
if ($this->_editing == true) {
    $panelTitle = $words->get('Triptitle_edit');
    $buttonTitle = $words->getSilent('TripSubmit_edit');
} else {
    $panelTitle = $words->get('TripTitle_create');
    $buttonTitle = $words->getSilent('TripSubmit_create');
}
var_dump($errors);
var_dump($tripInfo);
?>
    <h2><?= $panelTitle ?></h2>

        <form method="post" class="tripseditcreate" role="form">
            <?= $callback_tag; ?>
            <div class="form-group has-feedback">
                <label for="trip-name"
                       class="control-label sr-only"><?php echo $words->get('TripNameLabel'); ?></label>
                <input type="text" class="form-control" name="trip-name"
                       placeholder="<?= $words->getBuffered('TripNamePlaceHolder'); ?>" id="trip-name"
                       value="<?= htmlentities($tripInfo['trip-name'], ENT_COMPAT, 'utf-8') ?>"/>
                <?php if (in_array('TripErrorNameEmpty', $errors)) : ?>
                    <span class="help-block alert alert-danger"><?= $words->get('TripErrorNameEmpty') ?></span>
                <?php endif; ?>
            </div>

            <!-- password -->
            <div class="form-group has-feedback">
                <label for="trip-description" class="control-label sr-only"><?= $words->get('TripDescriptionLabel'); ?></label>
            <textarea class="form-control" id="trip-description" name="trip-description" cols="48" rows="7"
                      placeholder="<?= $words->getBuffered('TripDescriptionPlaceholder') ?>"><?php
                if (!empty($tripInfo['trip-description'])) {
                    echo htmlentities($tripInfo['trip-description'], ENT_COMPAT, 'utf-8');
                } ?></textarea>
                <?php if (in_array('TripErrorDescriptionEmpty', $errors)) : ?>
                    <span class="help-block alert alert-danger"><?= $words->get('TripErrorDescriptionEmpty') ?></span>
                <?php endif; ?>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><?= $words->get("TripsLocations") ?></div>
                <div class="panel-body">
                    <?php
                    $i=1;
                    foreach($tripInfo['locations'] as $locationDetails) :
                    ?>
                    <div id="div-location-<?= $i ?>" name="div-location-<?= $i ?>" class="row">
                        <?php $locationRow = $i; include SCRIPT_BASE . '/build/trips/templates/locationrow.php'; ?>
                    </div>
                    <?php
                        $i++;
                    endforeach; ?>
                    <div id="empty-location"><img id="location-loading"
                                                  src="/styles/css/minimal/screen/custom/jquery-ui/smoothness/images/ui-anim_basic_16x16.gif" style="display:none;">
                    </div>
                    <?php
                    if (in_array('TripErrorNoLocationSpecified', $errors)) {
                        echo '<span class="help-block alert alert-danger">' . $words->get('TripErrorNoLocationSpecified') . '</span>';
                    }
                    ?>

                    <?php $map_conf = PVars::getObj('map'); ?>
                    <input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
                    <input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>
                    <div class="row"><div class="col-md-12"><div id="trips-map"></div></div></div>
                </div>
            </div>
            <input type="hidden" name="trip-id" value="<?= $tripInfo['trip-id'] ?>"/>
            <input type="submit" class="btn btn-default btn-lg  pull-right hidden-xs"
                   value="<?= $buttonTitle ?>"/><?php echo $words->flushBuffer(); ?>
        </form>
