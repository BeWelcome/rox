<?php
$Gallery = new Gallery;
$callbackId = $Gallery->uploadProcess();

$uploadText = array();
$i18n = new MOD_i18n('apps/gallery/upload.php');
$uploadText = $i18n->getText('uploadText');
?>
<h2><?=$uploadText['title']?></h2>
<?php
if (!$User = APP_User::login()) {
    echo '<p class="error">'.$uploadError['not_logged_in'].'</p>';
    return;
}
?>
<div id="gallery-upload-content">
    <form method="post" action="gallery/show/user/<?=$User->getHandle()?>" class="def-form" id="gallery-img-upload" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?=PFunctions::returnBytes(ini_get('upload_max_filesize'))?>"/>
    <h3><?=$uploadText['title_images']?></h3>
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
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" value="<?=$uploadText['submit_upload']?>"/>
    </p>
    </form>
    <iframe id="gallery-img-upload-getter" name="gallery-img-upload-getter" class="hidden"></iframe>
    <script type="text/javascript">//<!--
var GalleryImg = new Uploader('gallery-img-upload', {
    iframeAfter:'gallery-upload-content',
    oncomplete:Gallery.imageUploaded,
    submit_title:'<?=$uploadText['loading_title']?>',
    submit_text:'<?=$uploadText['loading_description']?>',
    'notify_heading':'h3'
});
//-->
    </script>
</div>