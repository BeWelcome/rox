<? 
    $words = $this->words;
    $loggedInMember = $this->loggedInMember;
    // values from previous form submit
    if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
        // this is a fresh form
    } else {
        // last time something went wrong.
        // recover old form input.
        $vars = $mem_redirect->post;
    }
    
    $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
    
    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('GalleryController', 'createGalleryCallback');
    
?>
<? if ($this->loggedInMember) { ?>
<div class="row">
    <div class="col-12 col-md-6">
        <h3><?=$words->get('GalleryYourGallery')?></h3>
        <div class="list-group mt-1">
                <a href="gallery/show/user/<?=$loggedInMember->Username?>/images" class="list-group-item"><i class="fa fa-image mr-1"></i><?=$words->get('GalleryUserImages',$cnt_pictures)?></a></li>
                <a href="gallery/show/user/<?=$loggedInMember->Username?>/sets" class="list-group-item"><i class="fa fa-folder-open-o mr-1"></i><?=$words->get('GalleryUserPhotosets',count($galleries))?></a></li>
                <a href="gallery/upload" class="list-group-item"><i class="fa fa-camera-retro mr-1"></i><?=$words->get('GalleryUpload')?></a>
        </div>
    </div>

    <div class="col-12 col-md-6">
    <h3><?=$words->get('GalleryCreateGallery')?></h3>
        <form method="post" class="def-form form-inline" id="gallery-create-form">
        <?=$callback_tag ?>
        <?=$words->get('GalleryCreateNewPhotoset')?>:

            <?php
            // Display errors from last submit
            if (isset($vars['errors']) && !empty($vars['errors']))
            {
                foreach ($vars['errors'] as $error)
                {
                    echo '<div class="alert alert-danger small">'.$words->get($error).'</div>';
                }
            }
            ?>

            <div class="form-group">
                <input name="g-user" type="hidden" value="<?=$this->loggedInMember->id?>">
                <input name="g-title" id="g-title" type="text" size="20" maxlength="30" onclick="$('newGallery').checked = true; $('#deleteonly').val(0);">

                <input name="new" type="hidden" value="1">
                <input name="deleteOnly" id="deleteOnly" type="hidden" value="0">
            </div>
        <input type="submit" class="btn btn-sm btn-primary" name="button" value="<?=$words->getBuffered('Add')?>" id="button" onclick="return submitStuff();"/><?php echo $words->flushBuffer(); ?>
        </form>
    </div>
</div>

<? } ?>