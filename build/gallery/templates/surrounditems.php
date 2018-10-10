<?php

$words = new MOD_words();
?>

<h3><?php echo $words->getFormatted('gallerySetOthers')?></h3>

<?php
echo '<div class="clearfix">';
if ($Previous) {
    foreach ($Previous as $d) {
    	echo '<div class="imgnext float_left" style="border: 1px solid #999; margin: 5px; background: #fff url('.PVars::getObj('env')->baseuri.'gallery/thumbimg?id='.$d->id.') no-repeat; width: 80px; height: 70px;"><a href="gallery/img?id='.$d->id.'"><img src="images/misc/imgprev.gif" alt="image"/></a></div>';
    }
}
echo '';
if ($Next) {
    foreach ($Next as $e) {
    	echo '<div class="imgnext float_left" style="border: 1px solid #999; margin: 5px; background: #fff url('.PVars::getObj('env')->baseuri.'gallery/thumbimg?id='.$e->id.') bottom right no-repeat; width: 80px; height: 70px;"><a href="gallery/img?id='.$e->id.'"><img src="images/misc/imgnext.gif" alt="image"/></a></div>';
    }
}
?>
</div>
