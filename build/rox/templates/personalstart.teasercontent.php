<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/
?>

<div id="teaser" class="clearfix teaser_main">
    <?echo $thumbPathMember;?>
    <h1><?=$words->getSilent('HelloUsername',$_SESSION['Username'])?></h1>

    <div class="subcolumns" style="margin-bottom: 2em">
        <div class="c33l">
            <div class="subcl">
                <ul id="personalmenu">
                    <li><a id="tablink1" <?php if(!$ShowDonateBar) { echo 'class="active-tab"'; } ?> href="#tab1"><?=$words->getSilent('NotifyWhatsHappening')?></a></li>
                    <li><a id="tablink2" href="#tab2"><?=$words->getSilent('FindAHost')?></a></li>
                    <li><a id="tablink3" <?php if($ShowDonateBar) { echo 'class="active-tab"'; } ?> href="#tab3"><?=$words->getSilent('HelpTheProject')?></a></li>
                    <?php /* NOTREADY <li><a id="tablink3a" href="#tab3a"><?=$words->getSilent('OfferHosting')?></a></li> */ ?>
                    <?php /* NOTREADY<li><a id="tablink4" href="#tab4"><?=$words->getSilent('SetLocation')?></a></li> */ ?>
                    <li><a id="tablink5" href="#tab5"><?=$words->getSilent('CheckYourMessages')?>
                    <?php if (isset($_mainPageNewMessagesMessage)) { ?>
                        <span style="color:#333333; font-weight:bold;">(<img src="images/icons/icons1616/icon_contactmember.png" alt="Messages"/> <?=$_newMessagesNumber?>)</span>
                    <?php } ?>
                    </a></li>
                    <li><a id="tablink6" href="#tab6"><?=$words->getSilent('InviteAFriend')?></a></li>
                </ul>
            </div> <!-- subcl -->
        </div> <!-- c33l -->

        <div class="c66r">
            <div class="subcr">

                <div class="panel<?php echo (isset($_SESSION['Param']) && ($_SESSION['Param']->ToggleDonateBar)) ? '' : ' active-tab-body' ?>" id="tab1">
                    <?php
                    $notify_widget->render();
                    ?>
                </div> <!-- tab1 -->
                
                <div class="panel" id="tab2">
                    <?php /* NOTREADY: complete the stuff below
                                    <h3><?=$words->get('HowToFindAHost')?></h3>

                                    <p>At best, write down your expectations and travel plans first. People can then contact you directly and offer you accomodation. You can also search actively for a person and just combine your personal message to him with the travel description you created.</p>
                                    <div id="hostrequest">
                                        <a href="requesthosting" class="button">Blog your request</a>
                                    </div>
                                    <p class="row"></p>
                                    <p class="row">after that</p>
                    */ ?>
                    <div class="subcolumns">
                        <div class="c50l">
                            <div class="subcl">

                                <h3><?php echo $words->getFormatted('StartpageSearchMember') ?></h3>
                                <div id="search-bar">
                                    <form id="form1" name="form1" method="get" action="searchmembers">
                                        <input name="vars" type="text" class="search-style" id="searchq" size="20" onblur="if(this.value == '') this.value='<?php echo $words->get('StartpageSearchMemberText'); ?>'" value="<?php echo $words->get('StartpageSearchMemberText'); ?>" onfocus="this.value='';" />
                                        <input type="hidden" name="searchopt" id="searchopt" />
                                        <input type="submit" value="<?php echo $words->getFormatted('FindPeopleSubmitSearch'); ?>" class="button"/>
                                    </form>
                                </div>

                            </div> <!-- subcl -->
                        </div> <!-- c50l -->

                        <div class="c50r">
                            <div class="subcr">

                                <div id="browsecities_dropdown" style="display:none;">
                                    <h3><?php echo $words->getFormatted('StartpageBrowseCountries') ?></h3>
                                    <select onchange="window.location.href=this.value; return false" style="width: 100%">
                                    <?php foreach ($Countries as $continent => $countries_group) { ?>
                                        <optgroup label="<?php
                                        if ($continent=='AF') { echo $words->getformatted('Africa');} 
                                        if ($continent=='AN') { echo $words->getformatted('Antarctica');} 
                                        if ($continent=='EU') { echo $words->getformatted('Europe');} 
                                        if ($continent=='NA') { echo $words->getformatted('NorthAmerica');}
                                        if ($continent=='SA') { echo $words->getformatted('SouthAmerica');}
                                        if ($continent=='OC') { echo $words->getformatted('Oceania');}
                                        if ($continent=='AS') { echo $words->getformatted('Asia');} ?>">
                                        <?php foreach ($countries_group as $code => $country) { ?>
                                             <option label="<?=$country['name']?>" value="places/<?=$code?>"><?=$country['name']?> <?=($country['number'] != 0) ? ('('.$country['number'].')') : ''?></option>
                                        <?php } ?>
                                    </optgroup>
                                    <?php } ?>
                                    </select>
                                </div> <!-- browsecities_dropdown -->
                                <script type="text/javascript">
                                $('browsecities_dropdown').show();
                                </script>

                            </div> <!-- subcr -->
                        </div> <!--c50r -->
                    </div> <!-- subclumns -->
                </div> <!-- tab2 -->

                <div class="panel<?php echo (isset($_SESSION['Param']) && ($_SESSION['Param']->ToggleDonateBar)) ? ' active-tab-body' : '' ?>" id="tab3">

                    <div class="subcolumns">
                        <div class="c50l">
                            <div class="subcl">

                                <? include 'userbar_donate.php' ; // the Donation bar ?>

                            </div> <!-- subcl -->
                        </div> <!-- c50l -->

                        <div class="c50r">
                            <div class="subcr">

                                <h3><?=$words->get('StartpageVolunteer')?></h3>
                                <p><?=$words->get('GetActiveIntro')?></p>
                                <a href="getactive" class="button" title="getactive"><?=$words->get('GetActive')?></a>

                            </div> <!-- subcr -->
                        </div> <!-- c50r -->
                    </div> <!-- subcolumns -->
                </div> <!-- tab3 -->
                
                <div class="panel" id="tab3a">
                    <div class="subcolumns">
                        <div class="c50l">
                            <div class="subcl">

                                <h3><?=$words->get('HostingStatus')?></h3>
                                <form id="hostingstatus" name="hostingstatus" method="post" action="searchmembers/quicksearch">
                                    <select name="top5">
                                        <option>I'm hosting</option>
                                        <option>Can't host</option>
                                        <option>Maybe</option>
                                        <option>I'm travelling</option>
                                    </select>
                                    <input type="hidden" name="quicksearch_callbackId" value="1"/>
                                    <input type="hidden" name="searchoptstatus" id="searchoptstatus" />
                                </form>

                            </div> <!-- subcl -->
                        </div> <!-- c50l -->

                        <div class="c50r">
                            <div class="subcr">

                                <h3>Edit your profile</h3>
                                <p>Edit your profile now and describe in detail what you can offer, where you live, etc.</p>
                                <div id="hostrequest">
                                    <a href="requesthosting" class="button">EditMyProfile</a>
                                </div>

                            </div> <!-- subcr -->
                        </div> <!-- c50r -->
                    </div> <!-- subclumns -->
                </div> <!-- tab3a -->

                <div class="panel" id="tab4">

                    <h2>Where are you now?</h2>

                    <?php
                    /* NOTREADY
                    $Member = new MembersModel;
                    // Overwrite SetLocation-Geo-Info with GeoVars-Session (used for non-js users), afterwards unset it again.
                    if (isset($_SESSION['GeoVars']) && isset($_SESSION['GeoVars']['id']) && isset($_SESSION['GeoVars']['geonameid'])) {
                        foreach ($_SESSION['GeoVars'] as $key => $value) {
                            $vars[$key] = $value;
                        }
                        // set the location
                        $result = $Member->setLocation($vars['id'],$vars['geonameid']);
                        $errors['Geonameid'] = 'Geoname not set';
                        if (count($result['errors']) > 0) {
                            $vars['errors'] = $result['errors'];
                        }
                        // unset($_SESSION['GeoVars']);
                    }
                    $callback_tag = $this->layoutkit->formkit->setPostCallback('MembersController', 'setLocationCallback');
                    $member = $Member->getMemberWithId($_SESSION['IdMember']);
                    $m->geonameid = $member->IdCity;
                    $m->id = $member->id;
                    $mem_redirect = $this->layoutkit->formkit->getMemFromRedirect();

                    // values from previous form submit
                    if (!$mem_redirect || !isset($mem_redirect->post['id'])) {
                        // this is a fresh form
                        foreach ($m as $key => $value) {
                            $vars[$key] = $value;
                        }
                        $Geo = new GeoModel;
                        if (isset($vars['geonameid']) && !isset($vars['geonamename']))
                            $vars['geonamename'] = $Geo->getDataById($vars['geonameid'])->name;
                            $vars['geonamecountry'] = '';
                    } else {
                        $vars = $mem_redirect->post;
                        $vars['errors'] = $mem_redirect->errors;
                    }
                    if (!isset($vars['errors']) || empty($vars['errors']))
                        $vars['errors'] = array();
                    // Overwrite Signup-Geo-Info with GeoVars-Session (used for non-js users), afterwards unset it again.
                    if (isset($_SESSION['GeoVars'])) {
                        foreach ($_SESSION['GeoVars'] as $key => $value) {
                        $vars[$key] = $value;
                        }
                    }

                    ?>

                    <form method="post" name="geo-form-js" id="geo-form-js">
                        <input type="hidden" name="javascriptactive" value="false" />
                     <?php
                            if (in_array('inserror', $vars['errors'])) {
                                echo '<p class="error">'.$words->get('inserror').'</p>';
                            }
                            ?>
                    <?php
                            if (in_array('SignupErrorProvideLocation', $vars['errors'])) {
                                echo '<p class="error">'.$words->get('SignupErrorProvideLocation').'</p>';
                            }
                            ?>

                            <div class="floatbox" id="geoselectorjs" style="display: none;" >

                                <div class="subcolumns">
                                  <div class="c50l">
                                    <div class="subcl">
                                      <!-- Content of left block -->

                                        <label for="create-location"><?=$words->get('label_setlocation')?>:</label><br />
                                        <input type="text" name="create-location" id="create-location" <?php
                                        echo isset($vars['create-location']) ? 'value="'.htmlentities($vars['create-location'], ENT_COMPAT, 'utf-8').'" ' : '';
                                        ?>
                                         />
                                         <input id="btn-create-location" class="button" onclick="javascript:return false;" type="submit" value="<?=$words->get('label_search_location')?>" />
                                        <p class="desc"><?=$words->get('subline_location')?></p>

                                        <div id="location-status"></div>
                                <div id="location-suggestion">
                                <?php if (isset($vars['geonamename']) && isset($vars['geonameid']) && $vars['geonameid'] != '') { ?>
                                    <p><b><?=$words->get('Geo_choosenLocation')?>:</b></p>
                                    <ol id="locations" class="plain">
                                        <li style="background-color: #f5f5f5; font-weight: bold; background-image: url(images/icons/tick.png);"><a id="href_4544349">
                                        <?=$vars['geonamename']?><br/>
                                        <?php if (isset($vars['geonamecountrycode']) && isset($vars['countryname']) && isset($vars['admincode'])) { ?>
                                            <img alt="<?=$vars['countryname']?>" src="images/icons/flags/<?=strtolower($vars['geonamecountrycode'])?>.png"/>
                                            <span class="small"><?=$vars['countryname']?> / <?=$vars['admincode']?></span>
                                        <?php } ?>
                                        </a></li>
                                    </ol>
                                <?php } ?>
                            </div>
                                    </div>
                                  </div>

                                  <div class="c50r">
                                    <div class="subcr">
                                      <!-- Content of right block -->
                                        <div id="spaf_map" style="width:240px; height:180px; border: 2px solid #333; display:none;">
                                        </div>
                                    </div>
                                  </div>
                                </div>

                                </div> <!-- geoselectorjs -->

                    </form>

                        <?php
                            $Geo = new GeoController;
                            $Geo->layoutkit = $this->layoutkit;
                            $Geo->SelectorInclude(array('id' => $vars['id']));
                        ?>


                    <form method="post" action="setlocation" name="signup" id="user-register-form">
                      <?=$callback_tag ?>
                      <input type="hidden" name="javascriptactive" value="false" />

                      <?php
                            if (in_array('inserror', $vars['errors'])) {
                                echo '<p class="error">'.$errors['inserror'].'</p>';
                            }
                            ?>

                        <input type="hidden" name="id" id="id" value="<?php
                                echo isset($vars['id']) ? htmlentities($vars['id'], ENT_COMPAT, 'utf-8') : '';
                            ?>" />
                        <input type="hidden" name="geonameid" id="geonameid" value="<?php
                                echo isset($vars['geonameid']) ? htmlentities($vars['geonameid'], ENT_COMPAT, 'utf-8') : '';
                            ?>" />
                        <input type="hidden" name="latitude" id="latitude" value="<?php
                                echo isset($vars['latitude']) ? htmlentities($vars['latitude'], ENT_COMPAT, 'utf-8') : '';
                            ?>" />
                        <input type="hidden" name="longitude" id="longitude" value="<?php
                                echo isset($vars['longitude']) ? htmlentities($vars['longitude'], ENT_COMPAT, 'utf-8') : '';
                            ?>" />
                        <input type="hidden" name="geonamename" id="geonamename" value="<?php
                                echo isset($vars['geonamename']) ? htmlentities($vars['geonamename'], ENT_COMPAT, 'utf-8') : '';
                            ?>" />
                        <input type="hidden" name="countryname" id="countryname" value="<?php
                                echo isset($vars['countryname']) ? htmlentities($vars['countryname'], ENT_COMPAT, 'utf-8') : '';
                            ?>" />
                        <input type="hidden" name="geonamecountrycode" id="geonamecountrycode" value="<?php
                                echo isset($vars['geonamecountrycode']) ? htmlentities($vars['geonamecountrycode'], ENT_COMPAT, 'utf-8') : '';
                            ?>" />
                        <input type="hidden" name="admincode" id="admincode" value="<?php
                                echo isset($vars['admincode']) ? htmlentities($vars['admincode'], ENT_COMPAT, 'utf-8') : '';
                            ?>" />
                        <input type="hidden" name="newgeo" id="newgeo" value="0" />


                      <div id="submit_button" style="display: none;">
                        <input type="submit" value="<?php echo $words->get('SubmitForm'); ?>" class="button"
                        onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
                        />
                      </div>

                    </form>

                    <script src="script/geo_suggest.js" type="text/javascript"></script>
                    <script type="text/javascript">
                             var map = null;

                        function createMarker(point, descr) {
                             var marker = new GMarker(point);
                             GEvent.addListener(marker, "click", function() {
                                marker.openInfoWindowHtml(descr);
                             });
                             return marker;
                        }

                        var loaded = false;
                        function SPAF_Maps_load() {
                             if (!loaded && GBrowserIsCompatible()) {

                                map = new GMap2(document.getElementById("spaf_map"));
                    <?php
                        if (isset($vars['latitude']) && isset($vars['longitude']) && $vars['latitude'] && $vars['longitude']) {
                            echo 'map.setCenter(new GLatLng('.htmlentities($vars['latitude'], ENT_COMPAT, 'utf-8').', '.htmlentities($vars['longitude'], ENT_COMPAT, 'utf-8').'), 8);';
                            if (isset($vars['geonamename']) && isset($vars['geonamecountry'])) {
                                $desc = "'".$vars['geonamename'].", ".$vars['geonamecountry']."'";
                                echo 'var marker = new GMarker(new GLatLng('.$vars['latitude'].', '.$vars['longitude'].'), '.$desc.');
                                    map.addOverlay(marker);
                                    GEvent.addListener(marker, "click", function() {
                                        marker.openInfoWindowHtml('.$desc.');
                                    });
                                    marker.openInfoWindowHtml('.$desc.');';
                            }
                        } else {
                            echo 'map.setCenter(new GLatLng(47.3666667, 8.55), 8);';
                        } ?>
                                map.addControl(new GSmallMapControl());
                                //map.addControl(new GMapTypeControl());
                            }
                            loaded = true;
                        }

                        function changeMarker(lat, lng, zoom, descr) {
                            if (!loaded) {
                                SPAF_Maps_load();
                                loaded = true;
                            }
                            map.panTo(new GLatLng(lat, lng));
                            map.setZoom(zoom);
                            map.clearOverlays();
                            map.addOverlay(createMarker(new GLatLng(lat, lng), descr));
                        }

                        function setGeonameIdInForm(geonameid, latitude, longitude, geonamename, countryname, countrycode, admincode) {
                            $('geonameid').value = geonameid;
                            $('latitude').value = latitude;
                            $('longitude').value = longitude;
                            $('geonamename').value = geonamename;
                            $('countryname').value = countryname;
                            $('geonamecountrycode').value = countrycode;
                            $('admincode').value = admincode;
                            $('countryname').value = countryname;
                            $('newgeo').value = 1;
                        }

                        function removeHighlight() {
                            var lis = $A($('locations').childNodes);
                            lis.each(function(li) {
                                Element.setStyle(li, {fontWeight:'',backgroundColor:'#fff',backgroundImage:''});
                            });
                        }

                        function setMap(geonameid, latitude, longitude, zoom, geonamename, countryname, countrycode, admincode) {
                            setGeonameIdInForm(geonameid, latitude, longitude, geonamename, countryname, countrycode, admincode);
                            changeMarker(latitude, longitude, zoom, geonamename+', '+countryname);
                            removeHighlight();
                            Element.setStyle($('li_'+geonameid), {fontWeight:'bold',backgroundColor:'#f5f5f5',backgroundImage:'url(images/icons/tick.png)'});
                        }

                        function init(){
                            $('submit_button').style.display = 'block';
                            $('geoselector').style.display = 'none';
                            $('geoselectorjs').style.display = 'block';
                            $('spaf_map').style.display = 'block';
                            GeoSuggest.initialize('geo-form');
                            SPAF_Maps_load();
                        }

                        window.onunload = GUnload;

                        Event.observe(window, 'load', init, false);

                    </script>
                    */ ?>
            </div> <!-- tab4 -->
                <div class="panel" id="tab5">

                <?php $inbox_widget->render() ?>
                <p><a href="messages">more...</a></p>

                </div> <!-- tab5 -->

                <div class="panel" id="tab6">
                    <p class="big"><?php echo $words->get('InviteAFriendInfo');?></p>
                </div> <!-- tab5 -->

            </div> <!-- subcr -->
        </div> <!-- c66r -->
    </div> <!-- subcolumns -->

    <div><?=$words->flushBuffer()?></div>
