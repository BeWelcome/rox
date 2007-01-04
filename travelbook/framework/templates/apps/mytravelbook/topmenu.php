<?
$menuText = array();
$i18n = new MOD_i18n('apps/mytravelbook/topmenu.php');
$menuText = $i18n->getText('menuText');
?>
<div id="topmenu">
    <ul>
        <li><a href="blog"><?=$menuText['blogs']?></a></li>
        <li><a href="trip"><?=$menuText['trips']?></a></li>
        <li><a href="blog/tags"><?=$menuText['tags']?></a></li>
        <li><a href="gallery/show"><?=$menuText['gallery']?></a></li>
        <li><a href="country"><?=$menuText['country']?></a></li>
        <li><a href="forums"><?=$menuText['forums']?></a></li>
        <li><a href="about"><?=$menuText['about']?></a></li>
        <li><a href="help"><?=$menuText['help']?></a></li>
    </ul>
</div>
