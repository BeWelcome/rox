<?php
$member = $this->member;
$layoutkit = $this->layoutkit;
$formkit = $layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('MembersController', 'editMyProfileCallback');

$page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);

$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$profile_language_name = $lang->Name;
$languages = $member->profile_languages;
$languages_spoken = $member->languages_spoken;
$languages_all = $member->languages_all;

$CanTranslate = false; // FIXME that seems to be incorrect

$vars = $this->editMyProfileFormPrepare($member);

?>
<div id="profile">
<?php
// That's to switch the profile language/version
$urlstring = 'editmyprofile';
require 'profileversion.php';
?>
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
<?=$words->flushBuffer()?>
<?php
// Check for errors and update status and display a message
if (isset($vars['errors']) and count($vars['errors']) > 0) {
      echo '<div class="error">'.$ww->EditmyprofileError.'</div>';
} else {
    if ($this->status == 'finish') {
          echo '<div class="note check">'.$ww->EditmyprofileFinish.'</div>';
    }
    $vars['errors'] = array();
}
?>
