<?php

/* Template for showing the col3-content of a single gallery */

$g = $gallery;
$g->user_handle = MOD_member::getUserHandle($g->user_id_foreign);

// Set variable own (if own gallery)
$Own = false;
if ($this->myself) {
    $R = MOD_right::get();
    $GalleryRight = $R->hasRight('Gallery');
    $Own = ($this->myself == $this->member->Username);
}
if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}

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
      <p id="g-text" class="description">
      <?php 
        echo ($Own && !$g->text) ? $words->get('GalleryAddDescription') : $g->text;
      ?>
      </p>
      <div class="floatbox">
           <?=MOD_layoutbits::PIC_30_30($this->member->Username,'',$style='float_left')?>
           <p class="small"><?=$num_rows?> <?=$words->get('pictures')?><br />
           <?=$words->get('by')?> <?=$this->member->Username?></p>
       </div>
       <?
       if ($Own) {
           echo <<<HTML
           <a href="gallery/show/sets/" id="g-title-edit" class="button">{$words->getSilent('EditTitle')}</a>
           <a href="gallery/show/sets/" id="g-text-edit" class="button">{$words->getSilent('EditDescription')}</a>
           <script type="text/javascript">
           new Ajax.InPlaceEditor('g-title', 'gallery/ajax/set/', {
                   callback: function(form, value) {
                       return '?item={$g->id}&title=' + decodeURIComponent(value)
                   },
                   externalControl: 'g-title-edit',
                   formClassName: 'inplaceeditor-form-big',
                   cols: '25',
                   ajaxOptions: {method: 'get'}
               })

           new Ajax.InPlaceEditor('g-text', 'gallery/ajax/set/', {
                   callback: function(form, value) {
                       return '?item={$g->id}&text=' + decodeURIComponent(value)
                   },
                   externalControl: 'g-text-edit',
                   rows: '5',
                   cols: '25',
                   ajaxOptions: {method: 'get'}
               })
           </script>
HTML;
        }
        echo $words->flushBuffer();
        echo <<<HTML
    </div>
  </div>

  <div class="c66r">
    <div class="subcr">
      <!-- Content right block -->
HTML;
  if ($this->myself && $this->upload) {
      // Display the upload form
      require SCRIPT_BASE . 'build/gallery/templates/uploadform.php';
  } else {
      echo '<div class="floatbox">';
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