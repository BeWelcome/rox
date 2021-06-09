<?php

/**
 * Geo Popup Template
 * This is a popup that is beeing used as an alternative to javascript based geo location selection
 * We redefine the methods of RoxPageView to configure this page.
 *
 * @package geo
 * @author Micha (bw: lupochen)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
 ?>
 <div id="geoselector" >
    <form method="post" action="<?=$page_url?>" name="geo-form" id="geo-form" target="_self" class="form">
        <?=$callbacktag?>
        <fieldset id="location">
                <!-- Content of left block -->
                <div class="o-form-group">
                    <div class="input-group">
                        <label for="geo-search" class="control-label sr-only"><?=$words->get('label_setlocation')?>:</label>
                        <input type="text" name="geo-search" class="o-input" id="geo-search" placeholder="<?=$words->get('label_setlocation')?>"
                        <?php
                        echo isset($mem_redirect->location) ? 'value="'.htmlentities($mem_redirect->location, ENT_COMPAT, 'utf-8').'" ' : '';
                        ?>
                         >
                         <span class="input-group-append">
                             <button type="submit" class="button" id="btn-geo-search"><?=$words->get('label_search_location')?></button>
                         </span>
                    </div>
                    <span class="help-block text-justify"><?=$words->get('subline_location')?></span>
                </div>
        </fieldset>
    </form>

    <fieldset id="location_selection_nonjs" class="location_selection">
        <?php echo $locations_print; ?>
    </fieldset>
</div>
