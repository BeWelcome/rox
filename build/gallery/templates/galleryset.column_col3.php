<?php
$g = $gallery;
$g->user_handle = MOD_member::getUserHandle($g->user_id_foreign);
$Own = false;
if ($this->myself) {
    $R = MOD_right::get();
    $Own = ($this->myself == $this->member->Username);
}
if (!isset($vars['errors'])) {
    $vars['errors'] = [];
}
$words = $words ?? new MOD_words();
$formkit = $this->layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('GalleryController', 'updateGalleryCallback');
?>
<div class="p-gallery-show">

    <div class="p-gallery-manage__pagehead">
        <div class="p-gallery-manage__pagehead-row">
            <div class="p-gallery-manage__pagehead-copy">
                <div class="p-gallery-manage__pagehead-heading">
                    <a href="gallery/show/user/<?= htmlspecialchars((string) $g->user_handle) ?>"
                       class="p-edit-subpage__back"
                       aria-label="Back to gallery">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </a>
                    <div>
                        <p class="p-gallery-manage__pagehead-eyebrow"><?= htmlspecialchars((string) $g->user_handle) ?></p>
                        <h1 class="p-gallery-manage__pagehead-title"><?= htmlspecialchars((string) $g->title) ?></h1>
                    </div>
                </div>
            </div>
            <?php if ($this->myself): ?>
            <div class="p-gallery-manage__pagehead-actions">
                <a href="gallery/manage" class="o-button" title="<?= $words->get('GalleryManage') ?>">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                </a>
                <a href="gallery/show/sets/<?= $g->id ?>/delete"
                   class="p-gallery-photo-item__btn p-gallery-photo-item__btn--danger"
                   style="width:auto;padding:0 0.75rem;"
                   onclick="return confirm('<?= addslashes($words->get('GalleryDelete') ?: 'Delete this album?') ?>')">
                    <i class="fas fa-trash" aria-hidden="true"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($statement): ?>
    <div class="p-gallery-photo-grid" data-gallery="set-<?= $g->id ?>">
        <?php foreach ($statement as $d): ?>
        <div class="p-gallery-photo-item">
            <a href="gallery/img?id=<?= $d->id ?>"
               class="p-gallery-photo-item__link"
               data-toggle="lightbox"
               data-type="image"
               data-title="<?= htmlspecialchars((string) $d->title) ?>"
               data-gallery="set-<?= $g->id ?>">
                <img src="gallery/thumbimg?id=<?= $d->id ?>&t=2"
                     alt="<?= htmlspecialchars((string) $d->title) ?>"
                     loading="lazy">
            </a>
            <?php if ($this->myself): ?>
            <div class="p-gallery-photo-item__actions">
                <a href="gallery/img?id=<?= $d->id ?>"
                   class="p-gallery-photo-item__btn p-gallery-photo-item__btn--edit"
                   title="<?= $words->get('Edit') ?>">
                    <i class="fas fa-edit" aria-hidden="true"></i>
                </a>
                <form method="POST" style="display:contents">
                    <?= $callback_tag ?>
                    <input type="hidden" name="imageId[]" value="<?= $d->id ?>">
                    <input type="hidden" name="gallery" value="<?= $g->id ?>">
                    <input type="hidden" name="removeOnly" value="1">
                    <button type="submit"
                            class="p-gallery-photo-item__btn p-gallery-photo-item__btn--remove"
                            title="<?= $words->get('GalleryRemoveImagesFromPhotoset') ?>">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="p-profile-empty-hint u:mt-16"><?= $words->get('gallery.manage.empty.title') ?: 'No photos in this album yet.' ?></p>
    <?php endif; ?>

</div>
