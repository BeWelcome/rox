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
        <form method="post" action="<?=$page_url?>" name="geo-form" id="geo-form" target="_self">
            <?=$callbacktag?>
          <fieldset id="location">

            <?php
            /*if (isset($_SESSION['GeoVars']['geonameid'])) {
                <p>Current location:</p>
                <ol class="geoloc plain clearfix">
                <li style="background-color: #f5f5f5; font-weight: bold; background-image: url(images/icons/tick.png);"><a id="href_4544349">
                <?=$_SESSION['GeoVars']['geonamename']?><br/>
                <img alt="United States" src="images/icons/flags/<?=$_SESSION['GeoVars']['geonamecountrycode']?>.png"/>
                <span class="small"><?=$_SESSION['GeoVars']['countryname']?> / <?=$_SESSION['GeoVars']['admincode']?></span>
                </a></li>
                </ol>
             } */
            ?>

        <div class="subcolumns">
          <div class="c50l">
            <div class="subcl">
              <!-- Content of left block -->

              <div class="float_left">
                <label for="geo-search"><?=$words->get('label_setlocation')?>:</label><br />
                <input type="text" name="geo-search" id="geo-search" <?php
                echo isset($mem_redirect->location) ? 'value="'.htmlentities($mem_redirect->location, ENT_COMPAT, 'utf-8').'" ' : '';
                ?>
                 /> <input type="submit" class="button" id="btn-geo-search" class="button" value="<?=$words->get('label_search_location')?>" />
                <p class="desc"><?=$words->get('subline_location')?></p>
              </div>
            </div>
          </div>

          <div class="c50r">
            <div class="subcr">
              <!-- Content of right block -->
            </div>
          </div>
        </div>

          </fieldset>
        </form>


          <fieldset id="location_selection_nonjs" class="location_selection">
        <?php echo $locations_print; ?>
          </fieldset>

</div>
