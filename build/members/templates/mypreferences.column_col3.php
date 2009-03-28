       <form method="post" id="preferences" >
        <?=$callback_tag ?>
        <input type="hidden" name="memberid" value="<?=$this->member->id?>" />
        <h3><?=$words->get('PreferenceLanguage')?></h3>
        <p><?=$words->get('PreferenceLanguageDescription')?></p>
        <select name="PreferenceLanguage" >
            <?php foreach ($languages as $lang) { ?>
                <option value="<?=$lang->id ?>" <?=($lang->id == $p['PreferenceLanguage']->Value) ? 'selected' : ''?> ><?=$lang->Name ?></option>
            <?php } ?>
        </select>

<?php
	foreach ($p as $rr) {
		if ($rr->codeName != 'PreferenceLanguage') {
?>
        <h3><?=$words->get($rr->codeName)?></h3>
		<p><?=$words->get($rr->codeDescription)?></p>
		<?php
				if (isset($rr->Value) && $rr->Value != "") {
					$Value = $rr->Value;
				} else {
					$Value = $rr->DefaultValue;
				}
		?>
        <ul>
		<?
			$PossibleValueArray = explode((strpos($rr->PossibleValues,',') ? ',' : ';'),$rr->PossibleValues); 
			if (count($PossibleValueArray) > 1) {
		?>
			<? foreach ($PossibleValueArray as $PValue) : ?>
            <li><input type="radio" name="<?=$rr->codeName?>" value="<?=$PValue?>" <?=($Value == $PValue) ? 'checked' : '' ?> /><?=$words->get($rr->codeName.preg_replace("/[^a-zA-Z0-9s]/", "", $PValue))?></li>
			<? endforeach ?>
		<? } else { ?>
<li><input type="radio" name="<?=$rr->codeName?>" value="Yes" <?=($Value == 'Yes' || ($rr->Value != 'No' && $rr->DefaultValue == 'Yes')) ? 'checked' : '' ?> /><?=$words->get('Yes')?></li>
<li><input type="radio" name="<?=$rr->codeName?>" value="No" <?=($Value == 'No' || ($rr->Value != 'Yes' && $rr->DefaultValue == 'No')) ? 'checked' : '' ?> /><?=$words->get('No')?></li>
		<? } ?>
    </ul>
<?
		} // end if
	} // end foreach

?>

        <h3><?=$words->get('PreferencesPassword')?></h3>
        <p><?=$words->get('PreferencesPassword')?></p>
        <dl>
            <dt><?=$words->get('PreferencesPasswordOld')?></dt>
            <dd><input type="password" name="passwordold" /></dd>

            <dt><?=$words->get('PreferencesPasswordNew')?></dt>
            <dd><input type="password" name="passwordnew" /></dd>

            <dt><?=$words->get('PreferencesPasswordConfirm')?></dt>
            <dd><input type="password" name="passwordconfirm" /></dd>
        </dl>

        <p><input type="submit" id="submit"  value="Submit" /></p>
        
        </form>