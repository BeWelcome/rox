<?php
$User = new User;
$callbackId = $User->passwordProcess();
$avCallbackId = $User->avatarProcess();
$vars =& PPostHandler::getVars($callbackId);
$errors   = isset($vars['errors']) ? $vars['errors'] : array();
$messages = isset($vars['messages']) ? $vars['messages'] : array();
$settingsText = array();
$errorText = array();
$messageText = array();
$i18n = new MOD_i18n('apps/user/settings.php');
$settingsText = $i18n->getText('settingsText');
$errorText = $i18n->getText('errorText');
$messageText = $i18n->getText('messageText');

if (!$User = APP_User::login()) {
    echo '<span class="error">'.$errorText['not_logged_in'].'</span>';
    return;
}
?>
<h2><?=$settingsText['title']?></h2>
<?php
foreach ($messages as $msg) {
	if (array_key_exists($msg, $messageText))
        echo '<p class="notify">'.$messageText[$msg].'</p>';
}
foreach ($errors as $error) {
	if (array_key_exists($error, $errorText))
        echo '<p class="notify">'.$errorText[$error].'</p>';
}
?>
<form method="post" action="user/password" class="def-form" id="locationform">
<input type="hidden" name="<?=$callbackId?>" value="1"/>
<h3><?=$settingsText['title_password']?></h3>
<label><?= $settingsText["OldPassword"] ?></label><br />
<input type="password" name="OldPassword"><br />
<label><?= $settingsText["NewPassword"] ?></label><br />
<input type="password" name="NewPassword"><br />
<label><?= $settingsText["ConfirmPassword"] ?></label><br />
<input type="password" name="ConfirmPassword"><br /><br />
<input type="submit" id="ChangePassword" name="ChangePassword" value="<?= $settingsText["ChangePassword"] ?>"><br /><br />
</form>
<?php
PPostHandler::clearVars($callbackId);
?>