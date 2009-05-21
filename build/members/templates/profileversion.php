<?php
$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$profile_language_name = $lang->Name;
$languages = $member->profile_languages;
$languages_spoken = $member->languages_spoken;
$languages_all = $member->languages_all;

$words = $this->getWords();
$myself = $this->myself;
if (count($languages) > 1 || $myself) {
?>      
    <div class="profile_translations box">
    	<h3><?=$words->get('ProfileTranslations')?>:</h3>
        <p class="floatbox">  
        <?php
		$ii = 0;
		$max = count($languages);
        foreach($languages as $language) {
            if ($language->ShortCode == $profile_language_code) {
                
?><span class="activelanguage"><?=$profile_language_name ?><? if ($this->myself) { ?><a class="button" href="editmyprofile/<?=$profile_language_code?>/delete"><?=$words->get('delete')?></a><? } ?></span><?

            } else {
                
        ?><a class="availablelanguages" href="<?=$urlstring?>/<?=$language->ShortCode ?>"><?=$language->Name ?></a><?

			$ii++;
            }
            echo '&nbsp;&nbsp;';
        } ?>
		</p>
<?php } ?>
<?php if ($myself) { ?>
    &nbsp;  &nbsp;  &nbsp;  &nbsp;
<select id="add_language">
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
      foreach ($languages_all as $lang) {
      if (!in_array($lang->ShortCode,$languages))
      echo '<option value="'.$lang->ShortCode.'">' . $lang->Name . '</option>';
      } ?>
    </optgroup>
</select>
<?php } ?>
</div> <!-- profile_translations -->
