<?php

$words = new MOD_words();

?>
<table style="padding: 0;"><tr><td style="padding: 0;">
<?php
if ($Previous) {

    foreach ($Previous as $d) {
    	echo '<a href="gallery/img?id='.$d->id.'"><img src="gallery/thumbimg?id='.$d->id.'" style="border: 1px solid #ccc; padding: 1px; margin: 15px 25px 15px 0; width: 60px; height: 60px;" alt="image"/></a><br />';
        echo '<a href="gallery/img?id='.$d->id.'"><span class="small"><img src="styles/css/minimal/images/icon_image_prev.gif" style="float: left; padding-right: 5px;">'.$words->get('Previous').'</span></a>';
    }
}
?></td><td style="text-align: right; padding: 0;"><?php
if ($Next) {

    foreach ($Next as $e) {
    	echo '<a href="gallery/img?id='.$e->id.'"><img src="gallery/thumbimg?id='.$e->id.'" style="border: 1px solid #ccc; padding: 1px; margin: 15px 0 15px 0; width: 60px; height: 60px;" alt="image"/></a><br />';
        echo '<a href="gallery/img?id='.$e->id.'"><span class="small"><img src="styles/css/minimal/images/icon_image_next.gif" style="float: right; padding-left: 5px;">'.$words->get('Next').'</span></a>';
    }
}

?></td></tr></table><?php
