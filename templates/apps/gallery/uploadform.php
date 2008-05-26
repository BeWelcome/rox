<?php
$Gallery = new Gallery;
$callbackId = $Gallery->uploadProcess();
$vars = PPostHandler::getVars($callbackId);
$words = new MOD_words();
?>
<h2><?=$words->getFormatted('Gallery_UploadTitle')?></h2>
<?php
if (!$User = APP_User::login()) {
    echo '<p class="error">'.$words->getFormatted('Gallery_NotLoggedIn').'</p>';
    return;
}
if(isset($vars['error'])) {
    echo '<p class="error">'.$words->getFormatted($vars['error']).'</p>';
}
?>
<div id="gallery-upload-content">
    <form method="post" action="gallery/show/user/<?=$User->getHandle()?>" class="def-form" id="gallery-img-upload" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?=PFunctions::returnBytes(ini_get('upload_max_filesize'))?>"/>
    <h3><?=$words->getFormatted('Gallery_UploadInstruction')?></h3>
    <div class="notify"><?=$words->getFormatted('Gallery_UploadWarning')?> <? printf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576); ?></div>
    <div id="gallery-img-upload-files">
        <div class="row">
            <input type="file" name="gallery-file[]"/>
        </div>
        <div class="row">
            <input type="file" name="gallery-file[]"/>
        </div>
        <div class="row">
            <input type="file" name="gallery-file[]"/>
        </div>
        <div class="row">
            <input type="file" name="gallery-file[]"/>
        </div>
        <div class="row">
            <input type="file" name="gallery-file[]"/>
        </div>
    </div>
    <p>
        <input type="hidden" name="galleryId" value="<? if (isset($_GET['g'])) echo (int)$_GET['g']?>"/>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" value="<?=$words->getFormatted('Gallery_UploadSubmit')?>"/>
    </p>
    </form>
    <iframe id="gallery-img-upload-getter" name="gallery-img-upload-getter" class="hidden"></iframe>
    <script type="text/javascript">//<!--
var GalleryImg = new Uploader('gallery-img-upload', {
    iframeAfter:'gallery-upload-content',
    oncomplete:Gallery.imageUploaded,
    submit_title:'<?=$words->getFormatted('Gallery_LoadingTitle')?>',
    submit_text:'<?=$words->getFormatted('Gallery_LoadingDescription')?>',
    'notify_heading':'h3'
});
//-->
    </script>
</div>
<?php
PPostHandler::clearVars($callbackId);
?>