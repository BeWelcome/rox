<?php

/* Template for showing the content of a gallery */

if ($d) {
?>
<div class="subcolumns">
  <div class="c33l">
    <div class="subcl" >
      <!-- Content left block -->
      <a href="gallery/show/sets/<?=$gallery->id?>">
          <img class="framed" src="gallery/thumbimg?id=<?=$d?>" style="background-color: #fff; padding: 1em; text-align: center" alt="image"/>
      </a>
      <p class="small">
      <?php if ($gallery->text) echo htmlspecialchars($gallery->text) 
      ?>
      </p>
      <p class="small"><?=$words->get('by')?> <?=$this->member->Username?></p>
      <p><?=$num_rows?> <?=$words->get('pictures')?></p>
    </div>
  </div>

  <div class="c66r">
    <div class="subcr">
      <!-- Content right block -->
      <?php
      
  foreach ($statement as $d) {
  	echo '
<div class="img thumb float_left" style="width: 75px; height: 75px; margin: 0; padding-right: 6px;">
  <a href="gallery/img?id='.$d->id.'"><img class="img-fluid" src="gallery/thumbimg?id='.$d->id.'" alt="image" style="width: 75px; height: 75px; margin: 0; padding: 0;" /></a>
</div>';
  }
      
      ?>
    </div>
  </div>
</div>
<?php
}

if ($this->myself) {
?>
<hr/>
<div class="clearfix"><a href="gallery/show/sets/<?=$gallery->id?>/delete" class="bigbuttongrey"><span><?=$words->get('GalleryDelete')?></span></a></div>
<?
}
?>
<hr/>
<?
$shoutsCtrl = new ShoutsController;
$shoutsCtrl->shoutsList('gallery', $gallery->id);

?>
