<?php

/* Template for showing the deletion page of a single gallery */

?>
<div class="gallery_container float_left" style="margin: 10px; height: 170px; width: 150px; padding: 20px; text-align: center;">
    <p><?=$words->get('GalleryAboutToDeleteTheGallery', $this->gallery->name)?> <?=$words->get('AreYouSure')?></p>
    <p><a class="button" href="gallery/show/sets/<?=$this->gallery->id?>/delete/true" >Yes</a> or <a class="button" href="gallery/show/sets/<?=$this->gallery->id?>" >No</a></p>
</div>