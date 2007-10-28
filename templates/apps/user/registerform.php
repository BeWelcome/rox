<?php
// instantiate user model
$User = new User;
// retrieve the callback ID
$callbackId = $User->registerProcess();
// get the saved post vars
$vars =& PPostHandler::getVars($callbackId);
// get current request
$request = PRequest::get()->request;

if (!isset($vars['errors']) || !is_array($vars['errors']))
    $vars['errors'] = array();

// text
$regText = array();
$errors  = array();
$i18n = new MOD_i18n('apps/user/register.php');
$regText = $i18n->getText('regText');
$error = $i18n->getText('errors');

// don't show the register form, if user is logged in. Redirect to "my" page instead.
if ($User = APP_User::login()) {
    $url = PVars::getObj('env')->baseuri.'user/'.$User->getHandle();
    header('Location: '.$url);
    PPHP::PExit();
}

if (!isset($request[2]) || $request[2] != 'finish') {
/*
 * REGISTER FORM TEMPLATE
 */
?>
<h2><?php echo $regText['title']; ?></h2>
<form method="post" action="user/register" class="def-form" id="user-register-form">
<?php
if (in_array('inserror', $vars['errors'])) {
    echo '<p class="error">'.$errors['inserror'].'</p>';
}
?>
    <div class="row">
        <label for="register-u"><?php echo $regText['label_username']; ?></label><br/>
        <input type="text" id="register-u" name="u" <?php 
// the username may be set
echo isset($vars['u']) ? 'value="'.htmlentities($vars['u'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <div id="bregister-u" class="statbtn"><?php
if (in_array('username', $vars['errors'])) {
    echo '<span class="error">'.$errors['username'].'</span>';
}
if (in_array('uinuse', $vars['errors'])) {
    echo '<span class="error">'.$errors['uinuse'].'</span>';
}
?></div>
        <p class="desc"><?php echo $regText['subline_username']; ?></p>
    </div>
    <div class="row">
        <label for="register-e"><?php echo $regText['label_email']; ?></label><br/>
        <input type="text" id="register-e" name="e" <?php 
// the email may be set
echo isset($vars['e']) ? 'value="'.htmlentities($vars['e'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <div id="bregister-e" class="statbtn"><?php
if (in_array('email', $vars['errors'])) {
    echo '<span class="error">'.$errors['email'].'</span>';
}
if (in_array('einuse', $vars['errors'])) {
    echo '<span class="error">'.$errors['einuse'].'</span>';
}
?></div>
        <p class="desc"><?php echo $regText['subline_email']; ?></p>
    </div>
    <div class="row">
        <label for="register-p"><?php echo $regText['label_password']; ?></label><br/>
        <input type="password" id="register-p" name="p" <?php
echo isset($vars['p']) ? 'value="'.$vars['p'].'" ' : '';
?>/>
        <div id="bregister-p" class="statbtn"><?php
if (in_array('pw', $vars['errors'])) {
    echo '<span class="error">'.$errors['pw'].'</span>';
}
if (in_array('pwmismatch', $vars['errors'])) {
    echo '<span class="error">'.$errors['pwmismatch'].'</span>';
}
?></div>
        <p class="desc"><?php echo $regText['subline_password']; ?></p>
    </div>
    <div class="row">
        <label for="register-pc"><?php echo $regText['label_passwordc']; ?></label><br/>
        <input type="password" id="register-pc" name="pc" <?php
echo isset($vars['pc']) ? 'value="'.$vars['pc'].'" ' : '';
?>/>
        <div id="bregister-pc" class="statbtn"></div>
        <p class="desc"><?php echo $regText['subline_passwordc']; ?></p>
    </div>
    <p>
        <input type="submit" value="<?php echo $regText['submit']; ?>" class="submit"/>
        <input type="hidden" name="<?php
// IMPORTANT: callback ID for post data 
echo $callbackId; ?>" value="1"/>
    </p>
</form>
<script type="text/javascript">//<!--
Register.initialize('user-register-form');
//-->
</script>
<?php
} else {
/*
 * FINISHED
 */
?>
<h2><?php echo $regText['finish_title']; ?></h2>
<p><?php echo $regText['finish_text']; ?></p>
<?php
}
?>