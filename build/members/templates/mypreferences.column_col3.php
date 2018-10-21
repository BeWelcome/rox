
<form method="post" id="preferences" action="<?=$baseuri.'mypreferences'?>" class="w-100">
    <input type="hidden" name="memberid" value="<?=$this->member->id?>" />

        <div class="col-12"><?=$callback_tag ?></div>
        <div class="col-12"><h2><?=$words->get('MyPreferences')?></h2></div>

        <div class="col-12">
            <div id="MyPreferences" data-children=".item">

                <div class="item">
                    <div class="card-header">
                    <a data-toggle="collapse" data-parent="#MyPreferences" href="#MyPreferences1" aria-expanded="true"
                       aria-controls="MyPreferences1" class="mb-0">
                        <?=$words->get('PreferencesPassword')?>
                    </a>
                    </div>
                    <div id="MyPreferences1" class="show editprofilebox" role="tabpanel">
                        <a name="password"></a>
                        <p><?=$words->get('PreferencesPasswordDescription')?></p>
                        <div class="row">
                            <div class="col-12 col-md-4"><?=$words->get('PreferencesPasswordOld')?></div>
                            <div class="col-12 col-md-8"><input type="password" name="passwordold" /></div>
                            <div class="col-12 col-md-4"><?=$words->get('PreferencesPasswordNew')?></div>
                            <div class="col-12 col-md-8"><input type="password" name="passwordnew" /></div>
                            <div class="col-12 col-md-4"><?=$words->get('PreferencesPasswordConfirm')?></div>
                            <div class="col-12 col-md-8"><input type="password" name="passwordconfirm" /></div>
                        </div>

                    </div>
                </div>

                <div class="item">
                    <div class="card-header">
                    <a data-toggle="collapse" data-parent="#MyPreferences" href="#MyPreferences2" aria-expanded="false"
                       aria-controls="MyPreferences2" class="mb-0">
                        <?=$words->get('PreferenceLanguage')?>
                    </a>
                    </div>
                    <div id="MyPreferences2" class="collapse editprofilebox" role="tabpanel">
                        <div class="row">
                            <div class="col-12 col-md-3"><?=$words->get('PreferenceLanguageDesc')?></div>
                            <div class="col-12 col-md-9">
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
                    <div class="card-header">
                    <a data-toggle="collapse" data-parent="#MyPreferences" href="#MyPreferences3" aria-expanded="true"
                       aria-controls="MyPreferences3" class="mb-0">
                        <?=$words->get('Website')?>
                    </a>
                    </div>
                    <div id="MyPreferences3" class="collapse editprofilebox" role="tabpanel">

                            <?php
                            $doNotShow = array('PreferenceLanguage');
                            foreach ($p as $rr) {
                            if (!in_array($rr->codeName, $doNotShow)) {
                            ?>
                        <div class="row mb-3">
                            <div class="col-12 col-md-4"><?=$words->get($rr->codeName)?></div>
                            <?php
                            if (isset($rr->Value) && $rr->Value != "") {
                                $Value = $rr->Value;
                            } else {
                                $Value = $rr->DefaultValue;
                            }
                            ?>

                            <?php $PossibleValueArray = explode((strpos($rr->PossibleValues,',') ? ',' : ';'),$rr->PossibleValues); ?>
                            <?php if ($rr->codeName == 'PreferenceLocalTime') { ?>
                                <div class="col-12 col-md-4"><select name="PreferenceLocalTime" class="prefsel">
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
                                    <div class="col-12 col-md-4">
                                <? foreach ($PossibleValueArray as $PValue) : ?>
                                        <input type="radio" name="<?=$rr->codeName?>" value="<?=$PValue?>" <?=($Value == $PValue) ? 'checked' : '' ?> />
                                        <label><?=$words->get($rr->codeName.preg_replace("/[^a-zA-Z0-9s]/", "", $PValue))?></label><br>
                                <? endforeach ?>
                                    </div>
                            <? } else { ?>
                            <div class="col-12 col-md-4">
                                <input type="radio" name="<?=$rr->codeName?>" value="Yes" <?=($Value == 'Yes' || ($rr->Value != 'No' && $rr->DefaultValue == 'Yes')) ? 'checked' : '' ?> /><label><?=$words->get('Yes')?></label><br>
                                <input type="radio" name="<?=$rr->codeName?>" value="No" <?=($Value == 'No' || ($rr->Value != 'Yes' && $rr->DefaultValue == 'No')) ? 'checked' : '' ?> /><label><?=$words->get('No')?></label>
                            </div>
                                <? } ?>

                                <div class="col-12 col-md-4 small">
                                    <a tabindex="0" class="btn btn-outline-primary btn-sm ml-1 py-0" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-trigger="focus" data-html="true" data-content="<?= htmlentities($words->get($rr->codeDescription)) ?>">
                                        <i class="fa fa-question"></i>
                                    </a>
                                </div>
                        </div>
                            <? } // end if
                              } // end foreach
                             ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mt-3"><input type="submit" class="btn btn-primary float-right" id="submit" value="<?php echo $words->getSilent('SubmitForm'); ?>" /> <?php echo $words->flushBuffer(); ?></div>

</form>

<script type="text/javascript">
    $(function () {
        $('[data-toggle="popover"]').popover()
    })
    $('.popover-dismiss').popover({
        trigger: 'focus'
    })
</script>