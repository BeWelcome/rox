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
    <form method="post" action="gallery/show/user/<?=$User->getHandle()?>" class="def-form" id="gallery-img-upload" enctype="multipart/form-data" onsubmit="document.getElementById('uploadStatus').innerHTML = '<img src=\'images/misc/loading.gif\' />' + ' <?=$words->getFormatted('Gallery_UploadStatus')?> ...';">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?=PFunctions::returnBytes(ini_get('upload_max_filesize'))?>"/>
    <h3><?=$words->getFormatted('Gallery_UploadInstruction')?></h3>
    <div class="notify"><?=$words->getFormatted('Gallery_UploadWarning')?><? printf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576); ?></div>
    <p>
    <div id="gallery-img-upload-files">
        <div class="row">
            <input type="file" name="gallery-file"/>
        </div>
    </div>
    <p>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" value="<?=$words->getFormatted('Gallery_UploadSubmit')?>"/>&nbsp;&nbsp;<span id="uploadStatus"></span>
    </p>
    </form>
</div>
<?php
PPostHandler::clearVars($callbackId);
?>
