<?php
$words = new MOD_words();
$User = new User;
$callbackId = $User->passwordProcess();
$avCallbackId = $User->avatarProcess();
$vars =& PPostHandler::getVars($callbackId);
$errors   = isset($vars['errors']) ? $vars['errors'] : array();
$messages = isset($vars['messages']) ? $vars['messages'] : array();

if (!$User = APP_User::login()) {
    echo '<span class="error">'.$words->getFormatted('ChangePasswordNotLoggedIn').'</span>';
    return;
}
?>
<h2><?php echo $words->getFormatted('ChangePasswordHeading'); ?></h2>
<?php
foreach ($messages as $msg) {
        echo '<p class="notify">'.$words->getFormatted($msg).'</p>';
}
foreach ($errors as $error) {
        echo '<p class="error">'.$words->getFormatted($error).'</p>';
}
?>
<form method="post" action="user/password" class="def-form" id="locationform">
<input type="hidden" name="<?=$callbackId?>" value="1"/>
<h3><?php echo $words->getFormatted('ChangePasswordTitle'); ?></h3>
<label><?php echo $words->getFormatted('ChangePasswordOldPassword'); ?></label><br />
<input type="password" name="OldPassword">&nbsp;&nbsp;<span class="notify"><?php echo $words->getFormatted('ChangePasswordOldPasswordTip') ?></span><br />
<label><?php echo $words->getFormatted('ChangePasswordNewPassword'); ?></label><br />
<input type="password" name="NewPassword">&nbsp;&nbsp;<span class="notify"><?php echo $words->getFormatted('ChangePasswordNewPasswordTip') ?></span><br />
<label><?php echo $words->getFormatted('ChangePasswordConfirmPassword'); ?></label><br />
<input type="password" name="ConfirmPassword">&nbsp;&nbsp;<span class="notify"><?php echo $words->getFormatted('ChangePasswordConfirmPasswordTip') ?></span><br /><br />
<input type="submit" id="ChangePassword" name="ChangePassword" value="<?php echo $words->getFormatted('ChangePasswordSubmit'); ?>"><br /><br />
</form>
<?php
PPostHandler::clearVars($callbackId);
?>