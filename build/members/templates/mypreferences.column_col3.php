<div class="row">
<form method="post" id="preferences" action="<?=$baseuri.'mypreferences'?>" class="w-100">
    <input type="hidden" name="memberid" value="<?=$this->member->id?>" />

        <div class="col-12"><?=$callback_tag ?></div>
        <div class="col-12 mt-3"><h2><?=$words->get('MyPreferences')?></h2></div>

        <div class="col-12">
            <div id="MyPreferences" data-children=".item">

                <div class="item">
                    <div class="card-header">
                    <a data-toggle="collapse" href="#MyPreferences1" aria-expanded="true"
                       aria-controls="MyPreferences1" class="mb-0 d-block">
                        <?=$words->get('PreferencesPassword')?>
                    </a>
                    </div>
                    <div id="MyPreferences1" data-parent="#MyPreferences" class="show editprofilebox" role="tabpanel">
                        <p><?=$words->get('PreferencesPasswordDescription')?></p>
                        <div class="o-form-group row">
                            <label for="passwordold" class="col-md-3"><?=$words->get('PreferencesPasswordOld')?></label>
                            <div class="col-12 col-md-9"><input type="password" name="passwordold" class="o-input" /></div>
                        </div>
                        <div class="o-form-group row">
                            <label for="passwordnew" class="col-md-3"><?=$words->get('PreferencesPasswordNew')?></label>
                            <div class="col-12 col-md-9"><input type="password" name="passwordnew" class="o-input" /></div>
                        </div>
                        <div class="o-form-group row">
                            <label for="passwordconfirm" class="col-md-3"><?=$words->get('PreferencesPasswordConfirm')?></label>
                            <div class="col-12 col-md-9"><input type="password" name="passwordconfirm" class="o-input" /></div>
                        </div>

                    </div>
                </div>

                <div class="item">
                    <div class="card-header">
                    <a data-toggle="collapse" href="#MyPreferences2" aria-expanded="false"
                       aria-controls="MyPreferences2" class="mb-0 d-block collapsed">
                        <?=$words->get('PreferenceLanguage')?>
                    </a>
                    </div>
                    <div id="MyPreferences2" data-parent="#MyPreferences" class="collapse editprofilebox" role="tabpanel">
                        <div class="form-row">
                            <label for="PreferenceLanguage" class="col-md-3"><?=$words->get('PreferenceLanguageDesc')?></label>
                            <div class="col-12 col-md-9">
                                <select name="PreferenceLanguage" class="select2">
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
                    <a data-toggle="collapse" href="#MyPreferences3" aria-expanded="true"
                       aria-controls="MyPreferences3" class="mb-0 d-block collapsed">
                        <?=$words->get('Website')?>
                    </a>
                    </div>
                    <div id="MyPreferences3" data-parent="#MyPreferences" class="collapse editprofilebox" role="tabpanel">

                            <?php
                            $doNotShow = array('PreferenceLanguage');
                            foreach ($p as $rr) {
                            if (!in_array($rr->codeName, $doNotShow)) {
                            ?>
                        <div class="o-form-group row mb-3">
                            <label for="<?= $rr->codeName; ?>" class="col-10 col-md-4"><?=$words->get($rr->codeName)?></label>
                            <?php
                            if (isset($rr->Value) && $rr->Value != "") {
                                $Value = $rr->Value;
                            } else {
                                $Value = $rr->DefaultValue;
                            }
                            ?>

                            <?php $PossibleValueArray = explode((strpos($rr->PossibleValues,',') ? ',' : ';'),$rr->PossibleValues); ?>
                            <?php if ($rr->codeName == 'PreferenceLocalTime') { ?>
                                <div class="col-12 col-md-7 order-3 order-md-2"><select name="PreferenceLocalTime" class="prefsel select2">
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
                                    <div class="col-12 col-md-7 order-3 order-md-2">
                                <?php foreach ($PossibleValueArray as $PValue) : ?>
                                        <input type="radio" id="<?=$rr->codeName?><?=$PValue?>" name="<?=$rr->codeName?>" value="<?=$PValue?>" <?=($Value == $PValue) ? 'checked' : '' ?> />
                                        <label for="<?=$rr->codeName?><?=$PValue?>"><?=$words->get($rr->codeName.preg_replace("/[^a-zA-Z0-9s]/", "", $PValue))?></label><br>
                                <?php endforeach ?>
                                    </div>
                            <?php } else { ?>
                            <div class="col-12 col-md-7 order-3 order-md-2">
                                <input type="radio" id="<?=$rr->codeName?>Yes" name="<?=$rr->codeName?>" value="Yes" <?=($Value == 'Yes' || ($rr->Value != 'No' && $rr->DefaultValue == 'Yes')) ? 'checked' : '' ?> /><label for="<?=$rr->codeName?>Yes"><?=$words->get('Yes')?></label><br>
                                <input type="radio" id="<?=$rr->codeName?>No" name="<?=$rr->codeName?>" value="No" <?=($Value == 'No' || ($rr->Value != 'Yes' && $rr->DefaultValue == 'No')) ? 'checked' : '' ?> /><label for="<?=$rr->codeName?>No"><?=$words->get('No')?></label>
                            </div>
                                <?php } ?>

                                <div class="col-2 col-md-1 small order-2 order-md-3">
                                    <a tabindex="0" class="btn btn-primary btn-sm ml-1 py-0" data-container="body" data-toggle="popover" data-placement="right" data-trigger="focus" data-html="true" data-content="<?= htmlentities($words->get($rr->codeDescription)) ?>">
                                        <i class="fa fa-question white"></i>
                                    </a>
                                </div>
                        </div>
                            <?php } // end if
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
</div>
