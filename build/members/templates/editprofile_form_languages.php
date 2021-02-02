<div class="tab-pane fade card" id="languages" role="tabpanel" aria-labelledby="languages-tab">
    <div class="card-header" role="tab" id="heading-languages">
        <h5 class="mb-0">
            <a data-toggle="collapse" href="#collapse-languages" data-parent="#content" aria-expanded="true" aria-controls="collapse-languages">
                <?= $words->get('Languages') ?>
            </a>
        </h5>
    </div>
    <div id="collapse-languages" class="collapse" role="tabpanel" aria-labelledby="heading-languages">
        <div class="card-body">
            <?php
            $lang_ids = array();
            for ($ii = 0; $ii < count($vars['languages_selected']); $ii++) {
                $lang_ids[] = $vars['languages_selected'][$ii]->IdLanguage; } ?>
            <div class="row">
                <label for="ProfileLanguagesSpoken" class="col-form-label">
                <?= $words->get('ProfileLanguagesSpoken') ?>
                </label>
            </div>
            <?php for ($ii = 0; $ii < count($vars['languages_selected']); $ii++) { ?>
                <div id="lang_<?= $ii ?>_row" class="o-form-group row">
                    <div class="col-2 col-md-1">
                        <button class="btn btn-outline-danger p-1 px-2 remove_lang" id="lang_<?= $ii ?>" title="<?= $words->get('RemoveLanguage') ?>"><i class="fa fa-times-circle"></i><span
                                class="sr-only"><?= $words->get('RemoveLanguage') ?></span></button>
                    </div>

                    <div class="col-10 col-md-4">
                        <input id="lang_<?= $ii ?>_id" type="hidden" name="memberslanguages[]"
                               value="<?= $vars['languages_selected'][$ii]->IdLanguage ?>">
                        <input id="lang_<?= $ii ?>_name" type="text" disabled value="<?= $words->getSilent('Lang_' . $vars['languages_selected'][$ii]->ShortCode) ?>"
                               title="<?= $words->getSilent('Lang_' . $vars['languages_selected'][$ii]->ShortCode) ?>" class="o-input">

                    </div>
                    <div class="col-10 offset-2 col-md-7 offset-md-0">
                        <select id="mll_<?= $ii ?>" class="mll select2 o-input" data-minimum-results-for-search="-1" name="memberslanguageslevel[]">
                            <?php
                            for ($jj = 0; $jj < count($vars['language_levels']); $jj++) {
                                $selected = $vars['language_levels'][$jj] == $vars['languages_selected'][$ii]->Level ? ' selected="selected"' : '';
                                ?>
                                <option value='<?= $vars['language_levels'][$jj] ?>'<?= $selected ?>><?= $words->getSilent("LanguageLevel_" . $vars['language_levels'][$jj]) ?></option>
                            <?php } ?>

                        </select>
                    </div>
                </div>
            <?php } ?>
            <!-- Language selection template -->
            <div class="row langsel mt-2 d-none">
                <div class="col-2 col-md-1">
                    <button class="btn btn-outline-danger p-1 px-2 remove_lang invisible"><i
                            class="fa fa-times-circle"></i><span
                            class="sr-only"><?= $words->get('RemoveLanguage') ?></span></button>
                </div>
                <div class="col-10 col-md-4">
                    <select class='lang_selector select2 form-control' name="memberslanguages[]">
                        <option selected="selected">-<?= $words->get("ChooseNewLanguage") ?>-</option>
                        <optgroup label="<?= $words->getSilent('SpokenLanguages') ?>">
                            <?php
                            for ($jj = 0; $jj < count($vars['languages_all_spoken']); $jj++) {
                                if (in_array($vars['languages_all_spoken'][$jj]->id, $lang_ids)) {
                                    continue;
                                }
                                ?>
                                <option value="<?= $vars['languages_all_spoken'][$jj]->id ?>"><?= $vars['languages_all_spoken'][$jj]->TranslatedName ?>
                                    (<?= $vars['languages_all_spoken'][$jj]->Name ?>)
                                </option>
                                <?php
                            }
                            ?>
                        </optgroup>
                        <optgroup label="<?= $words->getSilent('SignedLanguages') ?>">
                            <?php
                            for ($jj = 0; $jj < count($vars['languages_all_signed']); $jj++) {
                                if (in_array($vars['languages_all_signed'][$jj]->id, $lang_ids)) {
                                    continue;
                                }
                                ?>
                                <option value="<?= $vars['languages_all_signed'][$jj]->id ?>"><?= $vars['languages_all_signed'][$jj]->TranslatedName ?></option>
                                <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col-10 offset-2 col-md-7 offset-md-0">
                    <select class="mll select2 o-input" data-minimum-results-for-search="-1" name="memberslanguageslevel[]">
                        <?php
                        for ($jj = 0; $jj < count($vars['language_levels']); $jj++) {
                            ?>
                            <option value="<?= $vars['language_levels'][$jj] ?>"
                                <?php if ($vars['language_levels'][$jj] == 'Beginner') { ?>selected="selected"<?php } ?>
                            ><?= $words->get("LanguageLevel_" . $vars['language_levels'][$jj]) ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-10 offset-2 offset-md-1 mt-1">
                    <input type="button" id="langbutton" class="btn btn-outline-primary mt-1" name="addlang"
                           value="<?= $words->getSilent('AddLanguage') ?>"/>
                    <?= $words->flushBuffer() ?>
                </div>

                <div class="col-12 mt-3">
                    <input type="submit" class="btn btn-primary float-right m-2" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function initLanguageSelect2s()
    {
        $(".mll,.lang_selector").each(function (i, obj) {
            if (!$(obj).hasClass("select2-hidden-accessible"))
            {
                $(obj).select2({
                    theme: 'bootstrap4',
                    width: 'auto'
                });
            }
        });
    }

    function destroyLanguageSelect2s()
    {
        $(".mll,.lang_selector").each(function (i, obj) {
            if ($(obj).hasClass("select2-hidden-accessible"))
            {
                $(obj).select2('destroy');
                $(obj).removeAttr('data-select2-id');
            }
        });
    }

    function insertNewTemplate(e){
        e.preventDefault();
        destroyLanguageSelect2s();
        $("div.langsel:first").clone(true, true).insertAfter("div.langsel:last");
        $("div.langsel:last").removeClass('d-none');
        initLanguageSelect2s();
    }

    function removeLang(e)
    {
        e.preventDefault();
        let id = e.target.id;
        let languageId = $("#" + id + "_id").val();
        let languageName = $("#" + id + "_name").val();
        $('.lang_selector').append($('<option>', {value: languageId, text: languageName}));
        let row = "#" + id + "_row";
        $(row).remove();
    }

    $('#langbutton').click( insertNewTemplate );
    $('button.remove_lang').click( removeLang );
    destroyLanguageSelect2s();
    initLanguageSelect2s();
</script>
