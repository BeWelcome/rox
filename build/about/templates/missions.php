<?php
$words = new MOD_words($this->getSession());
?>

<div class="info">
<h3><?php echo $words->get("OurMission"); ?></h3>
<q><?php echo $words->get("OurMissionQuote") ?></q>
<p><?php echo $words->get("OurMissionText") ?></p>

<h3><?php echo $words->get("OurAim") ?></h3>
<p><?php echo $words->get("OurAimText") ?></p>
</div>
