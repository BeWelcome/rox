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
?>
    <h2><?= $panelTitle ?></h2>

        <form method="post" class="tripseditcreate" role="form">
            <?= $callback_tag; ?>
            <input type="hidden" name="trip-id" value="<?= $tripInfo['trip-id'] ?>" >
            <div class="form-group has-feedback">
                <label for="trip-title"
                       class="control-label sr-only"><?php echo $words->get('TripNameLabel'); ?></label>
                <input type="text" class="form-control" name="trip-title"
                       placeholder="<?= $words->getBuffered('TripNamePlaceholder'); ?>"
                       value="<?= htmlentities($tripInfo['trip-title'], ENT_COMPAT, 'utf-8') ?>" />
                <?php if (in_array('TripErrorNameEmpty', $errors)) : ?>
                    <span class="help-block alert alert-danger"><?= $words->get('TripErrorNameEmpty') ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group has-feedback">
                <label for="trip-description" class="control-label sr-only"><?= $words->get('TripDescriptionLabel'); ?></label>
            <textarea class="form-control" name="trip-description" cols="48" rows="7"
                      placeholder="<?= $words->getBuffered('TripDescriptionPlaceholder') ?>"><?php
                if (!empty($tripInfo['trip-description'])) {
                    echo htmlentities($tripInfo['trip-description'], ENT_COMPAT, 'utf-8');
                } ?></textarea>
                <?php if (in_array('TripErrorDescriptionEmpty', $errors)) : ?>
                    <span class="help-block alert alert-danger"><?= $words->get('TripErrorDescriptionEmpty') ?></span>
                <?php endif; ?>
            </div>
            <div class="row has-feedback">
            <div class="form-group col-md-6">
                <label for="trip-count"
                       class="control-label sr-only"><?php echo $words->get('TripCountLabel'); ?></label>
                <?= _getCountDropdown($tripInfo['trip-count'], $words->getBuffered('TripCountPlaceholder')) ?>
                <?php if (in_array('TripErrorNumberOfPartyMissing', $errors)) : ?>
                    <span class="help-block alert alert-danger"><?= $words->get('TripErrorNumberOfPartyMissing') ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group col-md-6">
                <label for="trip-additional-info"
                       class="control-label sr-only"><?php echo $words->get('TripAdditionalInfoLabel'); ?></label>
                <?= _getAdditionalInfoDropdown($tripInfo['trip-additional-info'], $words) ?>
                <?php if (in_array('TripErrorCountAdditionalMismatch', $errors)) : ?>
                    <span class="help-block alert alert-danger"><?= $words->get('TripErrorCountAdditionalMismatch') ?></span>
                <?php endif; ?>
            </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><?= $words->get("TripsLocations") ?></div>
                <div class="panel-body">
                    <?php
                    if (in_array('TripErrorOverlappingDates', $errors)) {
                        echo '<span class="help-block alert alert-danger">' . $words->get('TripErrorOverlappingDates') . '</span>';
                    }
                    ?>
                    <?php
                    $locationRow=0;
                    foreach($tripInfo['locations'] as $locationDetails) :
                        $locationRow++; ?>
                        <div id="div-location-<?= $locationRow ?>">
                            <?php include SCRIPT_BASE . '/build/trips/templates/locationrow.php'; ?>
                        </div>
                    <?php endforeach; ?>
                    <div id="empty-location"><img id="location-loading"
                                                  src="/styles/css/minimal/screen/custom/jquery-ui/smoothness/images/ui-anim_basic_16x16.gif" alt="loading..." style="display:none;">
                    </div>
                    <?php
                    if (in_array('TripErrorNoLocationSpecified', $errors)) {
                        echo '<span class="help-block alert alert-danger">' . $words->get('TripErrorNoLocationSpecified') . '</span>';
                    }
                    ?>
                    <?php $map_conf = PVars::getObj('map'); ?>
                    <input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
                    <input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>
                    <div class="bw_row"><div class="col-md-12"><div id="trips-map" class="map"></div></div></div>
                </div>
            </div>
            <input type="hidden" name="trip-id" value="<?= $tripInfo['trip-id'] ?>"/>
            <input type="submit" class="btn btn-default btn-lg  pull-right hidden-xs"
                   value="<?= $buttonTitle ?>"/><?php echo $words->flushBuffer(); ?>
        </form>
