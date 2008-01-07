<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
// environment
$Env = PVars::getObj('env');
// default page elements
$Page = PVars::getObj('page');
$Rox = new RoxController;
$User = new UserController;
$Cal = new CalController;
$words = new MOD_words();
MOD_user::updateSessionOnlineCounter();    // update session environment
/*echo '<?xml version="1.0" encoding="utf-8"?>';*/ 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo PVars::get()->lang; ?>" lang="<?php echo PVars::get()->lang; ?>" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <title><?php echo $Page->title; ?></title>
    <base id="baseuri" href="<?php echo $Env->baseuri; ?>" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="verify-v1" content="NzxSlKbYK+CRnCfULeWj0RaPCGNIuPqq10oUpGAEyWw=" />
<?php
if (empty($meta_description)) {
    $meta_description = $words->getBuffered("default_meta_description");
}
echo "    <meta name=\"description\" content=\"",$meta_description,"\" />\n";
if (empty($meta_keyword)) {
    $meta_keyword = $words->getBuffered("default_meta_keyword");
}
echo "    <meta name=\"keywords\" content=\"",$meta_keyword,"\" />\n";
?>
    <link rel="shortcut icon" href="bw/favicon.ico" />
    <link rel="stylesheet" href="styles/YAML/main.css" type="text/css" />
    <link rel="stylesheet" href="styles/YAML/bw_yaml.css" type="text/css" />
    <?php echo $Page->addStyles; ?>
    <!--[if lte IE 7]>
    <link rel="stylesheet" href="styles/YAML/patches/iehacks_3col_vlines.css" type="text/css" />
    <![endif]-->

    <script type="text/javascript" src="script/main.js"></script>
    <script type="text/javascript" src="script/wordclick.js"></script>
    <link rel="stylesheet" href="styles/wordclick.css">
    <!--[if lt IE 7]>
    <script defer type="text/javascript" src="script/pngfix.js"></script><![endif]-->
</head>
	
<body>
<!-- #page_margins: Obsolete for now. If we decide to use a fixed width or want a frame around the page, we will need them aswell -->
<div id="page_margins">
<!-- #page: Used to hold the floats -->
<div id="page" class="hold_floats">

<div id="header">
  <div id="topnav">
    <ul>
      <li><img src="styles/YAML/images/icon_grey_online.png" alt="onlinemembers" /> <a href="bw/whoisonline.php"><?php echo $words->getBuffered('NbMembersOnline', $_SESSION['WhoIsOnlineCount']); ?></a><?php echo $words->flushBuffer(); ?></li>
<?php if (APP_User::isBWLoggedIn()) { ?>
      <li><img src="styles/YAML/images/icon_grey_mail.png" alt="mymessages"/><a href="bw/mymessages.php"><?php echo $words->getBuffered('Mymessages'); ?></a><?php echo $words->flushBuffer(); ?></li>
      <li><img src="styles/YAML/images/icon_grey_pref.png" alt="mypreferences"/><a href="bw/mypreferences.php"><?php echo $words->getBuffered('MyPreferences'); ?></a><?php echo $words->flushBuffer(); ?></li>
      <li><img src="styles/YAML/images/icon_grey_logout.png" alt="logout" /><a href="user/logout" id="header-logout-link"><?php echo $words->getBuffered('Logout'); ?></a><?php echo $words->flushBuffer(); ?></li>
<?php } else { ?>
      <li><img src="styles/YAML/images/icon_grey_logout.png" alt="login" /><a href="index.php" id="header-login-link"><?php echo $words->getBuffered('Login'); ?></a><?php echo $words->flushBuffer(); ?></li>
      <li><a href="bw/signup.php"><?php echo $words->getBuffered('Signup'); ?></a><?php echo $words->flushBuffer(); ?></li>
<?php } ?>
    </ul>
  </div> <!-- topnav -->
  <a href="start"><img id="logo" class="float_left overflow" src="styles/YAML/images/logo.gif" width="250" height="48" alt="Be Welcome" /></a>
</div> <!-- header -->

  <?php
  $Rox->topMenu($Page->currentTab);
  ?>

<!-- #main: content begins here -->
<div id="main">

<!-- #teaser: the orange bar shows title and elements that summarize the content of the current page -->
  <div id="teaser_bg">
<?php echo $Page->teaserBar; ?>
    <div id="teaser_shadow">
<?php if (!$Page->subMenu) {?>
      <img src="styles/YAML/images/spacer.gif" width="95%" height="5px" alt="spacer" />
<?php }?>
<?php echo $Page->subMenu; ?>
    </div> <!-- tease_shadow -->
  </div> <!-- teaser_bg -->
  
<!-- #col1: first floating column of content-area  -->
  <div id="col1">
    <div id="col1_content" class="clearfix">
      <?php echo $Page->newBar; ?>
      <br /><br /><?php // TODO: Replace HTML breaks by layout directive ?>
      <?php echo $Rox->volunteerBar(); ?>
    </div> <!-- col1_content -->
  </div> <!-- col1 -->


<!-- #col2: second floating column of content-area -->
  <div id="col2">
    <div id="col2_content" class="clearfix">
      <?php echo $Page->rContent; ?>
    </div> <!-- col2_content -->
  </div> <!-- col2 -->

  
<!-- #col3: static column of content-area -->
  <div id="col3">
    <div id="col3_content" class="clearfix" >
      <table class="full">
        <tr>
          <td class="info">
            <?php echo $Page->content; ?>
          </td>
        </tr>
      </table>
    </div> <!-- col3_content -->
      <!-- IE Column Clearing -->
    <div id="ie_clearing">&nbsp;</div>
      <!-- Ende: IE Column Clearing -->
  </div> <!-- col3 -->

</div> <!-- main -->

<?php
	$Rox->footer();
?>
</div> <!-- page -->
</div> <!-- page_margins-->

<?php
    if (PVars::get()->debug) {
?>
<!-- 
<?php echo 'Build: '.PVars::get()->build; ?> 
<?php echo 'Templates: '.basename(TEMPLATE_DIR); ?> 
-->
<?php
    }
?>
<?php
    if($words->translationLinksEnabled()) {
        echo $words->flushBuffer();
    }
?>
</body>
</html>

