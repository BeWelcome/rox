<?php

/* Template for showing the overview page of a photoset */

// Only show the galleries with pictures. The belonging user might see them anyway.
if ($d) {
?>
<div class="gallery_container float_left" style="margin: 10px; height: 170px; width: 150px; padding: 20px; text-align: center;">
    <a href="gallery/show/sets/<?=$g->id?>">
        <img class="framed" src="gallery/thumbimg?id=<?=$d?>" alt="image"/>
    </a>
    <h4><a href="gallery/show/sets/<?=$g->id?>"><?=$g->title?></a></h4>
    <p class="small"><?=$words->get('by')?> <?=$member?></p>
    <p><?=$num_rows?> <?=$words->get('pictures')?></p>
</div>
<?php }
