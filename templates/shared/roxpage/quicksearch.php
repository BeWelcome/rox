
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
    
    
    <input type="text" name="vars" size="15" maxlength="30" id="text-field" value="Search..." onClick="this.value=''"; onBlur="this.value='Search...';"/>
    <input type="hidden" name="quicksearch_callbackId" value="1"/>
    <input type="image" src="styles/css/minimal/images/icon_go.gif" id="submit-button" />
  
</form>
