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
    echo '<div class="alert alert-danger">'.$words->getFormatted('Gallery_NotLoggedIn').'</div>';
    return;
}
if(isset($vars->error)) {
    echo '<div class="alert alert-danger">'.$words->getFormatted($vars['error']).'</div>';
}
if (isset($_GET['g'])) $galleryId = (int)$_GET['g']; 
$postURL = 'gallery/uploaded';
if ($galleryId) $postURL = 'gallery/show/sets/'.$galleryId;

// If the upload-form IS hidden, display a link to show it
?>
<div class="col-12">
    <h4><?=$words->getFormatted('Gallery_UploadInstruction')?></h4>
</div>
<div class="col-12" id="gallery-upload-content">
    <form method="post" action="<?=$postURL?>" class="def-form" id="gallery-img-upload" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?=PFunctions::returnBytes(ini_get('upload_max_filesize'))?>"/>
    <div class="row">
        <div class="col-12 col-md-6">
            <div id="gallery-img-upload-files">
                    <input type="file" name="gallery-file[]" class="w-100 btn-outline-primary" id="upload" oninput="$('div.2').show(); $(this).removeClass('btn-outline-primary').addClass('btn-success');">
                    <div class="upload 2"><input type="file" name="gallery-file[]" class="w-100 btn-outline-primary" oninput="$('div.3').show(); $(this).removeClass('btn-outline-primary').addClass('btn-success');"></div>
                    <div class="upload 3"><input type="file" name="gallery-file[]" class="w-100 btn-outline-primary" oninput="$('div.4').show(); $(this).removeClass('btn-outline-primary').addClass('btn-success');"></div>
                    <div class="upload 4"><input type="file" name="gallery-file[]" class="w-100 btn-outline-primary" oninput="$('div.5').show(); $(this).removeClass('btn-outline-primary').addClass('btn-success');"></div>
                    <div class="upload 5"><input type="file" name="gallery-file[]" class="w-100 btn-outline-primary" oninput="$('div.max5').show();"></div>
                    <div class="upload max5 alert alert-warning">You can only upload 5 images at a time</div>
                                <input type="hidden" name="galleryId" value="<?=$galleryId;?>">
                <?php echo $callbacktag; ?>
                <input type="submit" class="btn btn-sm btn-primary my-2 px-5" value="<?=$words->getSilent('Gallery_UploadSubmit')?>"/>
            </div>
        </div>
        <div class="col-12 col-md-6">

            <div class="alert alert-warning">
                <?=$words->getFormatted('Gallery_UploadWarning')?><? printf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576); ?>
            </div>
        </div>
        <?=$words->flushBuffer()?>
    </div>
    </form>
    <iframe id="gallery-img-upload-getter" name="gallery-img-upload-getter" class="d-none"></iframe>
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

    <script>
        // Hide 4 upload inputs
        $(document).ready(function(){
            $('div.upload').hide();
        });
    </script>

</div>
