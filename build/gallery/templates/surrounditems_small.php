<?php

$words = new MOD_words();

?>
<table><tr><td>
<?php
if ($Previous) {

    foreach ($Previous as $d) {
    	echo '<a href="gallery/show/image/'.$d->id.'"><img src="gallery/thumbimg?id='.$d->id.'" style="border: 1px solid #ccc; padding: 1px; margin: 5px; width: 60px; height: 60px;" alt="image"/></a><br />
        <span class="small">Previous:</span>';
    }
}
?></td><td><?php
if ($Next) {

    foreach ($Next as $e) {
    	echo '<a href="gallery/show/image/'.$e->id.'"><img src="gallery/thumbimg?id='.$e->id.'" style="border: 1px solid #ccc; padding: 1px; margin: 5px; width: 60px; height: 60px;" alt="image"/></a><br />
        <span class="small">Next: </span>';
    }
}

?></td></tr></table><?php
