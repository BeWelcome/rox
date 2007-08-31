<?
	$i18n = new MOD_i18n('apps/rox/membersearch.php');
  $membersearchText = $i18n->getText('membersearchText');
?>
<form id="membersearch" name="membersearch" action="javascript: {}">
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
<input type="hidden" name="limitcount" id="limitcount" />
<input type="hidden" name="<?= $callbackId ?>" value="1" />

<h3><?= $membersearchText['enter_terms'] ?></h3>
<p><?= $membersearchText['enter_terms_help'] ?></p>
<ul class="floatbox input_float">
  <li>
    <p><strong class="small"><?= $membersearchText['username'] ?></strong><br />
    <input type="text" name="Username" size="30" maxlength="30" value="" /></p>
	</li>
  <li>
    <p><strong class="small"><?= $membersearchText['age'] ?></strong><br />
    <input type="text" name="Age" size="30" maxlength="30" value="",GetStrParam("Age"),"" />
    </p>
  </li>
  <li>
    <p><strong class="small"><?= $membersearchText['profile_words'] ?></strong><br />
    <input type="text" name="TextToFind" size="30" maxlength="30" value="" /></p>
  </li>
</ul>
<br />
<h3><?= $membersearchText['filter_search'] ?></h3>
<ul class="floatbox select_float">
	<li>
	  <p><strong class="small"><?= $membersearchText['gender'] ?></strong><br />
	  <select Name="Gender">
	    <option value="0"></option>
	    <option value="male"><?= $membersearchText['male'] ?></option>
		  <option value="female"><?= $membersearchText['female'] ?></option>
	   </select>
	  </p>
	</li>
  <li>
	  <p><strong class="small"><?= $membersearchText['groups'] ?></strong><br />
	  <select name="IdGroup">";
		  <option value="0"></option>
			<? for ($iiMax = count($TGroup), $ii = 0; $ii < $iiMax; $ii++) { ?>
      <option value="<?= $TGroup[$ii]->id; ?>"><?= "Group_".$TGroup[$ii]->Name; ?></option>
			<? } ?>
    </select>
  	</p>
  </li>

  <li>
   <p><strong class="small"><?= $membersearchText['typical_offer'] ?></strong><br />
	 <select name=TypicOffer[] multiple>
<?
	for ($ii=0;$ii<count($TabTypicOffer);$ii++) {
?>
			<option value="<?= $TabTypicOffer[$ii]; ?>"><?= $membersearchText[$TabTypicOffer[$ii]] ?></option>
<? } ?>
	</select>
	</p>
	</li>
</ul>
<br />
<p><input name="IncludeInactive" type="checkbox">&nbsp;<?= $membersearchText['inactive'] ?></p>
</form>
<h3><?= $membersearchText['search'] ?></h3>
<ul class="floatbox select_float">
	<li>
  	<p><strong class="small"><?= $membersearchText['sort_order'] ?></strong><br />
    <select Name="OrderBy">
	    <option value="0"></option>
	    <option value="4"><?= $membersearchText['accomodation'] ?></option>
	    <option value="5"><?= $membersearchText['accomodation'].' ('.$membersearchText['reversed'].')' ?></option>
	    <option value="6"><?= $membersearchText['age'] ?></option>
	    <option value="7"><?= $membersearchText['age'].' ('.$membersearchText['reversed'].')' ?>Age (reversed)</option>
	    <option value="12"><?= $membersearchText['city'] ?></option>
	    <option value="13"><?= $membersearchText['city'].' ('.$membersearchText['reversed'].')' ?></option>
	    <option value="10"><?= $membersearchText['country'] ?></option>
	    <option value="11"><?= $membersearchText['country'].' ('.$membersearchText['reversed'].')' ?></option>
	    <option value="2"><?= $membersearchText['last_login'] ?></option>
	    <option value="3"><?= $membersearchText['last_login'].' ('.$membersearchText['reversed'].')' ?></option>
	    <option value="8"><?= $membersearchText['comments'] ?></option>
	    <option value="9"><?= $membersearchText['comments'].' ('.$membersearchText['reversed'].')' ?></option>
    </select>
    </p>
  </li>
</ul>
<br />
<?
	if($_SERVER['HTTP_HOST'] == "localhost") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxShnDj7H5mWDU0QMRu55m8Dc2bJEg";
	else if($_SERVER['HTTP_HOST'] == "test.bewelcome.org") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBQw603b3eQwhy2K-i_GXhLp33dhxhTnvEMWZiFiBDZBqythTBcUzMyqvQ";
	else if($_SERVER['HTTP_HOST'] == "alpha.bewelcome.org") $google_conf->maps_api_key = "ABQIAAAARaC_q9WJHfFkobcvibZvUBTnd2erWePPER5A2i02q-ulKWabWxTRVNKdnVvWHqcLw2Rf2iR00Jq_SQ";
	else $google_conf = PVars::getObj('config_google');
?>

<script src="script/prototype.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?= $google_conf->maps_api_key ?>" type="text/javascript"></script>

<form action="javascript: {}" onsubmit="reset_start_rec(0); showAddress(this.address.value); return false">
  <p>
	  <input type="text" size="60" name="address" id="address" value="Paris, FR" onfocus="this.value='';"/>
	  <input id="text_search" type="submit" value="<?= $membersearchText['text_search'] ?>" />
  </p>
</form>
<br />
<? if($MapOff != "mapoff") { ?>
<div style="width: 740px">
<div style="float: left">
<form action="javascript: {}" onsubmit="reset_start_rec(0); update_map_loc(); return false">
    <input id="map_search" type="submit" value="<?= $membersearchText['map_search'] ?>" />
</form>
</div>
<div style="float: right">
<form action="javascript: {}" onsubmit="map.clearOverlays(); getElementById('member_list').innerHTML=''; return false">
    <input type="submit" value="<?= $membersearchText['clear_map'] ?>" />
</form>
</div>
</div>
<br /><br />
<div id="map" style="width: 740px; height: 480px; border: solid thin"></div>
<a href="rox/searchmembers/mapoff"><?= $membersearchText['disable_map'] ?></a>
<? } ?>
<br /><br />
<div id="member_list"></div>
<script type="text/javascript">
var mapoff = <?= ($MapOff == "mapoff") ? 'true' : 'false' ?>;
</script>
<script src="script/membersearch.js" type="text/javascript"></script>
