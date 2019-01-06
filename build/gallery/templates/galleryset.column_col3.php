<?php

/* Template for showing the col3-content of a single gallery */

$g = $gallery;
$g->user_handle = MOD_member::getUserHandle($g->user_id_foreign);
$purifier = MOD_htmlpure::getPurifier();
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
if ($statement){
foreach ($statement as $d) { ?>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card h-100 p-2">
            <a href="#" data-toggle="lightbox"  data-type="image"><img class="w-100" src="gallery/thumbimg?id=<?= $d->id ?>&amp;t=1" alt="<?= $d->title ?>"></a>
            <? if ($this->myself) { ?>
             <a href="gallery/img?id=<?= $d->id ?>" class="btn btn-outline-primary mt-1"><i class="fa fa-edit mr-1"></i><?= $words->get('Edit'); ?></a>
                <?
                    $formkit = $this->layoutkit->formkit;
                    $callback_tag = $formkit->setPostCallback('GalleryController', 'updateGalleryCallback');
                ?>
                <form method="POST" action=""><?= $callback_tag; ?>
                <input type="checkbox" class="input_check d-none" name="imageId[]" value="<?= $d->id ?>" checked>
                <input name="gallery" type="hidden" value="<?= $g->id ?>">
                <input name="removeOnly" type="hidden" value="1">
                <input type="submit" class="btn btn-outline-danger btn-block" name="button" value="<?= $words->getBuffered('GalleryRemoveImagesFromPhotoset') ?>">
            </form>
            <? } ?>
        </div>
    </div>
<?
}
}

if ($this->myself) {
    echo '</div>';
// Display the upload form
require SCRIPT_BASE . 'build/gallery/templates/uploadform.php';
}

/*
?>


<div class="subcolumns gallery_overview">
  <div class="c33l">
    <div class="subcl" >
      <!-- Content left block -->
<?php if ($d) { ?>
        <a href="gallery/img?<?= $d; ?>">
          <img class="gallery_first" src="gallery/thumbimg?id=<?=$d?>&amp;t=1" alt="image"/>
      </a>
<? } ?>
      <p id="g-text" class="description">
      <?php 
        echo ($Own && !$g->text) ? $words->get('GalleryAddDescription') : $purifier->purify($g->text);
      ?>
      </p>
      <div class="clearfix">

          <img src="members/avatar/<?= $this->member->Username; ?>/30">
           <p class="small"><?=$num_rows?> <?=$words->get('pictures')?><br />
           <?=$words->get('by')?> <a href="members/<?=$this->member->Username?>"><?=$this->member->Username?></a> 
           <a href="gallery/show/user/<?=$this->member->Username?>" title="<?=$words->getSilent('galleryUserOthers',$this->member->Username)?>"><?php echo $words->flushBuffer(); ?>
           <img src="styles/css/minimal/images/iconsfam/pictures.png" style="float: none">
           </a>
           </p>
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
      echo '</div>';
      ?>
    </div>
  </div>
</div>
<hr/>
<?php

// $shoutsCtrl = new ShoutsController;
// $shoutsCtrl->shoutsList('gallery', $gallery->id);


*/
?>
