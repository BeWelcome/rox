<?php

/* Template for showing the deletion page of a single gallery */

?>
    <p><?=$words->get('DeleteAlbumSure', htmlspecialchars($this->gallery->title))?></p>
    <p class="w-100"><a class="btn btn-success" role="button" href="gallery/show/sets/<?=$this->gallery->id?>/delete/true" ><?=$words->get('Yep, just delete it!')?></a> <a class="btn btn-danger" role="button" href="gallery/show/sets/<?=$this->gallery->id?>" ><?=$words->get('Cancel')?></a></p>
