<?php

/* Template for showing the deletion page of a single gallery */

?>
    <p><?=$words->get("You're about to delete the album '%s'. Are you sure?", $this->gallery->name)?></p>
    <p><a class="button" href="gallery/show/sets/<?=$this->gallery->id?>/delete/true" ><?=$words->get('Yep, just delete it!')?></a> <a class="button" href="gallery/show/sets/<?=$this->gallery->id?>" ><?=$words->get('Cancel')?></a></p>