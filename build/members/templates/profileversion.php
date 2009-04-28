<?php
$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$profile_language_name = $lang->Name;
$languages = $member->profile_languages;
$languages_spoken = $member->languages_spoken;
$languages_all = $member->languages_all;

$words = $this->getWords();

if (count($languages) > 1) {
?>      
		<p>
        <?=$words->get('ProfileVersion')?>:
        <span class="activelanguage"><?=$profile_language_name ?> 
        <? if ($this->myself) { ?>
        <a class="button" href="editmyprofile/<?=$profile_language_code?>/delete"><?=$words->get('delete')?></a>
        <? } ?>
        </span>
 		</p>
        <p class="floatbox">
		<span><?=$words->get('ProfileVersionIn')?>:</span>        
        
        <?php
		$ii = 0;
		$max = count($languages);
        foreach($languages as $language) {
            if ($language->ShortCode != $profile_language_code) {
			$space = ($ii != $max -1) ? ', ' : '';
        ?><a class="availablelanguages" href="<?=$urlstring?>/<?=$language->ShortCode ?>"><?=$language->Name ?><?=$space?></a><?php
			$ii++;
            }
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