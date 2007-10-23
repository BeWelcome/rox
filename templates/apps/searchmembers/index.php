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
    $words = new MOD_words();
?>
<script src="script/prototype.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $google_conf->maps_api_key; ?>" type="text/javascript"></script>

<form id="searchmembers" name="searchmembers" action="javascript: {}" />
<input type="hidden" name="mapsearch" id="mapsearch" value="0" />
<input type="hidden" name="bounds_zoom" id="bounds_zoom" />
<input type="hidden" name="bounds_center_lat" id="bounds_center_lat" />
<input type="hidden" name="bounds_center_lng" id="bounds_center_lng" />
<input type="hidden" name="bounds_sw_lat" id="bounds_sw_lat" />
<input type="hidden" name="bounds_ne_lat" id="bounds_ne_lat" />
<input type="hidden" name="bounds_sw_lng" id="bounds_sw_lng" />
<input type="hidden" name="bounds_ne_lng" id="bounds_ne_lng" />
<input type="hidden" name="CityName" id="CityName" />
<input type="hidden" name="IdCountry" id="IdCountry" />
<input type="hidden" name="start_rec" id="start_rec" />
<?php PPostHandler::setCallback("searchmembers_callbackId", "SearchmembersController", "index"); ?>
<input type="hidden" name="searchmembers_callbackId" value="1" />

<h3><?php echo $words->getFormatted('FindPeopleSearchTerms'); ?></h3>
<p><?php echo $words->getFormatted('FindPeopleSearchTermsExp'); ?></p>
<table><tr><td>
<strong class="small"><?php echo $words->getFormatted('Username'); ?></strong><br />
<input type="text" name="Username" size="30" maxlength="30" value="" onKeyPress="if(chkEnt(this, event)) searchGlobal(0);" />
</td><td>
<strong class="small"><?php echo $words->getFormatted('TextToFind'); ?></strong><br />
<input type="text" name="TextToFind" size="30" maxlength="30" value="" onKeyPress="if(chkEnt(this, event)) searchGlobal(0);" />
</td></tr></table>
<br />
<h3><a style="cursor:pointer;" onClick="$('FindPeopleFilter').toggle();"><?php echo $words->getFormatted('FindPeopleFilter'); ?></a> | 
<a style="cursor:pointer;" onClick="$('FindPeopleResults').toggle();"><?php echo $words->getFormatted('FindPeopleResults'); ?></a></h3>
<div id="FindPeopleFilter" class="NotDisplayed">
<table><tr><td>
<strong class="small"><?php echo $words->getFormatted('Gender'); ?></strong><br />
<select Name="Gender">
    <option value="0"></option>
    <option value="male"><?php echo $words->getFormatted('Male'); ?></option>
    <option value="female"><?php echo $words->getFormatted('Female'); ?></option>
</select>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleMinimumAge'); ?></strong><br />
<select Name="MinimumAge">
    <option value="0"></option>
    <?php foreach(range(18, 90, 2) as $age) { ?>
    <option value="<?php echo $age; ?>"><?php echo $age; ?></option>
    <?php } ?>
</select>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleMaximumAge'); ?></strong><br />
<select Name="MaximumAge">
    <option value="0"></option>
    <?php foreach(range(18, 90, 2) as $age) { ?>
    <option value="<?php echo $age; ?>"><?php echo $age; ?></option>
    <?php } ?>
</select>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleMemberStatus'); ?></strong><br />
<select name="IncludeInactive">
    <option value="0"><?php echo $words->getFormatted('Active'); ?></option>
    <option value="1"><?php echo $words->getFormatted('All'); ?></option>
</select>
</td><td>
<strong class="small"><?php echo $words->getFormatted('Groups'); ?></strong><br />
<select name="IdGroup">
    <option value="0"></option>
    <?php for ($iiMax = count($TGroup), $ii = 0; $ii < $iiMax; $ii++) { ?>
    <option value="<?php echo $TGroup[$ii]->id; ?>"><?php echo $TGroup[$ii]->Name; ?></option>
    <?php } ?>
</select>
</td></tr></table>

