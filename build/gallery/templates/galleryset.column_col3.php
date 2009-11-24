<?php

/* Template for showing the col3-content of a single gallery */

?>
<div class="subcolumns gallery_overview">
  <div class="c33l">
    <div class="subcl" >
      <!-- Content left block -->
<?php if ($d) { ?>
      <a href="gallery/show/sets/<?=$gallery->id?>">
          <img class="gallery_first" src="gallery/thumbimg?id=<?=$d?>&amp;t=1" alt="image"/>
      </a>
<? } ?>
      <p class="description">
      <?php if ($gallery->text) echo $gallery->text; ?>
      </p>
      <div class="floatbox">
           <?=MOD_layoutbits::PIC_30_30($this->member->Username,'',$style='float_left')?>
           <p class="small"><?=$num_rows?> <?=$words->get('pictures')?><br />
           <?=$words->get('by')?> <?=$this->member->Username?></p>
       </div>
    </div>
  </div>

  <div class="c66r">
    <div class="subcr">
      <!-- Content right block -->
      <?php
  if ($this->myself && $this->upload) {
      // Display the upload form
      require SCRIPT_BASE . 'build/gallery/templates/uploadform.php';
  } else {
      echo '<div>';
      foreach ($statement as $d) {
      	echo '
    <div class="img thumb_container float_left">
      <a href="gallery/show/image/'.$d->id.'"><img class="thumb framed" src="gallery/thumbimg?id='.$d->id.'" alt="image" /></a>
    </div>';
      }
  }
      echo '</div>';
      ?>
    </div>
  </div>
</div>
<hr/>
<?php

$shoutsCtrl = new ShoutsController;
$shoutsCtrl->shoutsList('gallery', $gallery->id);

?>