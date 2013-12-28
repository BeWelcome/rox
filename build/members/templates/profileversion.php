<?php
// Utility function to sort the languages
function cmpProfileLang($a, $b)
{
    if ($a == $b) {
        return 0;
    }
    return (strtolower($a->TranslatedName) < strToLower($b->TranslatedName)) ? -1 : 1;
}

function sortLanguages($languages)
{
    $words = new MOD_words;
    $langarr = array();
    foreach($languages as $language) {
        $lang = $language;
        $lang->TranslatedName = $words->getSilent($language->WordCode);
        $langarr[] = $lang;
    }
    usort($langarr, "cmpProfileLang");
    return $langarr;
}

$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$profile_language_name = $lang->Name;
$languages = $member->profile_languages;
$languages_spoken = $member->languages_spoken;
$all_spoken_languages = sortLanguages($member->get_all_translatable_languages());

$words = $this->getWords();
$myself = $this->myself;
if (count($languages) > 1 || $myself) {
?>
<div class="floatbox">
    <div class="profile_translations float_right">
        <strong><?=$words->get('ProfileTranslations')?></strong>
        <p class="floatbox"><?php
            $ii = 0;
            $max = count($languages);
            foreach($languages as $language) {
                if (($language->ShortCode == $profile_language_code) && ($max > 1)) {
                ?>
                    <span class="activelanguage"><?=$profile_language_name ?><? if ($this->myself) { ?><a href="editmyprofile/<?=$profile_language_code?>/delete"> <img src="images/icons/cancel.png" title="<?=$words->getSilent('delete')?>" alt="<?=$words->getSilent('delete')?>" /></a> <? } ?></span><?
                    $activelang_set = true;
                } else {
                    
                ?><a class="availablelanguages" href="<?=$urlstring?>/<?=$language->ShortCode ?>"><?=$language->Name ?></a> <?
                $ii++;
                }
            } 
            if (!isset($activelang_set)) echo '<br/><span class="activelanguage">'.$words->get('ProfileNoMatchingLanguage').'</span>';
            ?><?php echo $words->flushBuffer(); ?></p>
<?php if ($myself) { ?>
<select class="floatbox" id="add_language">
    <option>- <?=$wwsilent->AddLanguage?> -</option>
    <optgroup label="<?=$wwsilent->YourLanguages?>">
      <?php
      foreach ($languages_spoken as $lang) {
      if (!in_array($lang->ShortCode,$languages))
      echo '<option value="'.$lang->ShortCode.'">' . $lang->Name . '</option>';
      } ?>
    </optgroup>
    <optgroup label="<?=$wwsilent->AllLanguages?>">
      <?php
      foreach ($all_spoken_languages as $lang) {
      if (!in_array($lang->ShortCode,$languages))
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
    function linkDropDown(event){
        var element = Event.element(event);
        var index = element.selectedIndex;
        var lang = element.options[index].value;
        window.location.href = http_baseuri + 'editmyprofile/' + lang;
    }

    document.observe("dom:loaded", function() {
        var element = $('add_language');
        if (element !== null) {
            element.observe('change', linkDropDown);
        }
    });
//-->
</script>
