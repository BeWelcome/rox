<?php
// instantiate user model
$User = new User;
// get current request
$request = PRequest::get()->request;

$loginText = array();
$i18n = new MOD_i18n('apps/user/login.php');
$loginText = $i18n->getText('loginText');

/*
 * LOGIN FORM
 */

if (!APP_User::loggedIn()) {
    // retrieve the callback ID
    $callbackId = $User->loginProcess();
    // get the saved post vars
    $vars =& PPostHandler::getVars($callbackId);
    if (isset($vars['errors']) && is_array($vars['errors']) && in_array('not_logged_in', $vars['errors'])) {
?>
 <p class="error"><?php echo $loginText['not_logged_in']; ?></p>
<?php
    }
?>
<!-- <h2><a href="http://www.bewelcome.org/login.php"><?php echo $loginText['title']; ?></a></h2> !-->
<!-- START OLD LOGIN FORM -->
<div class="floatbox">
<h3><?php echo $loginText['title']; ?></h3>
<form method="post" action="<?php
// action is current request 
echo implode('/', $request); 
?>">
    <p>
        <label for="login-u"><?php echo $loginText['label_username']; ?></label>
        <input type="text" id="login-u" name="u" <?php 
// the username may be set
echo isset($vars['u']) ? 'value="'.htmlentities($vars['u'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
    </p>
    <p>
        <label for="login-p"><?php echo $loginText['label_password']; ?></label>
        <input type="password" id="login-p" name="p" />
    </p>
    <p>
        <input type="submit" value="<?php echo $loginText['submit']; ?>" class="submit"/>
        <input type="hidden" name="<?php
// IMPORTANT: callback ID for post data 
echo $callbackId; ?>" value="1"/>
    </p>
    <p>
        <a href="bw/signup.php"><?php echo $loginText['link_register']; ?></a>
    </p>
</form>
</div>
<!-- END -->
<?php
// and remove unused vars
PPostHandler::clearVars($callbackId);
} else {
/*
 * STATUS AND LOGOUT FORM
 */
$c = $User->logoutProcess();
$currUser = APP_User::get();
$navText = $i18n->getText('navText');
$countrycode = APP_User::countryCode($currUser->getHandle());
// FIXME: must be hidden from public
$BWImageURL=file_get_contents("http://www.bewelcome.org/myphotos.php?PictForMember=".$currUser->getHandle());
?>
<div class="floatbox">
<h2>
    <a href="user/<?php echo $currUser->getHandle(); ?>">
        <img src="http://<?php echo $BWImageURL; ?>" alt="<?php echo $currUser->getHandle(); ?>" class="l" height="100px" style="margin:0 10px 0 0"/> <?=$currUser->getHandle()?></a>
<?php
if ($countrycode) {
?>        
        <a href="country/<?php echo $countrycode; ?>"><img src="images/icons/flags/<?php echo strtolower($countrycode); ?>.png" alt="" /></a>
<?php
}
?>
    
</h2>

<form method="post" action="<?php
// action is current request 
echo implode('/', $request); 
?>" id="user-leftnav">
    <ul>
        
        <li><a href="user/settings"><?php echo $navText['settings']; ?></a></li>
       
    </ul>
<p>
    <input type="submit" value="<?php echo $loginText['logout']; ?>"/>
    <input type="hidden" name="<?php echo $c; ?>" value="1"/>
</p>
</form>
</div>
<?php 
}
?>
