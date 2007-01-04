<?php
$userbarText = array();
$i18n = new MOD_i18n('apps/blog/userbar.php');
$userbarText = $i18n->getText('userbarText');
?>
<div id="blog-userbar" class="vert-infobar box">
    <h2><?=$userbarText['title']?></h2>
    <p>
        <a href="blog/create"><?=$userbarText['create_entry']?></a>
         | 
        <a href="blog/cat"><?=$userbarText['manage_cats']?></a>
    </p>
    <div class="clear"></div>
</div>