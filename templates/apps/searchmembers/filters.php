﻿<?php
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

<div id="SearchAdvanced" class="clearfix" style="display:none; background: #e5e5e5 url(styles/YAML/images/teaser_shadow.gif) bottom left repeat-x">
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
<div id="FindPeopleFilter">
<table class="float_left"><tr><td>
<strong class="small"><?php echo $words->getFormatted('Username'); ?></strong><br />
<input type="text" name="Username" size="30" maxlength="30" value="" onKeyPress="if(chkEnt(this, event)) searchGlobal(0);" />
</td><td>
<strong class="small"><?php echo $words->getFormatted('TextToFind'); ?></strong><br />
<input type="text" name="TextToFind" size="30" maxlength="30" value="" onKeyPress="if(chkEnt(this, event)) searchGlobal(0);" />
</td></tr></table>
<table class="float_left">
<tr><td>
<strong class="small"><?php echo $words->getFormatted('Gender'); ?></strong><br />
<select Name="Gender">
    <option value="0"></option>
    <option value="male"><?php echo $words->getBuffered('Male'); ?></option>
    <option value="female"><?php echo $words->getBuffered('Female'); ?></option>
</select><?php echo $words->flushBuffer(); ?>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleMinimumAge'); ?></strong><br />
<select Name="MinimumAge">
    <option value="0"></option>
    <?php foreach(range(18, 90, 2) as $age) { ?>
    <option value="<?php echo $age; ?>"><?php echo $age; ?></option>
    <?php } ?>
</select><?php echo $words->flushBuffer(); ?>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleMaximumAge'); ?></strong><br />
<select Name="MaximumAge">
    <option value="0"></option>
    <?php foreach(range(18, 90, 2) as $age) { ?>
    <option value="<?php echo $age; ?>"><?php echo $age; ?></option>
    <?php } ?>
</select><?php echo $words->flushBuffer(); ?>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleMemberStatus'); ?></strong><br />
<select name="IncludeInactive">
    <option value="0"><?php echo $words->getBuffered('Active'); ?></option>
    <option value="1"><?php echo $words->getBuffered('All'); ?></option>
</select><?php echo $words->flushBuffer(); ?>
</td><td>
<strong class="small"><?php echo $words->getFormatted('Groups'); ?></strong><br />
<select name="IdGroup" class="sval">
    <option value="0"></option>
    <?php for ($iiMax = count($TGroup), $ii = 0; $ii < $iiMax; $ii++) { ?>
    <option value="<?php echo $TGroup[$ii]->id; ?>"><?php echo $TGroup[$ii]->Name; ?></option>
    <?php } ?>
</select><?php echo $words->flushBuffer(); ?>
</td></tr></table>

<table class="float_left"><tr><td valign="top">
<strong class="small"><?php echo $words->getFormatted('FindPeopleAccomodationTitle'); ?></strong><br />
<?php foreach($TabAccomodation as $TabAcc) { ?>
<input type="checkbox" name="Accomodation[]" id="<?php echo "Accomodation_$TabAcc"; ?>" value="<?php echo $TabAcc; ?>" class="sval">&nbsp;<span onclick="document.getElementById('<?php echo "Accomodation_$TabAcc"; ?>').click();"><?php echo $words->getFormatted('Accomodation_'.$TabAcc); ?></span><br />
<?php } ?>
<strong class="small">
<?php echo $words->getFormatted('FindPeopleAccomodationTip'); ?>
</strong>
</td><td valign="top">
<strong class="small"><?php echo $words->getFormatted('FindPeopleOfferTypeTitle'); ?></strong><br />
<?php foreach($TabTypicOffer as $TabTyp) { ?>
<input type="checkbox" name="TypicOffer[]" id="<?php echo "TypicOffer_$TabTyp"; ?>" value="<?php echo $TabTyp; ?>" class="sval">&nbsp;<span onclick="document.getElementById('<?php echo "TypicOffer_$TabTyp"; ?>').click();"><?php echo $words->getFormatted('TypicOffer_'.$TabTyp); ?></span><br />
<?php } ?>
<strong class="small">
<?php echo $words->getFormatted('FindPeopleTypicOfferTip'); ?>
</strong>
</td></tr></table>
</div>

<div id="FindPeopleResults">
<table class="float_left"><tr><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleSortOrder'); ?></strong><br />
<select Name="OrderBy">
    <?php foreach($TabSortOrder as $key=>$val) { ?>
    <option value="<?php echo $key; ?>"><?php echo $words->getBuffered($val); ?></option>
    <?php } ?>
</select>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleSortOrderDirection'); ?></strong><br />
<select Name="OrderByDirection">
    <option value="desc"><?php echo $words->getBuffered('Forward'); ?></option>
    <option value="asc"><?php echo $words->getBuffered('Reverse'); ?></option>
</select>
</td><td>
<strong class="small"><?php echo $words->getFormatted('FindPeopleLimitCount'); ?></strong><br />
<select Name="limitcount" class="sval">
    <option value="10">10</option>
    <option value="25" selected>25</option>
    <option value="50">50</option>
    <option value="100">100</option>
</select>
</td></tr></table>
</div>
    <div style="clear:both">
    <input id="text_search" class="button" type="button" value="<?php echo $words->getBuffered('FindPeopleSubmitSearch'); ?>"
        onclick="if(CheckEmpty(getElementById('address'))) {searchGlobal(0)} else {searchByText(get_val('address'), 0)};" /><?php echo $words->flushBuffer(); ?>
    &nbsp; &nbsp; &nbsp; 
    <input type="reset" class="button" value="<?php echo $words->getBuffered('SearchClearValues'); ?>">
        &nbsp; &nbsp; &nbsp; <a style="cursor:pointer;" id="linkadvanced" onclick="new Effect.toggle('SearchAdvanced', 'blind');"><?php echo $words->getFormatted('searchmembersHideFilters'); ?></a>
    </form>
    </div>
      <img src="styles/YAML/images/spacer.gif" width="95%" height="5px" alt="spacer" />
</div>
<script type="text/javascript">
// hide all the filters
document.getElementsByClassName('NotDisplayed').each(Element.toggle);
</script>
