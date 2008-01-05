<?php

$words = new MOD_words();

if ($message == '') {
?>
    <div id="teaser" class="clearfix">
    <h1><?php echo $words->getFormatted('InfoMessageTitle'); ?></h1>
    </div>
<?php
} else {
?>
    <h2><?php echo $words->getFormatted(''.$messagetitle.''); ?></h2>
    <p><?php echo $words->getFormatted(''.$message.''); ?></p>
<?php } ?>