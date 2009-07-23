<?php

/* Template for showing a single image */

$desc = $d->description;
if ($d->description == '' && $canEdit) {
    $desc = $words->getBuffered("GalleryAddDescription");
}
?>
<h2 id="g-title"><?=$d->title?></h2>
<p id="g-text"><?=$desc?></p>
<?php
$words->flushBuffer();
if ($canEdit  || ($GalleryRight > 1)) {
?>

    <a href="gallery/show/image/<?=$d->id?>" id="g-title-edit" class="button" style="display:none;"><?= $words->getSilent("EditTitle")?></a>
    <a href="gallery/show/image/<?=$d->id?>" id="g-text-edit" class="button" style="display:none;"><?= $words->getSilent("EditDescription")?></a>
    <a style="cursor:pointer" href="gallery/show/image/<?=$d->id?>/delete" class="button" onclick="return confirm('<?= $words->getFormatted("confirmdeletepicture")?>')"><?= $words->getSilent("Delete")?></a>
    <?=$words->flushBuffer()?>

<form method="post" action="gallery/show/image/<?=$d->id?>/edit" class="def-form">
    <fieldset id="image-edit" class="inline NotDisplayed">
    <legend><?php echo $words->getFormatted('GalleryTitleEdit'); ?></legend>
    
        <div class="row">
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
            <input type="submit" name="button" value="submit" id="button" />
        </div>
        <div class="row">
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
            cols: '25',
            ajaxOptions: {method: 'get'}
        })

    new Ajax.InPlaceEditor('g-text', 'gallery/ajax/image/', {
            callback: function(form, value) {
                return '?item=<?=$d->id?>&text=' + decodeURIComponent(value)
            },
            externalControl: 'g-text-edit',
            rows: '5',
            cols: '25',
            ajaxOptions: {method: 'get'}
        })
    </script>
<?php } ?>
<div class="floatbox">
<div class="img">
<?php
echo '<a id="link_'.$d->id.'" href="gallery/img?id='.$d->id.'" title="'.$d->title.' :: '.$d->description.'" class="lightview" rel="image">
    <img id="thumb_'.$d->id.'" src="gallery/thumbimg?id='.$d->id.'&amp;t=2" class="framed big" alt="image"/>
</a>';
?>
</div>
</div>

<div id="comments" style="padding: 10px 0px">
    <h3><?php echo $words->getFormatted('CommentsTitle'); ?></h3>
    
<?php
$comments = $Gallery->getComments($image->id);
if (!$comments) {
	echo '<p>'.$words->getFormatted('NoComments').'</p>';
} else {
    $count = 0;
    $lastHandle = '';
    foreach ($comments as $comment) {
        require 'comment.php';
        ++$count;
        $lastHandle = $comment->user_handle;
    }
}

if ($User) require_once 'commentform.php';
?>
</div>
<?php
if ($User) { 
PPostHandler::clearVars($callbackId); 
PPostHandler::clearVars($callbackIdCom); 
}
?>