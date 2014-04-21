<h2><?=$words->get('PlacesWikiTitle');?></h2>
<?php if (APP_User::isBWLoggedIn()) :?><p><?=$words->get('PlacesWikiHelp');?></p><?php endif;?>
<?php if ($this->wikipage) : ?>
<div class="wiki"><?php
$wiki = new WikiController();
$wiki->getWiki($this->wikipage,false);?></div>
<?php endif; ?>