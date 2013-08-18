<h2><?=$words->get('wiki');?></h2>
<p><?=$words->get('PlacesWikiHelp');?></p>
<?php echo "*" .$this->wikipage."*";?>
<div class="wiki"><?php
$wiki = new WikiController();
$wiki->getWiki($this->wikipage,false);?></div>
