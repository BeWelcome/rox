<script type="text/Javascript">
    var noMatchesFound = "<?php echo $words->getSilent('SearchNoMatchesFound');?>";
    var searchSimple = "<?php echo $words->getSilent('SearchMembersSimple');?>";
    var searchAdvanced = "<?php echo $words->getSilent('SearchMembersAdvanced');?>";
    var checkAllTextTranslation = "<?php echo $words->getSilent('SearchMembersCheckAll');?>";
    var uncheckAllTextTranslation = "<?php echo $words->getSilent('SearchMembersUncheckAll');?>";
    var noneSelectedTextTranslation = "<?php echo $words->getSilent('SearchMembersNoneSelected');?>";
    var selectedTextTranslation = "<?php echo $words->getSilent('SearchMembersSelected');?>";
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
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars = $this->vars;
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
        <form method="post" class="form tripseditcreate" role="form">
            <?= $callback_tag; ?>
            <div class="form-group has-feedback">
                <label for="tripname"
                       class="control-label sr-only"><?php echo $words->get('TripLabel_name'); ?></label>
                <input type="text" class="form-control" name="tripname"
                       placeholder="<?= $words->getBuffered('TripLabel_name'); ?>" id="tripname"
                       value="<?= htmlentities($vars['tripname'], ENT_COMPAT, 'utf-8') ?>"/>
            </div>

            <!-- password -->
            <div class="form-group has-feedback">
                <label for="tripdescription" class="control-label sr-only"><?= $words->get('TripLabel_desc'); ?></label>
            <textarea class="form-control" id="tripdescription" name="tripdescription" cols="48" rows="7"
                      placeholder="<?= $words->getBuffered('TripDesc_desc2') ?>"><?php
                if (!empty($vars['tripdescription'])) {
                    echo htmlentities($vars['tripdescription'], ENT_COMPAT, 'utf-8');
                } ?></textarea>
                <?php
                if (in_array('TripErrorDescEmpty', $errors)) {
                    echo '<span class="help-block alert alert-danger">' . $words->get('TripErrorDescEmpty') . '</span>';
                }
                ?>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><?= $words->get("TripsLocations") ?></div>
                <div class="panel-body">
                    <div id="div-location-1" name="div-location-1" class="row">
                    <?php $locationRow = 1; include SCRIPT_BASE . '/build/trips/templates/locationrow.php'; ?>
                    </div>
                    <div id="empty-location"><img id="location-loading"
                                                  src="/styles/css/minimal/screen/custom/jquery-ui/smoothness/images/ui-anim_basic_16x16.gif" style="display:none;">
                    </div>
                    <div id="add-button" class="row"><div class="col-md-12">
                            <input type="submit" id="trip-add-location" class="btn btn-default pull-right"
                                   value="<?= $words->getBuffered('TripsAddLocation')  ?>" ><?php echo $words->flushBuffer(); ?></div>
                    </div>
                    <?php $map_conf = PVars::getObj('map'); ?>
                    <input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
                    <input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>
                    <input type="hidden" id="trip-data-min-latitude" value="-60" >
                    <input type="hidden" id="trip-data-max-latitude" value="70" >
                    <input type="hidden" id="trip-data-min-longitude" value="-179" >
                    <input type="hidden" id="trip-data-max-longitude" value="179" >
                    <div class="row"><div class="col-md-12"><div id="trips-map" style="height:300px;"></div></div></div>
                </div>
            </div>
            <input type="hidden" name="tripid" value="<?= $vars['tripid'] ?>"/>
            <input type="submit" class="btn btn-default btn-lg  pull-right hidden-xs"
                   value="<?= $buttonTitle ?>"/><?php echo $words->flushBuffer(); ?>
        </form>
