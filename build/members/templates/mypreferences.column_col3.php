       <form method="post" id="preferences" action="<?=$baseuri.'mypreferences/'?>"  >
        <fieldset>
            <legend><?=$words->get('MyPreferences')?></legend>
        <?=$callback_tag ?>
        <input type="hidden" name="memberid" value="<?=$this->member->id?>" />
        <h3><?=$words->get('PreferenceLanguage')?></h3>
        <div class="subcolumns">

          <div class="c33l" >
            <div class="subcl" >
                <select name="PreferenceLanguage" >
                    <?php foreach ($languages as $lang) { ?>
                        <option value="<?=$lang->id ?>" <?=($lang->id == $p['PreferenceLanguage']->Value) ? 'selected' : ''?> ><?=$lang->TranslatedName . " (" . $lang->Name . ")"?></option>
                    <?php } ?>
                </select>
            </div> <!-- subcl -->
          </div> <!-- c50l -->
          <div class="c66r" >
            <div class="subcr" >
                <p><?=$words->get('PreferenceLanguageDesc')?></p>
            </div> <!-- subcr -->
          </div> <!-- c50r -->

        </div> <!-- subcolumns -->
        
        <h3><?=$words->get('PreferencePublicProfile')?></h3>
        <div class="subcolumns">

          <div class="c33l" >
            <div class="subcl" >
                <ul>
                    <li><input type="radio" name="PreferencePublicProfile" value="Yes" <?=(isset($pref_publicprofile) && $pref_publicprofile) ? 'checked' : '' ?> /> <?=$words->get('Yes')?></li>
                    <li><input type="radio" name="PreferencePublicProfile" value="No" <?=(isset($pref_publicprofile) && !$pref_publicprofile) ? 'checked' : '' ?> /> <?=$words->get('No')?></li>
                </ul>
            </div> <!-- subcl -->
          </div> <!-- c50l -->
          <div class="c66r" >
            <div class="subcr" >
                <p><?=$words->get('PreferencePublicProfileDesc')?></p>
            </div> <!-- subcr -->
          </div> <!-- c50r -->

        </div> <!-- subcolumns -->

<?php
        $doNotShow = array('PreferenceLanguage');
	foreach ($p as $rr) {
		if (!in_array($rr->codeName, $doNotShow)) {
?>
        <h3><?=$words->get($rr->codeName)?></h3>
		<?php
				if (isset($rr->Value) && $rr->Value != "") {
					$Value = $rr->Value;
				} else {
					$Value = $rr->DefaultValue;
				}
		?>
		
        <div class="subcolumns">

          <div class="c33l" >
            <div class="subcl" >
                <ul>
		            <?php $PossibleValueArray = explode((strpos($rr->PossibleValues,',') ? ',' : ';'),$rr->PossibleValues); ?>
		            <?php if ($rr->codeName == 'PreferenceLocalTime') { ?>
                    <select name="PreferenceLocalTime" class="prefsel">
		            <?php
                    foreach($timezones as $timezone) {
                        $option = '<option value="' . $timezone['timeshift'] . '"';
                        if ($Value == $timezone['timeshift']) {
                            $option .= ' selected="selected"';
                        }
                        $option .= '>' . $words->getBuffered($timezone['city']) . ' (';
                        $option .= $words->getBuffered('PreferenceLocalTimeUTC') . $timezone['utc'] . ')';
                        $option .= '</option>';
                        echo $option;
                    }
                    ?>
                    </select>
                    <?php echo $words->flushBuffer(); ?>
		            <?php } elseif (count($PossibleValueArray) > 1) { ?>
    			        <? foreach ($PossibleValueArray as $PValue) : ?>
                        <li>
                            <input type="radio" name="<?=$rr->codeName?>" value="<?=$PValue?>" <?=($Value == $PValue) ? 'checked' : '' ?> />
                            <label style="padding-left: 1em"><?=$words->get($rr->codeName.preg_replace("/[^a-zA-Z0-9s]/", "", $PValue))?></label>
                        </li>
    			        <? endforeach ?>
		            <? } else { ?>
                        <li><input type="radio" name="<?=$rr->codeName?>" value="Yes" <?=($Value == 'Yes' || ($rr->Value != 'No' && $rr->DefaultValue == 'Yes')) ? 'checked' : '' ?> /> <?=$words->get('Yes')?></li>
                        <li><input type="radio" name="<?=$rr->codeName?>" value="No" <?=($Value == 'No' || ($rr->Value != 'Yes' && $rr->DefaultValue == 'No')) ? 'checked' : '' ?> /> <?=$words->get('No')?></li>
            		<? } ?>
                </ul>
            </div> <!-- subcl -->
          </div> <!-- c50l -->
          <div class="c66r" >
            <div class="subcr" >
        		<p><?=$words->get($rr->codeDescription)?></p>
            </div> <!-- subcr -->
          </div> <!-- c50r -->

        </div> <!-- subcolumns -->
<?
		} // end if
	} // end foreach

?>

        <h3><?=$words->get('PreferencesPassword')?></h3>
        <p><?=$words->get('PreferencesPasswordDescription')?></p>
            <a name="password"></a>
        <dl>
            <dt><?=$words->get('PreferencesPasswordOld')?></dt>
            <dd><input type="password" name="passwordold" /></dd>

            <dt><?=$words->get('PreferencesPasswordNew')?></dt>
            <dd><input type="password" name="passwordnew" /></dd>

            <dt><?=$words->get('PreferencesPasswordConfirm')?></dt>
            <dd><input type="password" name="passwordconfirm" /></dd>
        </dl>

        <p><input type="submit" class="button" id="submit"  value="<?php echo $words->getSilent('SubmitForm'); ?>" /> <?php echo $words->flushBuffer(); ?></p>
        </fieldset>
        </form>
