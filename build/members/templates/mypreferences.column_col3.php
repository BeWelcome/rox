       <form method="post" id="preferences" >
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
                        <option value="<?=$lang->id ?>" <?=($lang->id == $p['PreferenceLanguage']->Value) ? 'selected' : ''?> ><?=$lang->Name ?></option>
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
	foreach ($p as $rr) {
		if ($rr->codeName != 'PreferenceLanguage') {
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
		            <?php
		            echo "<select name='PreferenceLocalTime' class='prefsel'>" ;
                     echo "<option value='-39600'"; if ($Value==-39600) echo  " selected ";echo ">Pago Pago (UTC-11)</option>";
		             echo "<option value='-36000'"; if ($Value==-36000) echo  " selected ";echo ">Honululu (UTC-10)</option>";
                     echo "<option value='-32400'"; if ($Value==-32400) echo  " selected ";echo ">Fairbanks (UTC-9)</option>";
                     echo "<option value='-28800'"; if ($Value==-28800) echo  " selected ";echo ">Los Angeles (UTC-8)</option>";
		             echo "<option value='-25200'"; if ($Value==-25200) echo  " selected ";echo ">Calgari (UTC-7)</option>";
                     echo "<option value='-21600'"; if ($Value==-21600) echo  " selected ";echo ">Mexico (UTC-6)</option>";
                     echo "<option value='-18000'"; if ($Value==-18000) echo  " selected ";echo ">New York (UTC-5)</option>";
                     echo "<option value='-14400'"; if ($Value==-14400) echo  " selected ";echo ">Santiago (UTC-4)</option>";
                     echo "<option value='-10800'"; if ($Value==-10800) echo  " selected ";echo ">Sao Paulo (UTC-3)</option>";
                     echo "<option value='-7200'"; if ($Value==-7200) echo  " selected ";echo ">Fernando de Noronha (UTC-2)</option>";
                     echo "<option value='-3600'"; if ($Value==-3600) echo  " selected ";echo ">Cape Verde (UTC-1)</option>";
                     echo "<option value='0'"; if ($Value==0) echo  " selected ";echo ">London (UTC-0)</option>";
                     echo "<option value='3600'"; if ($Value==3600) echo  " selected ";echo ">Paris, Berlin (UTC+1)</option>";
                     echo "<option value='7200'"; if ($Value==7200) echo  " selected ";echo ">Cairo (UTC+2)</option>";
                     echo "<option value='10800'"; if ($Value==10800) echo  " selected ";echo ">Moscow (UTC+3)</option>";
                     echo "<option value='14400'"; if ($Value==14400) echo  " selected ";echo ">Dubai (UTC+4)</option>";
                     echo "<option value='18000'"; if ($Value==18000) echo  " selected ";echo ">Karachi (UTC+5)</option>";
                     echo "<option value='19800'"; if ($Value==19800) echo  " selected ";echo ">Mumbai (UTC+5.5)</option>";
                     echo "<option value='21600'"; if ($Value==21600) echo  " selected ";echo ">Dhaka (UTC+6)</option>";
                     echo "<option value='25200'"; if ($Value==25200) echo  " selected ";echo ">Jakarta (UTC+7)</option>";
                     echo "<option value='28800'"; if ($Value==28800) echo  " selected ";echo ">Hong Kong (UTC+8)</option>";
                     echo "<option value='32400'"; if ($Value==32400) echo  " selected ";echo ">Tokyo (UTC+9)</option>";
                     echo "<option value='36000'"; if ($Value==36000) echo  " selected ";echo ">Sydney (UTC+10)</option>";
                     echo "<option value='39600'"; if ($Value==39600) echo  " selected ";echo ">Noumea (UTC+11)</option>";
                     echo "<option value='43200'"; if ($Value==43200) echo  " selected ";echo ">Auckland (UTC+12)</option>";
                     echo "</select>" ;
                    ?>
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
        <dl>
            <dt><?=$words->get('PreferencesPasswordOld')?></dt>
            <dd><input type="password" name="passwordold" /></dd>

            <dt><?=$words->get('PreferencesPasswordNew')?></dt>
            <dd><input type="password" name="passwordnew" /></dd>

            <dt><?=$words->get('PreferencesPasswordConfirm')?></dt>
            <dd><input type="password" name="passwordconfirm" /></dd>
        </dl>

        <p><input type="submit" id="submit"  value="<?php echo $words->get('SubmitForm'); ?>" /></p>
        </fieldset>
        </form>
