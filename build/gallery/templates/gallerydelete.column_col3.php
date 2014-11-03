<?php

/* Template for showing the deletion page of a single gallery */

?>
    <p><?=$words->get('DeleteAlbumSure', htmlspecialchars($this->gallery->title))?></p>
    <p><a class="button" role="button" href="gallery/show/sets/<?=$this->gallery->id?>/delete/true" ><?=$words->get('Yep, just delete it!')?></a> <a class="button" role="button" href="gallery/show/sets/<?=$this->gallery->id?>" ><?=$words->get('Cancel')?></a></p>
