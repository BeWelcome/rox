<?php
$User = APP_User::login();
if ($User) {
    $Gallery = new Gallery;
    $callbackId = $Gallery->editProcess();
    $vars =& PPostHandler::getVars($callbackId);
}
$words = new MOD_words();

$d = $image;
echo '
<h2>'.$d->title.'</h2>
<div class="img">
    <img src="gallery/thumbimg?id='.$d->id.'&amp;t=2" class="framed big" alt="image"/>
    <p class="small">'.$d->width.'x'.$d->height.'; '.$d->mimetype.'; '.$words->getFormatted('GalleryUploadedBy').': <a href="bw/member.php?cid='.$d->user_handle.'">'.$d->user_handle.'</a>.</p>';

echo '<p class="small"><a href="gallery/img?id='.$d->id.'&amp;s=1"><img src="images/icons/disk.png" alt="'.$words->getFormatted('GalleryDownload').'" title="'.$words->getFormatted('GalleryDownload').'"/></a></p>';
if ($User && $User->getId() == $d->user_id_foreign) {
    echo '<p class="small"><a href="gallery/show/image/'.$d->id.'/delete"><img src="images/icons/delete.png" alt="'.$words->getFormatted('GalleryDeleteImage').'" title="'.$words->getFormatted('GalleryDeleteImage').'"/></a></p>';
}

if ($User && $User->getId() == $d->user_id_foreign) {
?>
<form method="post" action="gallery/show/image/<?=$d->id?>" class="def-form">
    <fieldset id="image-edit" class="inline">
    <legend><?php echo $words->getFormatted('GalleryTitleEdit'); ?></legend>
    <h3><?php echo $words->getFormatted('GalleryTitleEdit'); ?></h3>
    
        <div class="row">
            <label for="image-edit-t"><?php echo $words->getFormatted('GalleryLabelTitle'); ?></label><br/>
            <input type="text" id="image-edit-t" name="t" class="long"<?php
                echo ' value="'.htmlentities($d->title, ENT_COMPAT, 'utf-8').'"';
            ?>/>
	        <input type="hidden" name="<?php echo $callbackId; ?>" value="1"/>
	        <input type="hidden" name="id" value="<?=$d->id?>"/>
            <p class="desc"><?php echo $words->getFormatted('GalleryDescTitle'); ?></p>
        </div>
        <div class="row">
        </div>    
</fieldset>
</form>
<script type="text/javascript">//<!--
createFieldsetMenu();
//-->
</script>
<?php
}
?>