<?php
$Gallery = new Gallery;
$callbackId = $Gallery->uploadProcess();

$uploadText = array();
$words = new MOD_words();
$i18n = new MOD_i18n('apps/gallery/upload.php');
$uploadText = $i18n->getText('uploadText');
?>
<h2><? echo $words->get('Gallery_Title'); ?></h2>
<?php
if (!$User = APP_User::login()) {
    echo '<p class="error">'.$uploadError['not_logged_in'].'</p>';
    return;
}
?>
<div id="gallery-upload-content">
    <form method="post" action="gallery/show/user/<?=$User->getHandle()?>" class="def-form" id="gallery-img-upload" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?=PFunctions::returnBytes(ini_get('upload_max_filesize'))?>"/>
    <h3><? echo $words->get('Gallery_TitleImages'); ?></h3>
    <p><? echo $words->get('Gallery_TextImages'); ?></p>
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
        <input type="submit" value="<? echo $words->get('Gallery_SubmitUpload'); ?>"/>
    </p>
    </form>
</div>