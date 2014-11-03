<?php

/* Template for showing a single image */

$desc = $d->description;
if ($d->description == '' && $canEdit) {
    $desc = $words->getSilent("GalleryAddDescription");
}
?>
<p id="g-text"><?=$desc?></p>
<?php
echo $words->flushBuffer();
if ($canEdit  || ($GalleryRight > 1)) {
?>

    <a href="gallery/show/image/<?=$d->id?>" id="g-title-edit" class="button" style="display:none;"><?= $words->getSilent("EditTitle")?></a>
    <a href="gallery/show/image/<?=$d->id?>" id="g-text-edit" class="button" style="display:none;"><?= $words->getSilent("EditDescription")?></a>
    <a style="cursor:pointer" href="gallery/show/image/<?=$d->id?>/delete" class="button" onclick="return confirm('<?= $words->getSilent("confirmdeletepicture")?>')"><?= $words->getSilent("GalleryDeleteImage")?></a>
    <?=$words->flushBuffer()?>

<form method="post" action="gallery/show/image/<?=$d->id?>/edit" class="def-form">
    <fieldset id="image-edit" class="inline NotDisplayed">
    <legend><?php echo $words->getFormatted('GalleryTitleEdit'); ?></legend>

        <div class="bw-row">
            <label for="image-edit-t"><?php echo $words->getFormatted('GalleryLabelTitle'); ?></label><br/>
            <input type="text" id="image-edit-t" name="t" class="short"<?php
                echo ' value="'.htmlentities($d->title, ENT_COMPAT, 'utf-8').'"';
            ?>/><br/><br/>
            <label for="image-edit-txt"><?php echo $words->getFormatted('GalleryLabelText'); ?></label><br/>
            <textarea id="image-edit-txt" name="txt" cols="30" rows="4"><?php
            echo htmlentities($d->description, ENT_COMPAT, 'utf-8');
            ?></textarea>
            <div id="bcomment-text" class="statbtn"></div>
	        <input type="hidden" name="<?php echo $callbackId; ?>" value="1"/>
	        <input type="hidden" name="id" value="<?=$d->id?>"/>
            <p class="desc"><?php echo $words->getFormatted('GalleryDescTitle'); ?></p>
            <input type="submit" class="button" name="button" value="submit" id="button" />
        </div>
        <div class="bw-row">
        </div>
</fieldset>
</form>
    <script type="text/javascript">
    $('image-edit').hide();
    $('g-title-edit').show();
    $('g-text-edit').show();

    new Ajax.InPlaceEditor('g-title', 'gallery/ajax/image/', {
            callback: function(form, value) {
                return '?item=<?=$d->id?>&title=' + decodeURIComponent(value)
            },
            externalControl: 'g-title-edit',
            formClassName: 'inplaceeditor-form-big',
            cols: '35',
            ajaxOptions: {method: 'get'}
        })

    new Ajax.InPlaceEditor('g-text', 'gallery/ajax/image/', {
            callback: function(form, value) {
                return '?item=<?=$d->id?>&text=' + decodeURIComponent(value)
            },
            externalControl: 'g-text-edit',
            rows: '4',
            cols: '35',
            ajaxOptions: {method: 'get'}
        })
    </script>
<?php } ?>
<div class="clearfix">
<div class="img">
<?php
echo '<a id="link_'.$d->id.'" href="gallery/img?id='.$d->id.'" title="'.$d->title.' :: '.$d->description.'" class="lightview" rel="image">
    <img id="thumb_'.$d->id.'" src="gallery/thumbimg?id='.$d->id.'&amp;t=2" class="framed big" alt="image"/>
</a>';
?>
</div>
</div>

<?php
$shoutsCtrl = new ShoutsController;
$shoutsCtrl->shoutsList('gallery_items', $d->id);

if ($member) {
PPostHandler::clearVars($callbackId);
}
