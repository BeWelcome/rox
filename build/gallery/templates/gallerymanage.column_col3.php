<?php


$member = $this->member;
$picture_url = 'members/avatar/'.$member->Username.'/100';
?>

<div class="row">
    <div class="col-12 order-sm-12 col-lg-3 order-lg-1 postleftcolumn">
        <div class="w-100 d-none d-lg-block"><a href="members/<?=$member->Username?>"><img src="<?=$picture_url?>" alt="Picture of <?=$member->Username?>" height="100%" width="100%"/></a></div>
        <a class="btn btn-primary btn-sm btn-block" href="members/<?=$member->Username ?>"><?=$member->Username ?></a>

        <form method="POST" action="" class="mt-3">
            <?= $callback_tag; ?>

            <script>
                function askDelete() {
                    returny = confirm('{$words->getBuffered("confirmdeleteimages")}');
                    $('deleteonly').value = 1;
                    if (returny) return true;
                    else return false;
                }
            </script>
            <p>
                <input type="checkbox" name="selectAllRadio" class="checker" onClick="toggle(this);">
                &nbsp;&nbsp;<?= $words->get('SelectAll')?>
            </p>

            <p class="m-0">
                <?
                if (isset($galleries) && $galleries) {
                    ?>
                    <label class="sr-only">
                        <?= $words->get('GalleryAddToPhotoset') ?>
                    </label>
                    <input type="radio" name="new" id="oldGallery" value="0" class="mr-1 d-none">
                    <input name="removeOnly" type="hidden" value="0">
                    <select name="gallery" size="1" onchange="$('oldGallery').checked = true;" class="w-100">
                        <option value="">- <?= $words->get('GalleryAddToPhotoset') ?> -</option>
                        <?
                        foreach ($galleries as $d) {
                            echo '<option value="'.$d->id.'">'.$d->title.'</option>';
                        }
                        ?>
                        <option data-toggle="collapse" href="#CreateNewAlbum" aria-controls="CreateNewAlbum">-- <?= $words->get('GalleryCreateNewPhotoset') ?> --</option>
                    </select>
                    <?
                }
                ?>

            <div class="collapse" id="CreateNewAlbum">
                <label class="sr-only">
                    <?= $words->get('GalleryCreateNewPhotoset') ?>
                </label>
                <input type="radio" name="new" id="newGallery" value="1" class="mr-1">
                <input name="g-user" type="hidden" value="<?= $member->get_userid() ?>">
                <input name="g-title" id="g-title" maxlength="30" placeholder="<?= $words->get('GalleryCreateNewPhotoset') ?>" onclick="$('newGallery').checked = true;  $('deleteonly').value = 0;">
            </div>
            <input type="submit" class="btn btn-sm btn-primary btn-block" name="button" value="<?= $words->getBuffered('Move images') ?>" id="button" onclick="$('deleteonly').value = 0; return submitStuff();">
            </p>
            <div class="w-100">
                <input name="deleteOnly" id="deleteonly" type="hidden" value="0">
                <input type="submit" class="btn btn-sm btn-danger btn-block" name="button" value="<?= $words->getBuffered('Delete images') ?>" onclick="return askDelete()" style="cursor:pointer">
            </div>
        </form>

    </div>
    <div class="col-12 order-sm-1 col-lg-9 order-lg-12">
        <?
            require SCRIPT_BASE . 'build/gallery/templates/overview.php';
        ?>
    </div>
</div>
<? /*


        echo '<form method="POST" action="">'.$callback_tag;
        echo <<<HTML
        <!-- Subtemplate: 2 rows at 66/33 percent -->
        <div class="row">
           <div class="col-12 col-lg-3">
           
HTML;
        if ($this->myself) {
        echo <<<HTML
        <script>
            function askDelete() {
                returny = confirm('{$words->getBuffered("confirmdeleteimages")}');
                $('deleteonly').value = 1;
                if (returny) return true;
                else return false;
            }
        </script>
        <p>
            <input type="checkbox" name="selectAllRadio" class="checker" onClick="toggle(this);">
            &nbsp;&nbsp;{$words->get('SelectAll')}            
        </p>
       
        <p class="m-0">
HTML;
        if (isset($galleries) && $galleries) { 
        echo <<<HTML
        <label class="sr-only">
            {$words->get('GalleryAddToPhotoset')}
        </label>
            <input type="radio" name="new" id="oldGallery" value="0" class="mr-1 d-none">
            <input name="removeOnly" type="hidden" value="0">
            <select name="gallery" size="1" onchange="$('oldGallery').checked = true;" class="w-100">
                <option value="">- {$words->get('GalleryAddToPhotoset')} -</option>
HTML;
        foreach ($galleries as $d) {
            echo '<option value="'.$d->id.'">'.$d->title.'</option>';
        }
        echo <<<HTML
        <option data-toggle="collapse" href="#CreateNewAlbum" aria-controls="CreateNewAlbum">-- {$words->get('GalleryCreateNewPhotoset')} --</option>
            </select>
HTML;
        }
        echo <<<HTML
        <div class="collapse" id="CreateNewAlbum">
            <label class="sr-only">
            {$words->get('GalleryCreateNewPhotoset')}
            </label>
            <input type="radio" name="new" id="newGallery" value="1" class="mr-1">
            <input name="g-user" type="hidden" value="{$member->get_userid()}">
            <input name="g-title" id="g-title" maxlength="30" placeholder="{$words->get('GalleryCreateNewPhotoset')}" onclick="$('newGallery').checked = true;  $('deleteonly').value = 0;">
        </div>
        <input type="submit" class="btn btn-primary" name="button" value="{$words->getBuffered('Move images')}" id="button" onclick="$('deleteonly').value = 0; return submitStuff();"/>
        </p>
        <div class="w-100">
            <input name="deleteOnly" id="deleteonly" type="hidden" value="0">
            <input type="submit" class="btn btn-primary" name="button" value="{$words->getBuffered('Delete images')}" class="button" onclick="return askDelete()" style="cursor:pointer"/>
        </div>
        </form>
HTML;
        }
        echo <<<HTML
            </div>
 */ ?>