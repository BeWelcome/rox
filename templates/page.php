<?php
// environment
$Env = PVars::getObj('env');
// default page elements
$Page = PVars::getObj('page');
// HC widgets
$HC = new HcifController;
$MyTravelbook = new MytravelbookController;
$User = new UserController;
$Cal = new CalController;
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=PVars::get()->lang?>" lang="<?=PVars::get()->lang?>" xmlns:v="urn:schemas-microsoft-com:vml">
    <head>
        <title><?php echo $Page->title; ?></title>
        <base id="baseuri" href="<?php echo $Env->baseuri; ?>"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="Travel planning trip information discussion community Reisen, Information, Kultur, St&auml;dte, Landschaften, Land, Reiseziel, Reiseland, Traumland, Travel, Urlaub"/> 
        <meta name="description" content="Travel Community diary"/>
        <link rel="stylesheet" href="styles/main.css" type="text/css"/>
        <link rel="stylesheet" href="styles/blog.css" type="text/css"/>
        <link rel="stylesheet" href="styles/forums.css" type="text/css"/>
		<link rel="stylesheet" href="styles/bw_yaml.css" type="text/css"/>
		<!--[if lte IE 7]>
		<link rel="stylesheet" href="styles/explorer/iehacks_3col_vlines.css" type="text/css" />
		<![endif]-->

        <script type="text/javascript" src="script/main.js"></script>
    </head>
    <body>
<body>

<!-- #page_margins: Obsolete for now. If we decide to use a fixed width or want a frame around the page, we will need them aswell -->
<div id="page_margins">
<!-- #page: Used to hold the floats -->
<div id="page" class="hold_floats">

<div id="header">
	<div id="topnav">
	  <div id="navigation-functions">
	    <ul>

			<li><a href="http://www.bewelcome.org/whoisonline.php">members online</a></li>
			<li><a href="http://www.bewelcome.org/faq.php">FAQs</a></li>
			<li><a href="http://www.bewelcome.org/feedback.php">Contact Us</a></li>
			<li><a href="http://www.bewelcome.org/mypreferences.php">My Preferences</a></li>
			<li><a href="http://www.bewelcome.org/main.php?action=logout" id="header-logout-link">Logout</a></li>

	    </ul>
	  </div>
	</div>
	<img src="styles/images/logo.gif" alt="Be Welcome"/>
</div>

<!-- #nav: main navigation -->
<div id="nav">
	<div id="nav_main">
	    <ul>
		
			<li ><a href="http://www.bewelcome.org/main.php"><span>Home</span></a></li>
			<li ><a href="http://www.bewelcome.org/member.php?cid=<? echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>"><span>My Account</span></a></li>
			<li ><a href="http://www.bewelcome.org/mymessages.php"><span>My Messages</span></a></li>
			<li ><a href="http://www.bewelcome.org/members.php"><span>Members</span></a></li>
			<li ><a href="http://www.bewelcome.org/groups.php"><span>Groups</span></a></li>
			<li class="active"><a href="http://www.bewelcome.org/tb/forums"><span>Forum</span></a></li>
			<li ><a href="http://www.bewelcome.org/tb/blog"><span>Blogs</span></a></li>
			<li ><a href="http://www.bewelcome.org/tb/gallery/show"><span>Gallery</span></a></li>

			<!-- #nav_flowright: This part of the main navigation floats to the right. The items have to be listed in reversed order to float properly-->			
			<span id="nav_flowright">
		    <li>
		      <form action="quicksearch.php" id="form-quicksearch">
		          <fieldset id="fieldset-quicksearch">
		          Search 
		          <input type="text" name="searchtext" size="10" maxlength="30" id="text-field" />
		          <input type="hidden" name="action" value="quicksearch" />
		          <input type="image" src="styles/images/icon_go.gif" id="submit-button" />
		        </fieldset>
		      </form>
		    </li>
			</span>
			<!-- #nav_flowright: end -->
			
	    </ul>
	</div>
</div>
<!-- #nav: - end -->

<!-- #main: content begins here -->
<div id="main">

	<!-- #teaser: the orange bar shows title and elements that summarize the content of the current page -->
	<div id="teaser" class="clearfix">
		<h2>BETA Community</h2>
		<!--<p>This could be a short description, either to the title's right or below.</p>-->
	</div>
	<!-- #teaser: end -->
	
<!-- #nav: sub navigation -->
<div id="middle_nav" class="clearfix">
	<?php
//$HC->topMenu();
$MyTravelbook->topMenu();
                ?>
</div>
<!-- #nav: - end -->

<!-- #col1: first floating column of content-area  -->
    <div id="col1">
      <div id="col1_content" class="clearfix">

           <h3>Action</h3>
           <ul class="linklist">
		   
				<li><a href="http://www.bewelcome.org/tb/forums/new">New forum post</a></li>
				<li><a href="http://www.bewelcome.org/tb/blog/create">New blog entry</a></li>
<!-- #				<li><a href="#">This is a very very long link</a></li>   -->
<!-- #				<li><a href="#" method=post  title="Test Title">This is a longer link</a></li> -->
<!-- #				<li><a href="#" title="Test Title">This is a link</a></li> -->
				
           </ul>

	</div>
    </div>
<!-- #col1: - end -->

<!-- #col2: second floating column of content-area -->
    <div id="col2">
      <div id="col2_content" class="clearfix">

        <h2>Sponsored Links</h2>
        <p><script type="text/javascript"><!--
google_ad_client = "pub-2715182874315259";
google_ad_width = 120;
google_ad_height = 240;
google_ad_format = "120x240_as";
google_ad_type = "text_image";
google_ad_channel = "";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>     </p>

      </div>
    </div>
<!-- #col2: - end -->

<!-- #col3: static column of content-area -->
    <div id="col3">
      <div id="col3_content" class="clearfix" >

		<div class="info">

			<?php echo $Page->content; ?>
	
		<!-- page content -->
		</div>
      </div>
      <!-- IE Column Clearing -->
	  <div id="ie_clearing">&nbsp;</div>
      <!-- Ende: IE Column Clearing -->
    </div>
<!-- #col3: - Ende -->

</div>
<!-- #main: - Ende -->

<!-- #footer: Begin Footer -->
<div id="footer">
	
	<p>&copy;2007 <strong>BeWelcome</strong> - The Hospitality Network<br />
	Code partly based on <a href="http://sourceforge.net/projects/mytravelbook">MyTravelBook</a></p>
	The Layout is based on <a href="http://www.yaml.de/">YAML</a> 
	&copy; 2005-2006 by <a href="http://www.highresolution.info">Dirk Jesse</a></p>

	<p>Choose your language here. Don't find it? Help us to translate :)</p>
	<p>
	<span><a href="/member.php?cid=lupochen&lang=en"><img height="11px" src="images/en.png" title="English" width=16></a></span>
	<a href="/member.php?cid=lupochen&lang=fr"><img height="11px" src="images/fr.png" title="French" width=16></a>
	<a href="/member.php?cid=lupochen&lang=esp"><img height="11px" src="images/esp.png" title="Español" width=16></a>
	<a href="/member.php?cid=lupochen&lang=de"><img height="11px" src="images/de.png" title="Deutsh" width=16></a>
	</p>
</div>
<!-- #footer: End -->
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
