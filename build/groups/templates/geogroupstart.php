<?php foreach ($this->getMessages() as $message) : ?>
<p><?= $words->get($message); ?>
<?php endforeach; ?>
<?php
    $google_conf = PVars::getObj('config_google');
    $google_api_key = ($google_conf && $google_conf->maps_api_key)
        ? $google_conf->maps_api_key : "";

    $geo = $this->group->getGeo();
    //var_dump($geo);
    if ($geo->isCity()) $zoom = 10;
    elseif ($geo->fcode == 'ADM4') $zoom = 9;
    elseif ($geo->fcode == 'ADM3') $zoom = 8;
    elseif ($geo->fcode == 'ADM2') $zoom = 7;
    else $zoom = 6;
    
    if ($geo->parentCountryId == 0) $zoom = 5;
    elseif ($geo->name == 'US' || $geo->name == 'Russia' || $geo->name == 'China') $zoom = 3;

?>

<div id="groups">
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">

                <?php if (isset($children) && $children) { ?>

                <div class="row floatbox">
                    
                    <?php
                    if (isset($cities) && $cities) {
                        echo '<h3>'.$words->get('PlacesFeaturedCities').'</h3>';
                        // split array into arrays of 5 pieces
                        $cities_packs = array_chunk($cities,5);
                        foreach ($cities_packs as $cities) {
                            echo '<ul class="featuredcities">';
                            foreach ($cities as $city)
                            {
                                $city_name = $city->getTranslatedName();
                                $temp = implode('/',array(('location' .($loc_count + 1)) => $city->url_string.$city->name));
                                echo " <li><a href='{$this->router->url('geogroup_country', array('location' => $temp))}'>{$city_name} <span class='grey'>({$city->member_count})</span></a></li>";
                            }
                            echo '</ul>';
                        }
                    }
                    ?>

                </div> <!--row floatbox -->

                <?php } ?>

                <h3><?= $words->get('GeoGroupMembers'); ?></h3>
                <div class="floatbox">
                    <?php
                    if ($this->group->getMemberCount()) {
                        $memberlist_widget->render();
                    } else echo $words->get("This group doesn't have any members yet.")
                    ?>
                </div> <!-- floatbox -->
                <?php if ($this->group->getMemberCount() > 12) { ?>
                <strong><a href="<?= $this->getPlacesURL('geogroup_members') ?>"><?= $words->get('ShowMore'); ?></a></strong>
                <?php } ?>

                <? if ($this->group->getForumThreads()) { ?>
                <h3><?= $words->getFormatted('ForumRecentPostsLong');?></h3>
                <div class="row floatbox">
                    <?= $group_forum_widget->render(); ?>
                </div> <!-- row floatbox -->
                <? } ?>
      
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        
        <div class="c38r">
            <div class="subcr">
                <div id="places-location-map" style="width: 300px;border: 1px solid #999; background-color: #fff; padding: 2px; margin-bottom: 2em;">
                <?php 
                /*
                YAHOO MAPS
                    $request = 'http://local.yahooapis.com/MapsService/V1/mapImage?appid=BUl3JJvV34Gw1tIC3vyWMlq1edd78o3oq89uO9UdFhfswwK_.6iMMwceBuHrvsUILP7j&location=Germany&image_height=256&image_width=256&zoom=12&output=php';

                    $response = file_get_contents($request);

                    if ($response === false) {
                        die('Request failed');
                    }

                    $phpobj = unserialize($response);

                    echo '<img src="'.$phpobj["Result"].'">'; */

                /* GOOGLE MAPS */
                    echo '<img src="http://maps.google.com/staticmap?center='.$geo->latitude.','.$geo->longitude.'&amp;zoom='.$zoom.'&amp;size=300x256&amp;key='.$google_api_key.'&amp;markers='.$geo->latitude.','.$geo->longitude.',orange&amp;maptype=terrain&amp;sensor=false" alt="map" />'
                ?>
                </div>
                
                
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolumns -->
</div> <!-- groups -->
