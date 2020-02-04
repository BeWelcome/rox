<div id="masonry-grid" class="row">
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
<div class="col-12 col-sm-6 col-md-4">
        <div class="card p-2 mb-2">
            <?php
            $formkit = $this->layoutkit->formkit;
            $callback_tag = $formkit->setPostCallback('GalleryController', 'updateGalleryCallback');
            ?>
            <form method="POST"><?= $callback_tag; ?>
            <a href="gallery/img?id=<?= $d->id ?>" data-toggle="lightbox" data-type="image"><img class="img-fluid d-block mx-auto mb-1" src="gallery/thumbimg?id=<?= $d->id ?>&amp;t=1" alt="<?= $d->title ?>"></a>
            <?php if ($this->myself) { ?>
             <a href="gallery/img?id=<?= $d->id ?>" class="btn btn-sm btn-primary"><i class="fa fa-edit mr-1"></i><?= $words->get('Edit'); ?></a>
                <input type="submit" class="btn btn-sm btn-danger" name="button" value="<?= $words->getBuffered('GalleryRemoveImagesFromPhotoset') ?>">
                <input type="checkbox" class="form-check-input d-none" name="imageId[]" value="<?= $d->id ?>" checked>
                <input name="gallery" type="hidden" value="<?= $g->id ?>">
                <input name="removeOnly" type="hidden" value="1">
            </form>
            <?php } ?>
        </div>
    </div>
    <?php
    }
}
?>
</div>
