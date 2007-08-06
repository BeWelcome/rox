<?php
$words = new MOD_words();

/* INACTIVE
<div id="topmenu">
    <ul>
        <li><a href="http://www.bewelcome.org/main.php">Menu</a></li>
		<li><a href="blog"><?=$menuText['blogs']?></a></li>
        <li><a href="trip"><?=$menuText['trips']?></a></li>
        <li><a href="gallery/show"><?=$menuText['gallery']?></a></li>
        <li><a href="country"><?=$menuText['country']?></a></li>
        <li><a href="forums"><?=$menuText['forums']?></a></li>
        <li><a href="wiki"><?=$menuText['wiki']?></a></li>
        <li><a href="chat"><?=$menuText['chat']?></a></li>
     <li><a href="http://www.bewelcome.org/faq.php">FAQ</a></li>
      <li><a href="http://www.bewelcome.org/feedback.php">Contact</a></li>
    </ul>
</div>
INACTIVE
*/
?>
<div id="nav_sub">
    <ul>
        <li class="active"><a href="http://www.bewelcome.org/main.php"><span><?php echo $words->get('Menu'); ?></span></a></li>
		<li><a href="blog"><span><?php echo $words->get('Blogs'); ?></span></a></li>
        <li><a href="trip"><span>Trips<?php // FIXME: echo $words->get('Trips'); ?></span></a></li>
        <li><a href="gallery/show"><span><?php echo $words->get('Gallery'); ?></span></a></li>
        <li><a href="forums"><span><?php echo $words->get('Forum'); ?></span></a></li>
        <li><a href="wiki"><span>Wiki<?php // FIXME: echo $words->get('Wiki'); ?></span></a></li>
        <li><a href="chat"><span>Chat<?php // FIXME: echo $words->get('Chat'); ?></span></a></li>
    </ul>
</div>

<!--
<div id="middle_nav" class="clearfix">
	<div id="nav_sub" class="notabs">
		<ul>
		</ul>
	</div>
</div>
-->