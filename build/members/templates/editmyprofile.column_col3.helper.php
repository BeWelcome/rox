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

<?=$words->flushBuffer()?>
<?php
// Check for errors and update status and display a message
if (isset($vars['errors']) and count($vars['errors']) > 0) {
    echo '<div class="error">'.$ww->EditmyprofileError;
    echo "<ul>";
    foreach ($vars['errors'] as $error)
    {
        echo "<li>" . $words->get($error) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
} else {
    if ($this->status == 'finish') {
          echo '<div class="success">'.$words->getFormatted("EditmyprofileFinish", $profile_language_name, 
                '<a href="members/'. $member->Username . '/' . $profile_language_code . '">', '</a>') . '</div>';
    }
    $vars['errors'] = array();
}
?>
