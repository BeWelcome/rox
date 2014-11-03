<?php
$layoutkit = $this->layoutkit;
$formkit = $layoutkit->formkit;
$vars = $formkit->mem_from_redirect;
$callbacktag = $formkit->setPostCallback('GalleryController', 'uploadedProcess');

//$Gallery = new GalleryController;
//$callbackId = $Gallery->uploadProcess();
//$vars = PPostHandler::getVars($callbackId);
$galleryId = isset($this->galleryId) ? $this->galleryId : false;
$galleryId = ($this->gallery) ? $this->gallery->id : $galleryId;
$words = $this->words;
// If the upload-form is NOT hidden, show it!
?>

<?php
if (!$member = $this->model->getLoggedInMember()) {
    echo '<p class="error">'.$words->getFormatted('Gallery_NotLoggedIn').'</p>';
    return;
}
if(isset($vars->error)) {
    echo '<p class="error">'.$words->getFormatted($vars['error']).'</p>';
}
if (isset($_GET['g'])) $galleryId = (int)$_GET['g']; 
$postURL = 'gallery/uploaded';
if ($galleryId) $postURL = 'gallery/show/sets/'.$galleryId;

// If the upload-form IS hidden, display a link to show it
?>
<div id="gallery-upload-content">
    <form method="post" action="<?=$postURL?>" class="def-form" id="gallery-img-upload" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?=PFunctions::returnBytes(ini_get('upload_max_filesize'))?>"/>
    <h4><?=$words->getFormatted('Gallery_UploadInstruction')?></h4>
    <div class="notify"><?=$words->getFormatted('Gallery_UploadWarning')?> <? printf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576); ?></div>
    <div id="gallery-img-upload-files">
        <div class="bw-row">
            <input type="file" name="gallery-file[]"/>
        </div>
        <div class="bw-row">
            <input type="file" name="gallery-file[]"/>
        </div>
        <div class="bw-row">
            <input type="file" name="gallery-file[]"/>
        </div>
        <div class="bw-row">
            <input type="file" name="gallery-file[]"/>
        </div>
        <div class="bw-row">
            <input type="file" name="gallery-file[]"/>
        </div>
    </div>
    <p>
        <input type="hidden" name="galleryId" value="<?=$galleryId;?>"/>
        <?php echo $callbacktag; ?>
        <input type="submit" class="button" value="<?=$words->getSilent('Gallery_UploadSubmit')?>"/>
        <?=$words->flushBuffer()?>
    </p>
    </form>
    <iframe id="gallery-img-upload-getter" name="gallery-img-upload-getter" class="hidden"></iframe>
    <script type="text/javascript">//<!--
    <? if (isset($uploaderUrl)) { ?>
    var Gallery = {
    	imageUploaded: function() {
    		if($('gallery-upload-content')) {
    			var url = http_baseuri+'<?=$uploaderUrl?>';
    			new Ajax.Updater('gallery-upload-content', url, {method:'get', parameters:'raw=1'});
    		}
    	}
    }
    <? } ?>
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
