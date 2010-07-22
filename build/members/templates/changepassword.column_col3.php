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
foreach ($messages as $msg) {
        echo '<p class="notify">'.$words->getFormatted($msg).'</p>';
}
foreach ($errors as $error) {
        echo '<p class="error">'.$words->getFormatted($error).'</p>';
}
?>

<p class="big"><?php echo $words->getFormatted('ChangePasswordIntro'); ?></p>
<form method="post" action="password" class="yform" id="locationform">
    <input type="hidden" name="<?=$callbackId?>" value="1"/>
    <div class="type-text">
        <label for="oldpassword"><?php echo $words->getFormatted('ChangePasswordOldPassword'); ?></label>
        <input type="password" id="oldpassword" name="OldPassword">
        <span class="small"><?php echo $words->getFormatted('ChangePasswordOldPasswordTip') ?></span>
    </div>
    <div class="type-text">
        <label for="newpassword"><?php echo $words->getFormatted('ChangePasswordNewPassword'); ?></label>
        <input type="password" id="newpassword" name="newpassword">
        <span class="small"><?php echo $words->getFormatted('ChangePasswordNewPasswordTip') ?></span>
    </div>
    <div class="type-text">
        <label for="confirmpassword"><?php echo $words->getFormatted('ChangePasswordConfirmPassword'); ?></label>
        <input type="password" id="confirmpassword" name="confirmpassword">
        <span class="small"><?php echo $words->getFormatted('ChangePasswordConfirmPasswordTip') ?></span>
    </div>
    <div class="type-button">
        <input type="submit" id="ChangePassword" name="ChangePassword" value="<?php echo $words->getFormatted('ChangePasswordSubmit'); ?>">
    </div>
</form>
<?php
PPostHandler::clearVars($callbackId);
?>
