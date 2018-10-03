<?php


$member = $this->member;
$picture_url = 'members/avatar/'.$member->Username.'/100';
?>

<form method="POST" action="" class="mt-3">
<div class="row">
    <div class="col-12 order-sm-12 col-lg-3 order-lg-1 postleftcolumn">
        <div class="w-100 d-none d-lg-block"><a href="members/<?=$member->Username?>"><img src="<?=$picture_url?>" alt="Picture of <?=$member->Username?>" height="100%" width="100%"/></a></div>
        <a class="btn btn-primary btn-sm btn-block mb-2" href="members/<?=$member->Username ?>"><?=$member->Username ?></a>

        <?= $callback_tag; ?>

        <script>
            function askDelete() {
                returny = confirm("<?= $words->getBuffered('confirmdeleteimages') ?>");
                $('#deleteOnly').val(1);
                if (returny) return true;
                else return false;
            }
        </script>
        <div class="form-check mb-2">
            <input type="checkbox" name="selectAllRadio" id="selectAllRadio" class="form-check-input checker" onClick="toggle(this);">&nbsp;&nbsp;
            <label class=form-label" for="selectAllRadio"><?= $words->get('SelectAll')?></label>
        </div>
        <?
        if (isset($galleries) && $galleries) {
            ?>
            <input name="removeOnly" type="hidden" value="0">
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <input type="radio" id="oldGallery" name="new" value="0" aria-label="<?= $words->get('GalleryAddToPhotoset') ?>">
                    </div>
                </div>
                <select name="gallery" size="1" onchange="$('oldGallery').checked = true;" class="form-control">
                    <option value="">- <?= $words->get('GalleryAddToPhotoset') ?> -</option>
                    <?
                    foreach ($galleries as $d) {
                        echo '<option value="'.$d->id.'">'.$d->title.'</option>';
                    }
                    ?>
                </select>
            </div>
            <?
        }
        ?>

        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <input type="radio" id="newGallery" name="new" value="1" aria-label="Create a new album">
                </div>
            </div>
            <input class="form-control" name="g-title" id="g-title" maxlength="30" aria-label="Enter the name of the new album" placeholder="<?= $words->get('GalleryCreateNewPhotoset') ?>" onclick="$('newGallery').checked = true;  $('#deleteOnly').val(0);">
        </div>
        <input name="deleteOnly" id="deleteOnly" type="hidden" value="0">
        <input name="g-user" type="hidden" value="<?= $member->id ?>">
        <input type="submit" class="btn btn-sm btn-primary btn-block mb-2" name="moveImages" value="<?= $words->getBuffered('Move images') ?>" id="button" onclick="$('#deleteOnly').val(0); return submitStuff();">
        <input type="submit" class="btn btn-sm btn-danger btn-block mb-2" name="deleteImages" value="<?= $words->getBuffered('Delete images') ?>" onclick="return askDelete()" style="cursor:pointer">
    </div>
    <div class="col-12 order-sm-1 col-lg-9 order-lg-12">
        <?
            require SCRIPT_BASE . 'build/gallery/templates/overview.php';
        ?>
    </div>
</div>
</form>
<script type="text/javascript">
    function submitStuff() {
        let deleteOnly = $('#deleteOnly').val();
        let newGallery = $('#newGallery').is(':checked') ;
        let newName = $('#g-title').val().trim();
        if ( deleteOnly === "0" &&  newGallery === true && newName !== "") {
            return true;
        } else {
            return ((deleteOnly === "0") && (newGallery === false));
        }
    }
</script>