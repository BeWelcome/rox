<ul>
  <li><img src="styles/YAML/images/icon_grey_online.png" alt="onlinemembers" /> <a href="online" id="IdLoggedMembers"><?php echo $words->getBuffered('NbMembersOnline', $who_is_online_count); ?></a><?php echo $words->flushBuffer(); ?></li>
  <?php if ($logged_in) { ?>
  <li><img src="styles/YAML/images/icon_grey_mail.png" alt="mymessages"/><a href="bw/mymessages.php"><?php echo $words->getBuffered('Mymessages'); ?></a><?php echo $words->flushBuffer(); ?></li>
  <li><img src="styles/YAML/images/icon_grey_pref.png" alt="mypreferences"/><a href="bw/mypreferences.php"><?php echo $words->getBuffered('MyPreferences'); ?></a><?php echo $words->flushBuffer(); ?></li>
  <li><img src="styles/YAML/images/icon_grey_logout.png" alt="logout" /><a href="user/logout/<?php echo implode('/', PRequest::get()->request) ?>" id="header-logout-link"><?php echo $words->getBuffered('Logout'); ?></a><?php echo $words->flushBuffer(); ?></li>
  <?php } else { ?>
  <li><img src="styles/YAML/images/icon_grey_logout.png" alt="login" /><a href="<?php echo $login_url ?>#login-widget" id="header-login-link"><?php echo $words->getBuffered('Login'); ?></a><?php echo $words->flushBuffer(); ?></li>
  <li><a href="bw/signup.php"><?php echo $words->getBuffered('Signup'); ?></a><?php echo $words->flushBuffer(); ?></li>
<?php } ?>
</ul>

