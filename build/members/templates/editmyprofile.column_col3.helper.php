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
$words = $this->getWords();

$vars = $this->editMyProfileFormPrepare($member);

if (!$memory = $formkit->getMemFromRedirect()) {
    // no memory
    // echo 'no memory';
} else {
    // from previous form
    if ($memory->post) {
        foreach ($memory->post as $key => $value) {
            $vars[$key] = $value;
        }
        // update $vars for messengers
        if(isset($vars['messengers'])) {
            $ii = 0;
            foreach($vars['messengers'] as $me) {
                $val = 'chat_' . $me['network_raw'];
                $vars['messengers'][$ii++]['address'] = $vars[$val];
            }
        }
        // update $vars for $languages
        if(!isset($vars['languages_selected'])) {
            $vars['languages_selected'] = array();
        }
        $ii = 0;
        $ii2 = 0;
        $lang_used = array();
        foreach($vars['memberslanguages'] as $lang) {
            if (ctype_digit($lang) and !in_array($lang,$lang_used)) { // check $lang is numeric, hence a legal IdLanguage
                $vars['languages_selected'][$ii]->IdLanguage = $lang;
                $vars['languages_selected'][$ii]->Level = $vars['memberslanguageslevel'][$ii2];
                array_push($lang_used, $vars['languages_selected'][$ii]->IdLanguage);
                $ii++;
            }
            $ii2++;
        }
    }
    // problems from previous form
    if (is_array($memory->problems)) {
        require_once 'edit_warning.php';
    }
}
// var_dump($vars);

// That's to switch the profile language/version
$urlstring = 'editmyprofile';
require 'profileversion.php';
?>
        &nbsp;  &nbsp;  &nbsp;  &nbsp;
    <select id="add_language">
        <option>- Add language -</option>
        <optgroup label="Your languages">
          <?php
          foreach ($languages_spoken as $lang) {
          if (!in_array($lang->ShortCode,$languages))
          echo '<option value="'.$lang->ShortCode.'">' . $lang->Name . '</option>';
          } ?>
        </optgroup>
        <optgroup label="All languages">
          <?php
          foreach ($languages_all as $lang) {
          if (!in_array($lang->ShortCode,$languages))
          echo '<option value="'.$lang->ShortCode.'">' . $lang->Name . '</option>';
          } ?>
        </optgroup>
    </select>

<hr />
<?php
// Check for errors and update status and display a message
if (isset($vars['errors']) and count($vars['errors']) > 0) {
      echo '<div class="error">'.$words->get('EditmyprofileError').'</div>';
} else {
    if ($this->status == 'finish') {
          echo '<div class="note check">'.$words->get('EditmyprofileFinish').'</div>';
    }
    $vars['errors'] = array();
}
?>
<br />