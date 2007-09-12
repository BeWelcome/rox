<?php
$words = new MOD_words();
?>

<!-- #nav: main navigation -->
<div id="nav">
	<div id="nav_main">
	    <ul>
		
			<li><a href="bw/main.php"><span>Home</span></a></li>
			<li><a href="bw/member.php?cid=<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>"><span>My Account</span></a></li>
			<li <?= ($currentTab == 'searchmembers') ? 'class="active"' : '' ?>><a href="rox/searchmembers"><span>Find Members</span></a></li>
			<li <?= ($currentTab != 'searchmembers') ? 'class="active"' : '' ?>><a href="forums"><span>Community</span></a></li>
			<li><a href="bw/groups.php"><span>Groups</span></a></li>
			<li><a href="bw/aboutus.php"><span>Get Answers</span></a></li>

			<!-- #nav_flowright: This part of the main navigation floats to the right. The items have to be listed in reversed order to float properly-->			
			<span id="nav_flowright">
		    <li>
		      <form action="quicksearch.php" id="form-quicksearch">
		          <fieldset id="fieldset-quicksearch">
		          Search 
		          <input type="text" name="searchtext" size="10" maxlength="30" id="text-field" />
		          <input type="hidden" name="action" value="quicksearch" />
		          <input type="image" src="styles/YAML/images/icon_go.gif" id="submit-button" />
		        </fieldset>
		      </form>
		    </li>
			</span>
			<!-- #nav_flowright: end -->
			
	    </ul>
	</div>
</div>
<!-- #nav: - end -->

<!-- <div id="nav_sub">
    <ul>
        <li class="active"><a href="http://www.bewelcome.org/main.php"><span><?php echo $words->get('Menu'); ?></span></a></li>
		<li><a href="blog"><span><?php echo $words->get('Blogs'); ?></span></a></li>
        <li><a href="trip"><span>Trips<?php // FIXME: echo $words->get('Trips'); ?></span></a></li>
        <li><a href="gallery/show"><span><?php echo $words->get('Gallery'); ?></span></a></li>
        <li><a href="forums"><span><?php echo $words->get('Forum'); ?></span></a></li>
        <li><a href="wiki"><span>Wiki<?php // FIXME: echo $words->get('Wiki'); ?></span></a></li>
        <li><a href="chat"><span>Chat<?php // FIXME: echo $words->get('Chat'); ?></span></a></li>
    </ul>
</div>-->



<!--
<div id="middle_nav" class="clearfix">
	<div id="nav_sub" class="notabs">
		<ul>
		</ul>
	</div>
</div>
-->