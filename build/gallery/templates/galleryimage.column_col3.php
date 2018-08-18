<?php
if ($d->description) {
    $desc = $d->description;
} else {
    $desc = "no description";
}
?>

<div class="col">
    <div class="img">
        <a id="link_<?= $d->id ?>" href="gallery/img?id=<?= $d->id ?>" title="<?= $d->title ?> rel="image">
        <img id="thumb_<?= $d->id ?>" src="gallery/thumbimg?id=<?= $d->id ?>&amp;t=2" class="framed big" alt="image">
        </a>
        <a href="gallery/show/image/<?=$d->id?>/delete" class="btn btn-danger" onclick="return confirm('<?= $words->getSilent("confirmdeletepicture")?>')"><?= $words->getSilent("GalleryDeleteImage")?></a>
    </div>
</div>
<div class="col">

    <p id="desc"><?=$desc?></p>
    <?php
    echo $words->flushBuffer();
    if ($canEdit  || ($GalleryRight > 1)) {
        ?>

        <form method="post" action="gallery/show/image/<?=$d->id; ?>/edit">

            <input type="hidden" name="<?php echo $callbackId; ?>" value="1"/>
            <input type="hidden" name="id" value="<?=$d->id; ?>"/>

            <label for="image-edit-t"><?php echo $words->getFormatted('GalleryLabelTitle'); ?></label>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="photo_title"><?php echo $words->getFormatted('GalleryLabelTitle'); ?></span>
                </div>
                <input type="text" name="t" class="form-control" id="image-edit-t" aria-describedby="photo_title" value="<?= htmlentities($d->title, ENT_COMPAT, 'utf-8'); ?>">
            </div>

            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><?php echo $words->getFormatted('GalleryLabelText'); ?></span>
                </div>
                <textarea id="image-edit-txt" name="txt" class="form-control" aria-label="<?php echo $words->getFormatted('GalleryLabelText'); ?>">
                    <?php
                    echo htmlentities($d->description, ENT_COMPAT, 'utf-8');
                    ?>
                </textarea>
            </div>


            <input type="submit" class="btn btn-primary mt-3" value="submit">

        </form>
    <?php } ?>

</div>

<?php

if ($member) {
PPostHandler::clearVars($callbackId);
}
