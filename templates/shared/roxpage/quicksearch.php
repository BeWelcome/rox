
<form action="searchmembers" method="get" id="form-quicksearch">

<?
/*
 * Disabled until we switch to new topmenu layout
 */

/*
  <?php if ($logged_in) { ?>
  <a href="bw/mypreferences.php"><?php echo $words->getBuffered('MyPreferences'); ?></a><?php echo $words->flushBuffer(); ?>
  <a href="user/logout/<?php echo implode('/', PRequest::get()->request) ?>" id="header-logout-link"><?php echo $words->getBuffered('Logout'); ?></a><?php echo $words->flushBuffer(); ?>
  <?php } else { ?>
  <a href="<?php echo $login_url ?>#login-widget" id="header-login-link"><?php echo $words->getBuffered('Login'); ?></a><?php echo $words->flushBuffer(); ?>
  <a href="signup"><?php echo $words->getBuffered('Signup'); ?></a><?php echo $words->flushBuffer(); ?>
<?php } ?>
*/
?>
    
    
    <input type="text" name="vars" size="15" maxlength="30" id="text-field" value="<?echo htmlentities($words->getSilent('TopMenuSearchtext'));?>..." onclick="this.value='';" onblur="this.value='<?echo htmlentities($words->getSilent('TopMenuSearchtext'));?>...';"/>
    <input type="hidden" name="quicksearch_callbackId" value="1"/>
    <input type="image" src="images/icons/icon_searchtop.gif" id="submit-button" />
    <?=$words->flushBuffer()?>
</form>
