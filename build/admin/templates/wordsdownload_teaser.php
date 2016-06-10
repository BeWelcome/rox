<?php
$words = new MOD_words($this->getSession());
?>
<div id="teaser" class="page-teaser clearfix">
<h1><?php echo $words->getBuffered("WordsDownload_Title"); ?></h1>
</div>