<table><tr><td valign="top">
<strong class="small"><?php echo $words->getFormatted('FindPeopleAccomodationTitle'); ?></strong><br />
<?php foreach($TabAccomodation as $TabAcc) { ?>
<input type="checkbox" name="Accomodation[]" id="<?php echo "Accomodation_$TabAcc"; ?>" value="<?php echo $TabAcc; ?>">&nbsp;<span onclick="document.getElementById('<?php echo "Accomodation_$TabAcc"; ?>').click();"><?php echo $words->getFormatted('Accomodation_'.$TabAcc); ?></span><br />
<?php } ?>
<strong class="small">
<?php echo $words->getFormatted('FindPeopleAccomodationTip'); ?>
</strong>
</td><td valign="top">
<strong class="small"><?php echo $words->getFormatted('FindPeopleOfferTypeTitle'); ?></strong><br />
<?php foreach($TabTypicOffer as $TabTyp) { ?>
<input type="checkbox" name="TypicOffer[]" id="<?php echo "TypicOffer_$TabTyp"; ?>" value="<?php echo $TabTyp; ?>">&nbsp;<span onclick="document.getElementById('<?php echo "TypicOffer_$TabTyp"; ?>').click();"><?php echo $words->getFormatted('TypicOffer_'.$TabTyp); ?></span><br />
<?php } ?>
<strong class="small">
<?php echo $words->getFormatted('FindPeopleTypicOfferTip'); ?>
</strong>
</td></tr></table>
<br />
</div>


<div id="FindPeopleResults" class="NotDisplayed">
<table><tr><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleSortOrder'); ?></strong><br />
<select Name="OrderBy">
    <?php foreach($TabSortOrder as $key=>$val) { ?>
    <option value="<?php echo $key; ?>"><?php echo $words->getFormatted($val); ?></option>
    <?php } ?>
</select>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleSortOrderDirection'); ?></strong><br />
<select Name="OrderByDirection">
    <option value="desc"><?php echo $words->getFormatted('Forward'); ?></option>
    <option value="asc"><?php echo $words->getFormatted('Reverse'); ?></option>
</select>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleLimitCount'); ?></strong><br />
<select Name="limitcount">
    <option value="10">10</option>
    <option value="25">25</option>
    <option value="50">50</option>
</select>
</td></tr></table>
</form>
</div>
<br />

<h3><?php echo $words->getFormatted('FindPeopleBeginSearch'); ?></h3>
<p><?php echo $words->getFormatted('FindPeopleBeginSearchExp'); ?></p>
<br />
<input id="global_search" class="button" type="button" value="<?php echo $words->getFormatted('FindPeopleSubmitGlobalSearch'); ?>"
    onclick="searchGlobal(0);" /> &nbsp; <span id="loading"></span>
<br /><br />
<input id="text_search" class="button" type="button" value="<?php echo $words->getFormatted('FindPeopleSubmitTextSearch'); ?>"
    onclick="searchByText(get_val('address'), 0);" />&nbsp;
<input type="text" size="60" name="address" id="address" value="<?php echo "Praha"; ?>"
    onfocus="this.value='';" onKeyPress="if(chkEnt(this, event)) searchByText(this.value, 0);"/>
<br/><br/>

<?php if ($MapOff != "mapoff") { ?>
<div id="MapDisplay">
<input id="map_search" class="button" type="button" value="<?php echo $words->getFormatted('FindPeopleSubmitMapSearch'); ?>"
    onclick="searchByMap(0);" />&nbsp;
<input class="button" type="button" value="<?php echo $words->getFormatted('FindPeopleClearMap'); ?>"
	onclick="map.clearOverlays(); put_html('member_list', '');"/>&nbsp;
<input class="button" type="button" value="<?php echo $words->getFormatted('FindPeopleDisableMap'); ?>"
	onclick="window.location='searchmembers/index/mapoff';"/>
<br /><br />
<div id="map" style="width: 100%; height: 480px; border: solid thin"></div>
</div>
<?php } else { ?>

<input type="button" value="<?php echo $words->getFormatted('FindPeopleEnableMap'); ?>"
    onclick="window.location='searchmembers/index';"/>

<?php } ?>
<br /><br />
<div id="member_list"></div>

<script type="text/javascript">
// hide all the filters
document.getElementsByClassName('NotDisplayed').each(Element.toggle);
// other stuff
var mapoff = <?php echo ($MapOff == "mapoff") ? 'true' : 'false'; ?>;
var loading = '<?php echo $words->getFormatted('FindPeopleIndicateLoading'); ?>';
var addressNotFound = '<?php echo $words->getFormatted('FindPeopleIndicateAddressNotFound'); ?>';
</script>
<script src="script/searchmembers.js" type="text/javascript"></script>

