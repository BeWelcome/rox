<h2><?=$words->get('PlacesWikiTitle');?></h2>
<?php
    $a = new APP_User();
if ($a->isBWLoggedIn()) :?><p><?=$words->get('PlacesWikiHelp');?></p><?php endif;?>
<?php if ($this->wikipage) : ?>
<div class="wiki"><?php
$wiki = new WikiController();
$wiki->getWiki($this->wikipage,false);?></div>
<?php endif; ?>