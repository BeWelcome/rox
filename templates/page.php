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
echo '<?xml version="1.0" encoding="utf-8"?>'; 

####
# bwlink from layouttools.php doesn't work
#    require ("../htdocs/bw/layout/layouttools.php")
# so this is the temporary dirty hack for favicon
function bwlink() {
	return "bw/favicon.ico";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo PVars::get()->lang; ?>" lang="<?php echo PVars::get()->lang; ?>" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
  <title><?php echo $Page->title; ?></title>
  <base id="baseuri" href="<?php echo $Env->baseuri; ?>"/>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="keywords" content="Travel planning trip information discussion community Reisen, Information, Kultur, St&auml;dte, Landschaften, Land, Reiseziel, Reiseland, Traumland, Travel, Urlaub"/> 
  <meta name="description" content="Travel Community diary"/>
  <link rel="shortcut icon" href="<?=bwlink("favicon.ico")?>" />
  <link rel="stylesheet" href="styles/YAML/main.css" type="text/css"/>
  <link rel="stylesheet" href="styles/YAML/bw_yaml.css" type="text/css"/>
  <?php echo $Page->addStyles; ?>
  <!--[if lte IE 7]>
  <link rel="stylesheet" href="styles/YAML/patches/iehacks_3col_vlines.css" type="text/css" />
  <![endif]-->

  <script type="text/javascript" src="script/main.js"></script>
</head>
	
<body>

<!-- #page_margins: Obsolete for now. If we decide to use a fixed width or want a frame around the page, we will need them aswell -->
<div id="page_margins">
<!-- #page: Used to hold the floats -->
<div id="page" class="hold_floats">

<div id="header">
  <!-- <div id="topnav"> //-->
    <div id="navigation-functions">
      <ul>
        <li class="icon_online"><img src="styles/YAML/images/icon_grey_online.png"/> <a href="bw/whoisonline.php">Online Members</a></li>
        <li><img src="styles/YAML/images/icon_grey_mail.png"/><a href="bw/mymessages.php">My Messages</a></li>
        <li><img src="styles/YAML/images/icon_grey_pref.png"/><a href="bw/mypreferences.php">My Preferences</a></li>
        <li><img src="styles/YAML/images/icon_grey_logout.png"/><a href="bw/main.php?action=logout" id="header-logout-link">Logout</a></li>
      </ul>
    </div> <!-- navigation-functions -->
  <!-- </div> <!-- topnav --> -->
  
	<a href='/'><img id="logo" class="float_left overflow" src="styles/YAML/images/logo.gif" width="250" height="48" alt="Be Welcome"/></a>
  
</div> <!-- header -->

	<?php
	$Rox->topMenu($Page->currentTab);
	?>

<!-- #main: content begins here -->
<div id="main">

	<!-- #teaser: the orange bar shows title and elements that summarize the content of the current page -->
	<div id="teaser_bg">	
	<div id="teaser" class="clearfix">
		<?php echo $Page->teaserBar; ?>
		<!--<p>This could be a short description, either to the title's right or below.</p>-->

		<!-- #nav: - end -->
	</div>
			<!-- #nav: sub navigation -->

	  
	</div>
<hr class="hr_divide" />
	<!-- #teaser: end -->
	


<!-- #col1: first floating column of content-area  -->
    <div id="col1">
      <div id="col1_content" class="clearfix">
<?php echo $Page->newBar; ?>

	</div>
    </div>
<!-- #col1: - end -->

<!-- #col2: second floating column of content-area -->
    <div id="col2">
      <div id="col2_content" class="clearfix">
		
		<?php echo $Page->rContent; ?>
		
		<!-- THIS IS JUST FOR TESTING THE TB and SHOULD BE REMOVED IN ALPHA -->
		<h3>TB Test Links</h3>
          <ul class="linklist">
			<li><a href="rox">Index page</a></li>
			<li><a href="trip">Trips</a></li>
			<li><a href="blog">Blogs</a></li>
			<li><a href="gallery">Gallery</a></li>
			<li><a href="forums">Forums</a></li>
			<li><a href="wiki">Wiki</a></li>
		  </ul>		
		<!-- STOP @author: lupochen -->
		
         </p>

      </div>
    </div>
<!-- #col2: - end -->

<!-- #col3: static column of content-area -->
    <div id="col3">
      <div id="col3_content" class="clearfix" >

			<table>
			<tr><td class="info"><?php echo $Page->content; ?>
			</td>
			</tr>
			</table>
		<!-- page content -->
	  
      </div>
      <!-- IE Column Clearing -->
	  <div id="ie_clearing">&nbsp;</div>
      <!-- Ende: IE Column Clearing -->
    </div>
<!-- #col3: - Ende -->

</div>
<!-- #main: - Ende -->

<?php
	$Rox->footer();
?>
</div>
</div>

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
    </body>
</html>