</div> <!-- teaser -->

<!--
<style type="text/css">
    p{padding:6px 0 20px 0;}
    #search-bar{padding:0; clear:both;}
    #search-bar div{  background:#FFC04A; padding:5px; }
    #search-bar .search-style{font-size:16px; color:#333; border:solid 1px #CCCCCC; padding:4px;}
    ul.search-options, ul.search-options li{padding:0; border:0; margin:0; list-style:none;}
    ul.search-options{clear:both;}
    #teaser ul.search-options li a,#teaser ul.search-options li a.hover,#teaser ul.search-options li a.focus{float:left; margin-right:1px; width:auto; background:#FFD689; padding:8px; color:#fff; text-decoration:none; font-weight:bold;}
    #teaser .selected a{background:#FFC04A; color:#fff;}
    #teaser ul.search-options li.selected a{background:#FFC04A; color:#fff;}
</style>
-->
<script type="text/javascript">

function tabPersonal(idElement){
    /* Total Tabs above the input field (in this case there are 3 tabs: web, images, videos) */
    $$('#personalmenu li').invoke('hide');
    this.show();
}

function observeTabs(tab){
    /* Total Tabs above the input field (in this case there are 3 tabs: web, images, videos) */
    Event.observe(tab,'click',tabPersonal(tab));
}

//$$('#personalmenu li').each(Element,observeTabs);
Event.observe(window,'load',function(){ new Fabtabs('personalmenu'); },false);
</script>
