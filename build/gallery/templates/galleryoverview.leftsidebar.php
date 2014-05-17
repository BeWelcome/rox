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
    $subTab = $this->getSubmenuActiveItem();
?>
<h3><?=$words->get('GalleryBrowse')?></h3>
<div>
    <ul class="list-image">
        <li <?php echo ($subTab === 'overview') ? ' class="active"' : ''; ?>><a style="cursor:pointer;" href="gallery"><span><?php echo $words->getBuffered('Photosets'); ?></span></a></li>
        <li <?php echo ($subTab === 'images') ? ' class="active"' : ''; ?>><a style="cursor:pointer;" href="gallery/images"><span><?php echo $words->getBuffered('GalleryAllPhotos'); ?></span></a>
        </li>
    </ul>
    <?php echo $words->flushBuffer(); ?>
</div>
<?php
    if ($this->loggedInMember) { ?>

<h3><?=$words->get('GalleryYourGallery')?></h3>
<div>
    <ul>
        <li><img src="images/icons/folder_picture.png" style="padding-right: 3px;"> <a href="gallery/show/user/<?=$loggedInMember->Username?>/sets"><?=$words->get('GalleryUserPhotosets')?></a></li>
        <li><img src="images/icons/images.png" style="padding-right: 3px;"> <a href="gallery/manage" class="manage"><?=$words->get('GalleryManage')?></a></li>
        <li><img src="images/icons/picture_add.png" style="padding-right: 3px;"> <a href="gallery/upload"><?=$words->get('GalleryUpload')?></a></li>
    </ul>
    <?//=$this->userLinks()?>
</div>

    <h3><?=$words->get('GalleryCreateGallery')?></h3>
    <p>
        <form method="post" class="def-form" id="gallery-create-form">
        <?=$callback_tag ?>
        <?=$words->get('GalleryCreateNewPhotoset')?>: 
        <br />
        <input name="g-user" type="hidden" value="<?=$this->loggedInMember->get_userId()?>">
        <input name="g-title" id="g-title" type="text" maxlength="30" style="width: 90%" onclick="$('newGallery').checked = true; $('deleteonly').value = 0;">
        <?php
        // Display errors from last submit	
        if (isset($vars['errors']) && !empty($vars['errors']))
        {
            foreach ($vars['errors'] as $error)
            {
                echo '<div class="error small">'.$words->get($error).'</div>';
            }
        }
        ?>
        <input name="new" type="hidden" value="1">
        <input id="deleteonly" name="deleteOnly" type="hidden" value="0">
        <br />
        <input type="submit" name="button" value="<?=$words->getBuffered('Add')?>" id="button" onclick="$('deleteonly').value = 0; return submitStuff();"/>
        </form>
    </p>

<? } 