<?
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
<input type="hidden" name="<?= $callbackId ?>" value="1" />

<h3><?= $searchmembersText['enter_terms'] ?></h3>
<p><?= $searchmembersText['enter_terms_help'] ?></p>
<ul class="floatbox input_float">
  <li>
    <p><strong class="small"><?= $searchmembersText['username'] ?></strong><br />
    <input type="text" name="Username" size="30" maxlength="30" value="" /></p>
	</li>
  <li>
    <p><strong class="small"><?= $searchmembersText['age'] ?></strong><br />
    <input type="text" name="Age" size="30" maxlength="30" value="",GetStrParam("Age"),"" />
    </p>
  </li>
  <li>
    <p><strong class="small"><?= $searchmembersText['profile_words'] ?></strong><br />
    <input type="text" name="TextToFind" size="30" maxlength="30" value="" /></p>
  </li>
</ul>
<br />
<h3><?= $searchmembersText['filter_search'] ?></h3>
<ul class="floatbox select_float">
	<li>
	  <p><strong class="small"><?= $searchmembersText['gender'] ?></strong><br />
	  <select Name="Gender">
	    <option value="0"></option>
	    <option value="male"><?= $searchmembersText['male'] ?></option>
		  <option value="female"><?= $searchmembersText['female'] ?></option>
	   </select>
	  </p>
	</li>
  <li>
	  <p><strong class="small"><?= $searchmembersText['groups'] ?></strong><br />
	  <select name="IdGroup">";
		  <option value="0"></option>
			<? for ($iiMax = count($TGroup), $ii = 0; $ii < $iiMax; $ii++) { ?>
      <option value="<?= $TGroup[$ii]->id; ?>"><?= "Group_".$TGroup[$ii]->Name; ?></option>
			<? } ?>
    </select>
  	</p>
  </li>

  <li>
   <p><strong class="small"><?= $searchmembersText['typical_offer'] ?></strong><br />
	 <select name=TypicOffer[] multiple>
<?
	for ($ii=0;$ii<count($TabTypicOffer);$ii++) {
?>
			<option value="<?= $TabTypicOffer[$ii]; ?>"><?= $searchmembersText[$TabTypicOffer[$ii]] ?></option>
<? } ?>
	</select>
	</p>
	</li>
</ul>
<br />
<p><input name="IncludeInactive" type="checkbox">&nbsp;<?= $searchmembersText['inactive'] ?></p>
<h3><?= $searchmembersText['search'] ?></h3>
<ul class="floatbox select_float">
	<li>
  	<p><strong class="small"><?= $searchmembersText['sort_order'] ?></strong><br />
    <select Name="OrderBy">
	    <option value="0"><?= $searchmembersText['new_members'] ?></option>
	    <option value="1"><?= $searchmembersText['old_members'] ?></option>
	    <option value="4"><?= $searchmembersText['accomodation'] ?></option>
	    <option value="5"><?= $searchmembersText['accomodation'].' ('.$searchmembersText['reversed'].')' ?></option>
	    <option value="6"><?= $searchmembersText['age'] ?></option>
	    <option value="7"><?= $searchmembersText['age'].' ('.$searchmembersText['reversed'].')' ?></option>
	    <option value="12"><?= $searchmembersText['city'] ?></option>
	    <option value="13"><?= $searchmembersText['city'].' ('.$searchmembersText['reversed'].')' ?></option>
	    <option value="10"><?= $searchmembersText['country'] ?></option>
	    <option value="11"><?= $searchmembersText['country'].' ('.$searchmembersText['reversed'].')' ?></option>
	    <option value="2"><?= $searchmembersText['last_login'] ?></option>
	    <option value="3"><?= $searchmembersText['last_login'].' ('.$searchmembersText['reversed'].')' ?></option>
	    <option value="8"><?= $searchmembersText['comments'] ?></option>
	    <option value="9"><?= $searchmembersText['comments'].' ('.$searchmembersText['reversed'].')' ?></option>
    </select>
    </p>
  </li>
	<li>
  	<p><strong class="small"><?= $searchmembersText['limit_count'] ?></strong><br />
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
<?
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
<? if($MapOff != "mapoff") { ?>
<div style="width: 740px">
<div style="float: left">
<form action="javascript: {}" onsubmit="reset_start_rec(0); searchByMap(); return false">
    <input id="map_search" type="submit" value="<?= $searchmembersText['map_search'] ?>" />
</form>
</div>
<div style="float: right">
<form action="javascript: {}" onsubmit="map.clearOverlays(); getElementById('member_list').innerHTML=''; return false">
    <input type="submit" value="<?= $searchmembersText['clear_map'] ?>" />
</form>
</div>
</div>
<br /><br />
<div id="map" style="width: 740px; height: 480px; border: solid thin"></div>
<a href="rox/searchmembers/mapoff"><?= $searchmembersText['disable_map'] ?></a>
<? } ?>
<br /><br />
<div id="member_list"></div>
<script type="text/javascript">
var mapoff = <?= ($MapOff == "mapoff") ? 'true' : 'false' ?>;
var loading = '<?= $searchmembersText['loading'] ?>';
var text_search = '<?= $searchmembersText['text_search'] ?>';
var map_search = '<?= $searchmembersText['map_search'] ?>';
</script>
<script src="script/searchmembers.js" type="text/javascript"></script>
