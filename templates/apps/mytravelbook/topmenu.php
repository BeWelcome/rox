<?
$menuText = array();
$i18n = new MOD_i18n('apps/mytravelbook/topmenu.php');
$menuText = $i18n->getText('menuText');
?>
<!-- INACTIVE
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
INACTIVE -->

 <div id="nav_sub">
    <ul>
        <li class="active"><a href="http://www.bewelcome.org/main.php"><span>Home</span></a></li>
		<li><a href="blog"><span><?=$menuText['blogs']?></span></a></li>
        <li><a href="trip"><span><?=$menuText['trips']?></span></a></li>
        <li><a href="gallery/show"><span><?=$menuText['gallery']?></span></a></li>
        <li><a href="forums"><span><?=$menuText['forums']?></span></a></li>
        <li><a href="wiki"><span><?=$menuText['wiki']?></span></a></li>
        <li><a href="chat"><span><?=$menuText['chat']?></span></a></li>
    </ul>
</div>

<!--	<div id="middle_nav" class="clearfix">
		<div id="nav_sub" class="notabs">
			<ul>
			</ul>
		</div>
	</div>-->