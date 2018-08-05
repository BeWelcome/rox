
<form method="post" id="preferences" action="<?=$baseuri.'mypreferences'?>">
    <input type="hidden" name="memberid" value="<?=$this->member->id?>" />

    <div class="row mt-3">
        <div class="col-12"><?=$callback_tag ?></div>
        <div class="col-12"><h2><?=$words->get('MyPreferences')?></h2></div>

        <div class="col-12">
            <div id="MyPreferences" data-children=".item">

                <div class="item">
                    <a data-toggle="collapse" data-parent="#MyPreferences" href="#MyPreferences1" aria-expanded="false"
                       aria-controls="MyPreferences1" class="btn btn-light editbutton text-left">
                        <i class="fa fa-angle-down"></i> <?=$words->get('PreferencesPassword')?>
                    </a>
                    <div id="MyPreferences1" class="collapse editprofilebox" role="tabpanel">
                        <a name="password"></a>
                        <p><?=$words->get('PreferencesPasswordDescription')?></p>
                        <div class="row">
                            <div class="col-3"><?=$words->get('PreferencesPasswordOld')?></div>
                            <div class="col-9"><input type="password" name="passwordold" /></div>
                            <div class="col-3"><?=$words->get('PreferencesPasswordNew')?></div>
                            <div class="col-9"><input type="password" name="passwordnew" /></div>
                            <div class="col-3"><?=$words->get('PreferencesPasswordConfirm')?></div>
                            <div class="col-9"><input type="password" name="passwordconfirm" /></div>
                        </div>

                    </div>
                </div>

                <div class="item">
                    <a data-toggle="collapse" data-parent="#MyPreferences" href="#MyPreferences2" aria-expanded="false"
                       aria-controls="MyPreferences2" class="btn btn-light editbutton text-left">
                        <i class="fa fa-angle-down"></i> <?=$words->get('PreferenceLanguage')?>
                    </a>
                    <div id="MyPreferences2" class="collapse editprofilebox" role="tabpanel">
                        <div class="row">
                            <div class="col-3 mt-3"><?=$words->get('PreferenceLanguageDesc')?></div>
                            <div class="col-9">
                                <select name="PreferenceLanguage" >
                                    <?php foreach ($languages as $lang) { ?>
                                        <option value="<?=$lang->id ?>" <?=($lang->id == $p['PreferenceLanguage']->Value) ? 'selected' : ''?> ><?=$lang->TranslatedName . " (" . $lang->Name . ")"?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="item">
                    <a data-toggle="collapse" data-parent="#MyPreferences" href="#MyPreferences3" aria-expanded="true"
                       aria-controls="MyPreferences3" class="btn btn-light editbutton text-left">
                        <i class="fa fa-angle-down"></i> <?=$words->get('Website')?>
                    </a>
                    <div id="MyPreferences3" class="collapse editprofilebox" role="tabpanel">
                        <div class="row">
                            <div class="col-3">
                                <?=$words->get('PreferencePublicProfile')?>
                            </div>
                            <div class="col-4">
                                <input type="radio" name="PreferencePublicProfile" value="Yes" <?=(isset($pref_publicprofile) && $pref_publicprofile) ? 'checked' : '' ?> /> <?=$words->get('Yes')?><br>
                                <input type="radio" name="PreferencePublicProfile" value="No" <?=(isset($pref_publicprofile) && !$pref_publicprofile) ? 'checked' : '' ?> /> <?=$words->get('No')?>
                            </div>
                            <div class="col-5 small">
                                <?=$words->get('PreferencePublicProfileDesc')?>
                            </div>
                            <div class="col-12 mt-3"></div>

                            <?php
                            $doNotShow = array('PreferenceLanguage');
                            foreach ($p as $rr) {
                            if (!in_array($rr->codeName, $doNotShow)) {
                            ?>
                            <div class="col-3"><?=$words->get($rr->codeName)?></div>
                            <?php
                            if (isset($rr->Value) && $rr->Value != "") {
                                $Value = $rr->Value;
                            } else {
                                $Value = $rr->DefaultValue;
                            }
                            ?>

                            <?php $PossibleValueArray = explode((strpos($rr->PossibleValues,',') ? ',' : ';'),$rr->PossibleValues); ?>
                            <?php if ($rr->codeName == 'PreferenceLocalTime') { ?>
                                <div class="col-4"><select name="PreferenceLocalTime" class="prefsel">
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
                                </div>
                                <?php echo $words->flushBuffer(); ?>
                            <?php } elseif (count($PossibleValueArray) > 1) { ?>
                                    <div class="col-4">
                                <? foreach ($PossibleValueArray as $PValue) : ?>
                                        <input type="radio" name="<?=$rr->codeName?>" value="<?=$PValue?>" <?=($Value == $PValue) ? 'checked' : '' ?> />
                                        <label><?=$words->get($rr->codeName.preg_replace("/[^a-zA-Z0-9s]/", "", $PValue))?></label><br>
                                <? endforeach ?>
                                    </div>
                            <? } else { ?>
                            <div class="col-4">
                                <input type="radio" name="<?=$rr->codeName?>" value="Yes" <?=($Value == 'Yes' || ($rr->Value != 'No' && $rr->DefaultValue == 'Yes')) ? 'checked' : '' ?> /> <?=$words->get('Yes')?>
                                <input type="radio" name="<?=$rr->codeName?>" value="No" <?=($Value == 'No' || ($rr->Value != 'Yes' && $rr->DefaultValue == 'No')) ? 'checked' : '' ?> /> <?=$words->get('No')?>
                            </div>
                                <? } ?>
                                <div class="col-5 small">
                                    <?=$words->get($rr->codeDescription)?>
                                </div>
                                <div class="col-12 mt-3"></div>
                            <? } // end if
                            } // end foreach
                             ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mt-3"><input type="submit" class="btn btn-primary editbutton text-left" id="submit" value="<?php echo $words->getSilent('SubmitForm'); ?>" /> <?php echo $words->flushBuffer(); ?></div>
    </div>
</form>
