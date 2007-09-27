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
<form id="searchmembers" name="searchmembers" action="javascript: {}"
      onsubmit="reset_start_rec(0); searchByMap();return false;">
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
<input type="hidden" name="<?php echo $callbackId; ?>" value="1" />

<h3><?php echo $words->getFormatted('FindPeopleSearchTerms'); ?></h3>
<p><?php echo $words->getFormatted('FindPeopleSearchTermsExp'); ?></p>
<ul class="floatbox input_float">
  <li>
    <p><strong class="small"><?php echo $words->getFormatted('Username'); ?></strong><br />
    <input type="text" name="Username" size="30" maxlength="30" value="" /></p>
  </li>
  <li>
    <p><strong class="small"><?php echo $words->getFormatted('Age'); ?></strong><br />
    <input type="text" name="Age" size="30" maxlength="30" value="",GetStrParam("Age"),"" />
    </p>
  </li>
  <li>
    <p><strong class="small"><?php echo $words->getFormatted('TextToFind'); ?></strong><br />
    <input type="text" name="TextToFind" size="30" maxlength="30" value="" /></p>
  </li>
</ul>
<br/>
<h3><?php echo $words->getFormatted('FindPeopleFilter'); ?></h3>
<ul class="floatbox select_float">
  <li><p><strong class="small"><?php echo $words->getFormatted('Gender'); ?></strong><br />
	  <select Name="Gender">
	    <option value="0"></option>
	    <option value="male"><?php echo $words->getFormatted('Male'); ?></option>
		  <option value="female"><?php echo $words->getFormatted('Female'); ?></option>
	   </select>
	  </p>
  </li>
  <li>
    <p><strong class="small"><?php echo $words->getFormatted('Groups'); ?></strong><br />
	  <select name="IdGroup">
		  <option value="0"></option>
			<?php for ($iiMax = count($TGroup), $ii = 0; $ii < $iiMax; $ii++) { ?>
       <option value="<?php echo $TGroup[$ii]->id; ?>"><?php echo "Group_".$TGroup[$ii]->Name; ?></option>
			<?php } ?>
    </select>
  	</p>
  </li>

  <li>
   <p><strong class="small"><?php echo $words->getFormatted('FindPeopleOfferTypeTitle'); ?></strong><br />
	 <select name="TypicOffer[]" multiple="multiple">
<?php
	for ($ii=0; $ii<count($TabTypicOffer); $ii++) {
	   // FIXME:  The displayed values are directly taken from the set of column
	   // values. I had no idea how to solve this for now in a reasonable way.
?>
			<option value="<?php echo $TabTypicOffer[$ii]; ?>"><?php echo $TabTypicOffer[$ii]; ?></option>
<?php } ?>
	</select>
	</p>
	</li>
</ul>
<br />
<p><input name="IncludeInactive" type="checkbox">&nbsp;
	<?php echo $words->getFormatted('FindPeopleIncludeInactive'); ?>
</p>
<h3><?php echo $words->getFormatted('SearchPage'); ?></h3>
<ul class="floatbox select_float">
	<li>
  	<p><strong class="small"><?php echo $words->getFormatted('FindPeopleSortOrder'); ?></strong><br />
    <select Name="OrderBy">
	    <option value="0"><?php echo $words->getFormatted('FindPeopleSortOrderNewMembers'); ?></option>
	    <option value="1"><?php echo $words->getFormatted('FindPeopleSortOrderOldMembers'); ?></option>
	    <option value="4"><?php echo $words->getFormatted('FindPeopleSortOrderAccomodation'); ?></option>
	    <option value="5"><?php echo $words->getFormatted('FindPeopleSortOrderAccomodation'); ?> (<?php echo $words->getFormatted('FindPeopleSortOrderReversed'); ?>)</option>
	    <option value="6"><?php echo $words->getFormatted('Age'); ?></option>
	    <option value="7"><?php echo $words->getFormatted('Age'); ?> (<?php echo $words->getFormatted('FindPeopleSortOrderReversed'); ?>)</option>
	    <option value="12"><?php echo $words->getFormatted('City'); ?></option>
	    <option value="13"><?php echo $words->getFormatted('City'); ?> (<?php echo $words->getFormatted('FindPeopleSortOrderReversed'); ?>)</option>
	    <option value="10"><?php echo $words->getFormatted('country'); ?></option>
	    <option value="11"><?php echo $words->getFormatted('country'); ?> (<?php echo $words->getFormatted('FindPeopleSortOrderReversed'); ?>)</option>
	    <option value="2"><?php echo $words->getFormatted('Lastlogin'); ?></option>
	    <option value="3"><?php echo $words->getFormatted('Lastlogin'); ?> (<?php echo $words->getFormatted('FindPeopleSortOrderReversed'); ?>)</option>
	    <option value="8"><?php echo $words->getFormatted('FindPeopleSortOrderComments'); ?></option>
	    <option value="9"><?php echo $words->getFormatted('FindPeopleSortOrderComments'); ?> (<?php echo $words->getFormatted('FindPeopleSortOrderReversed'); ?>)</option>
    </select>
    </p>
  </li>
	<li>
  	<p><strong class="small"><?php echo $words->getFormatted('FindPeopleLimitCount'); ?></strong><br />
    <select Name="limitcount">
	    <option value="10">10</option>
	    <option value="25">25</option>
	    <option value="50">50</option>
    </select>
    </p>
  </li>
</ul>
<br/>
<p>
	  <input type="text" size="60" name="address" id="address" value="<?php echo ""; // FIXME ?>" onfocus="this.value='';"/>
	  <input id="text_search" type="button" value="<?php echo $words->getFormatted('FindPeopleSubmitTextSearch'); ?>"
	 		onclick="reset_start_rec(0); searchByText(this.address.value);"/>
  </p>
<br/>
<?php if ($MapOff != "mapoff") { ?>

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $google_conf->maps_api_key; ?>" type="text/javascript"></script>

<div style="width: 95%">
  <div style="float: left">
    <input id="map_search" type="submit" value="<?php echo $words->getFormatted('FindPeopleIndicateSearchTypeMap'); ?>"/>
  </div>
  <div style="float: right">
  <input type="button" value="<?php echo $words->getFormatted('FindPeopleClearMap'); ?>" 
		   onclick="map.clearOverlays(); getElementById('member_list').innerHTML='';"/>		
  </div>
</div>
</form>
<br /><br />
<div id="map" style="width: 95%; height: 480px; border: solid thin"></div>
<a href="searchmembers/index?mapoff"><?php echo $words->getFormatted('FindPeopleDisableMap'); ?></a>
<?php } else { ?>
</form>
<a href="searchmembers/index"><?php echo $words->getFormatted('FindPeopleEnableMap'); ?></a>
<?php } ?>
<br /><br />
<div id="member_list"></div>
<script type="text/javascript">
var mapoff = <?php echo ($MapOff == "mapoff") ? 'true' : 'false'; ?>;
var loading = '<?php echo $words->getFormatted('FindPeopleIndicateLoading'); ?>';
var text_search = '<?php echo $words->getFormatted('FindPeopleIndicateSearchTypeText'); ?>';
var map_search = '<?php echo $words->getFormatted('FindPeopleIndicateSearchTypeMapBoundaries'); ?>';
</script>
<script src="script/searchmembers.js" type="text/javascript"></script>

