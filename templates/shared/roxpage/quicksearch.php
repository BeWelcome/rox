
<form action="searchmembers/quicksearch" method="post" id="form-quicksearch">

  <?php if ($logged_in) { ?>
  <a href="bw/mypreferences.php"><?php echo $words->getBuffered('Preferences'); ?></a><?php echo $words->flushBuffer(); ?>
  <a href="user/logout/<?php echo implode('/', PRequest::get()->request) ?>" id="header-logout-link"><?php echo $words->getBuffered('Logout'); ?></a><?php echo $words->flushBuffer(); ?>
  <?php } else { ?>
  <a href="<?php echo $login_url ?>#login-widget" id="header-login-link"><?php echo $words->getBuffered('Login'); ?></a><?php echo $words->flushBuffer(); ?>
  <a href="bw/signup.php"><?php echo $words->getBuffered('Signup'); ?></a><?php echo $words->flushBuffer(); ?>
<?php } ?>

  <input type="text" name="searchtext" size="15" maxlength="30" id="text-field" value="Search..." onfocus="this.value='';"/>
  <input type="hidden" name="quicksearch_callbackId" value="1"/>
  <input type="image" src="styles/YAML/images/icon_go.gif" id="submit-button" />
</form>
