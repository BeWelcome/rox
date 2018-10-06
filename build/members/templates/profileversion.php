<?php
// Utility function to sort the languages
function cmpProfileLang($a, $b)
{
    if ($a == $b) {
        return 0;
    }
    return (strtolower($a->TranslatedName) < strToLower($b->TranslatedName)) ? -1 : 1;
}

function indexedLanguages($languages) {
    $langarr = array();
    foreach($languages as $language) {
        $lang = $language;
        if (isset($lang->id)) {
            $langarr[$lang->id] = $lang;
        } else {
            $langarr[$lang->IdLanguage] = $lang;
        }
    }
    return $langarr;
}

function sortLanguages($languages)
{
    $words = new MOD_words;
    $langarr = indexedLanguages($languages);
    foreach($langarr as $language) {
        $language->TranslatedName = $words->getSilent($language->WordCode);
    }
    uasort($langarr, "cmpProfileLang");
    return $langarr;
}

$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$profile_language_name = $lang->Name;
$languages = indexedLanguages($member->profile_languages);
$languages_spoken = indexedLanguages($member->languages_spoken);
$all_written_languages = sortLanguages($member->get_all_translatable_languages());
$words = $this->getWords();
$myself = $this->myself;
if (count($languages) > 1 || $myself) {
?>
<div>
    <div class="profile_translations float_right">
        <strong><?=$words->get('ProfileTranslations')?></strong>
        <div class="btn-group btn-group-sm"><?php
            $ii = 0;
            $activelang_set = false;
            $max = count($languages);
            foreach($languages as $language) {
                if (($language->ShortCode == $profile_language_code)) {
                ?>
                    <button class="btn btn-primary"><?=$profile_language_name ?><? if ($this->myself && $max > 1) { ?><a href="editmyprofile/<?=$profile_language_code?>/delete" title="<?= $words->getSilent('delete')?>"> <i class="fa fa-close"></i></a> <? } ?></button><?
                    $activelang_set = true;
                } else {
                    
                    ?><button class="btn btn-outline-primary bg-white"><a href="<?=$urlstring?>/<?=$language->ShortCode ?>"><?=$language->Name ?></a></button> <?
                $ii++;
                }
            }
            ?><?php echo $words->flushBuffer(); ?></div>
<?php if ($myself) { ?>
<select class="select2 small" id="add_language">
    <option>- <?=$wwsilent->AddLanguage?> -</option>
      <?php
      $ownLanguages = "";
      foreach ($languages_spoken as $lang) {
          if ((!array_key_exists($lang->IdLanguage,$languages)) && (array_key_exists($lang->IdLanguage,$all_written_languages))) {
           $ownLanguages .= '<option value="'.$lang->ShortCode.'">' . $lang->Name . '</option>';
          }
      }

      if (!empty($ownLanguages)) { ?>
    <optgroup label="<?=$wwsilent->YourLanguages?>">
    <?php echo $ownLanguages; ?>
    </optgroup>
    <?php } ?>
    <optgroup label="<?=$wwsilent->AllLanguages?>">
      <?php
      foreach ($all_written_languages as $lang) {
      if (!in_array($lang->id,$languages))
      echo '<option value="'.$lang->ShortCode.'">' . $lang->TranslatedName . ' (' . $lang->Name . ')</option>';
      } ?>
    </optgroup>
</select>
    <?php } ?>
    </div>
<?=$words->flushBuffer()?>
<?php }
if (count($languages) > 1 || $myself) {
    echo "</div> <!-- profile_translations -->";
} ?>

<script type="text/javascript">//<!--
    $('#add_language').change(function () {
            var lang = $("#add_language option:selected").val();
            window.location.href = http_baseuri + 'editmyprofile/' + lang;
        }
    )
    //-->
</script>
