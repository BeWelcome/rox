<?php $words = new MOD_words(); ?>
<div id="blog-userbar" class="vert-infobar box">
    <h2><?=$words->get('linkAdminbar')?></h2>
    <p>
        <a href="link/update"><?=$words->get('linkUpdate')?></a><br>
        <a href="link/rebuild"><?=$words->get('linkRebuild')?></a><br>
        <a href="link/rebuildmissing"><?=$words->get('linkRebuildMissing')?></a>
    </p>
    <div class="clear"></div>
</div>
