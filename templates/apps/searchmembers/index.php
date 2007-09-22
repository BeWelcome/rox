<?php
	$i18n = new MOD_i18n('apps/rox/searchmembers.php');
  $searchmembersText = $i18n->getText('searchmembersText');
?>
<form id="searchmembers" name="searchmembers" action="javascript: {}">
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

<h3><?php echo $searchmembersText['enter_terms']; ?></h3>
<p><?php echo $searchmembersText['enter_terms_help']; ?></p>
<ul class="floatbox input_float">
  <li>
    <p><strong class="small"><?php echo $searchmembersText['username']; ?></strong><br />
    <input type="text" name="Username" size="30" maxlength="30" value="" /></p>
	</li>
  <li>
    <p><strong class="small"><?php echo $searchmembersText['age']; ?></strong><br />
    <input type="text" name="Age" size="30" maxlength="30" value="",GetStrParam("Age"),"" />
    </p>
  </li>
  <li>
    <p><strong class="small"><?php echo $searchmembersText['profile_words']; ?></strong><br />
    <input type="text" name="TextToFind" size="30" maxlength="30" value="" /></p>
  </li>
</ul>
<br />
<h3><?= $searchmembersText['filter_search'] ?></h3>
<ul class="floatbox select_float">
	<li>
	  <p><strong class="small"><?php echo $searchmembersText['gender']; ?></strong><br />
	  <select Name="Gender">
	    <option value="0"></option>
	    <option value="male"><?php echo $searchmembersText['male']; ?></option>
		  <option value="female"><?php echo $searchmembersText['female']; ?></option>
	   </select>
	  </p>
	</li>
  <li>
	  <p><strong class="small"><?php echo $searchmembersText['groups']; ?></strong><br />
	  <select name="IdGroup">";
		  <option value="0"></option>
			<?php for ($iiMax = count($TGroup), $ii = 0; $ii < $iiMax; $ii++) { ?>
      <option value="<?= $TGroup[$ii]->id; ?>"><?= "Group_".$TGroup[$ii]->Name; ?></option>
			<?php } ?>
    </select>
  	</p>
  </li>

  <li>
   <p><strong class="small"><?= $searchmembersText['typical_offer'] ?></strong><br />
	 <select name=TypicOffer[] multiple>
<?php
	for ($ii=0;$ii<count($TabTypicOffer);$ii++) {
?>
			<option value="<?php echo $TabTypicOffer[$ii]; ?>"><?php echo $searchmembersText[$TabTypicOffer[$ii]]; ?></option>
<?php } ?>
	</select>
	</p>
	</li>
</ul>
<br />
<p><input name="IncludeInactive" type="checkbox">&nbsp;<?php echo $searchmembersText['inactive']; ?></p>
<h3><?php echo $searchmembersText['search']; ?></h3>
<ul class="floatbox select_float">
	<li>
  	<p><strong class="small"><?php echo  $searchmembersText['sort_order']; ?></strong><br />
    <select Name="OrderBy">
	    <option value="0"><?php echo  $searchmembersText['new_members']; ?></option>
	    <option value="1"><?php echo  $searchmembersText['old_members']; ?></option>
	    <option value="4"><?php echo  $searchmembersText['accomodation']; ?></option>
	    <option value="5"><?php echo  $searchmembersText['accomodation'].' ('.$searchmembersText['reversed'].')'; ?></option>
	    <option value="6"><?php echo  $searchmembersText['age']; ?></option>
	    <option value="7"><?php echo  $searchmembersText['age'].' ('.$searchmembersText['reversed'].')'; ?></option>
	    <option value="12"><?php echo  $searchmembersText['city']; ?></option>
	    <option value="13"><?php echo  $searchmembersText['city'].' ('.$searchmembersText['reversed'].')'; ?></option>
	    <option value="10"><?php echo  $searchmembersText['country']; ?></option>
	    <option value="11"><?php echo  $searchmembersText['country'].' ('.$searchmembersText['reversed'].')'; ?></option>
	    <option value="2"><?php echo  $searchmembersText['last_login']; ?></option>
	    <option value="3"><?php echo  $searchmembersText['last_login'].' ('.$searchmembersText['reversed'].')'; ?></option>
	    <option value="8"><?php echo  $searchmembersText['comments']; ?></option>
	    <option value="9"><?php echo  $searchmembersText['comments'].' ('.$searchmembersText['reversed'].')'; ?></option>
    </select>
    </p>
  </li>
	<li>
  	<p><strong class="small"><?php echo  $searchmembersText['limit_count']; ?></strong><br />
    <select Name="limitcount">
	    <option value="10">10</option>
	    <option value="25">25</option>
	    <option value="50">50</option>
    </select>
    </p>
  </li>
</ul>
</form>
<br />
<?php
	if($_SERVER['HTTP_HOST'] == "localhost") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxShnDj7H5mWDU0QMRu55m8Dc2bJEg";
	else if($_SERVER['HTTP_HOST'] == "test.bewelcome.org") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBQw603b3eQwhy2K-i_GXhLp33dhxhTnvEMWZiFiBDZBqythTBcUzMyqvQ";
	else if($_SERVER['HTTP_HOST'] == "alpha.bewelcome.org") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBTnd2erWePPER5A2i02q-ulKWabWxTRVNKdnVvWHqcLw2Rf2iR00Jq_SQ";
	else $google_conf = PVars::getObj('config_google');
?>

<script src="script/prototype.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?= $google_conf->maps_api_key ?>" type="text/javascript"></script>

<form action="javascript: {}" onsubmit="reset_start_rec(0); searchByText(this.address.value); return false">
  <p>
	  <input type="text" size="60" name="address" id="address" value="Paris, FR" onfocus="this.value='';"/>
	  <input id="text_search" type="submit" value="<?= $searchmembersText['text_search'] ?>" />
  </p>
</form>
<br />
<?php if($MapOff != "mapoff") { ?>
<div style="width: 95%">
<div style="float: left">
<form action="javascript: {}" onsubmit="reset_start_rec(0); searchByMap(); return false">
    <input id="map_search" type="submit" value="<?php echo $searchmembersText['map_search']; ?>" />
</form>
</div>
<div style="float: right">
<form action="javascript: {}" onsubmit="map.clearOverlays(); getElementById('member_list').innerHTML=''; return false">
    <input type="submit" value="<?php echo $searchmembersText['clear_map']; ?>" />
</form>
</div>
</div>
<br /><br />
<div id="map" style="width: 95%; height: 480px; border: solid thin"></div>
<a href="searchmembers/mapoff"><?php echo $searchmembersText['disable_map']; ?></a>
<?php } else { ?>
<a href="searchmembers"><?php echo $searchmembersText['enable_map']; ?></a>
<?php } ?>
<br /><br />
<div id="member_list"></div>
<script type="text/javascript">
var mapoff = <?php echo ($MapOff == "mapoff") ? 'true' : 'false'; ?>;
var loading = '<?php echo $searchmembersText['loading']; ?>';
var text_search = '<?php echo $searchmembersText['text_search']; ?>';
var map_search = '<?php $searchmembersText['map_search']; ?>';
</script>
<script src="script/searchmembers.js" type="text/javascript"></script>
