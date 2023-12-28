<?php


$member = $this->member;
$picture_url = 'members/avatar/'.$member->Username.'/160';
?>

<form id="manage" method="POST" class="mt-3">
<div class="row">
    <div class="col-12 order-sm-12 col-lg-3 order-lg-1 postleftcolumn">
        <div class="u-w-full u-hidden u-p-8 d-lg-block">
            <div class="o-avatar o-avatar--l o-avatar--shadow">
                <div class="o-avatar__img-wrapper">
                    <a href="members/<?=$member->Username?>"><img class="o-avatar__img" src="<?=$picture_url?>" alt="Picture of <?=$member->Username?>"/></a>
                </div>
                <a class="o-avatar__name u-break-all" href="members/<?=$member->Username ?>"><?=$member->Username ?></a>
            </div>
        </div>


        <?= $callback_tag; ?>

        <div class="o-checkbox mb-2">
            <input type="checkbox" name="selectAll" id="selectAll" class="o-checkbox__input checker">&nbsp;&nbsp;
            <label class="o-checkbox__label" for="selectAll"><?= $words->get('gallery.select.all'); ?></label>
        </div>
        <?php
        if (isset($galleries) && $galleries) {
            ?>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <input type="radio" id="existingAlbum" name="newOrExistingAlbum" value="Existing" class="o-radiobutton__input" aria-label="<?= $words->get('gallery.use.existing.album') ?>">
                    </div>
                </div>
                <select id="albums" name="gallery" size="1" class="o-input">
                    <option value="">- <?= $words->get('gallery.use.existing.album') ?> -</option>
                    <?php
                    foreach ($galleries as $d) {
                        echo '<option value="'.$d->id.'">'.$d->title.'</option>';
                    }
                    ?>
                </select>
            </div>
            <?php
        }
        ?>

        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <input type="radio" id="newAlbum" name="newOrExistingAlbum" value="New" class="o-radiobutton__input" aria-label="<?= $words->get('gallery.create.new.album'); ?>">
                </div>
            </div>
            <input class="o-input" id="newAlbumTitle" name="newAlbumTitle" maxlength="30" aria-label="<?= $words->get('gallery.create.new.album'); ?>" placeholder="<?= $words->get('gallery.create.new.album'); ?>">
        </div>
        <input name="deleteOrMove" id="deleteOrMove" type="hidden" value="Move">
        <input name="g-user" type="hidden" value="<?= $member->id ?>">
        <input type="submit" class="btn btn-sm btn-primary btn-block mb-2" id="moveImages" name="moveImages" value="<?= $words->getBuffered('Move images') ?>">
        <input type="submit" class="btn btn-sm btn-danger btn-block mb-2" id="deleteImages" name="deleteImages" value="<?= $words->getBuffered('Delete images') ?>">
    </div>
    <div class="col-12 order-sm-1 col-lg-9 order-lg-12">
        <?php
            require SCRIPT_BASE . 'build/gallery/templates/overview.php';
        ?>
    </div>
</div>
</form>
<script type="text/javascript">
    const manageForm = document.getElementById("manage");
    const deleteImages = document.getElementById("deleteImages");
    const moveImages = document.getElementById("moveImages");
    const selectAll = document.getElementById("selectAll");
    const checkboxes = document.getElementsByName("imageId[]");
    const deleteOrMove = document.getElementById("deleteOrMove");
    const newOrExistingAlbum = document.getElementsByName("newOrExistingAlbum");
    const newAlbumTitle = document.getElementById("newAlbumTitle");

    newAlbumTitle.addEventListener("change", () => {
        newOrExistingAlbum[0].checked = false;
        newOrExistingAlbum[1].checked = true;
    });

    deleteImages.addEventListener("click", (e) => {
        e.preventDefault();

        const atLeastOneImageSelected = checkIfAtLeastOneImageSelected();
        if (!atLeastOneImageSelected) {
            alert(<?php echo json_encode($words->get('gallery.no.images.selected')); ?>);
            return;
        }

        deleteOrMove.value = "Delete";
        manageForm.submit();
    });

    moveImages.addEventListener("click", (e) => {
        e.preventDefault();

        const atLeastOneImageSelected = checkIfAtLeastOneImageSelected();
        if (!atLeastOneImageSelected) {
            alert(<?php echo json_encode($words->get('gallery.no.images.selected')); ?>);
            return;
        }

        if (!newOrExistingAlbum[0].checked && !newOrExistingAlbum[1].checked) {
            alert(<?php echo json_encode($words->get('gallery.move.not.possible')); ?>);
            return;
        }

        const albums = document.getElementById("albums");
        if (newOrExistingAlbum[0].checked && albums.value === "") {
            alert(<?php echo json_encode($words->get('gallery.no.album.selected')); ?>);
            return;
        }

        if (newOrExistingAlbum[1].checked && newAlbumTitle.value === "") {
            alert(<?php echo json_encode($words->get('gallery.no.album.given')); ?>);
            return;
        }

        deleteOrMove.value = "Move";
        manageForm.submit();
    });

    selectAll.addEventListener("change", (e) => {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
        e.preventDefault();
    });

    function checkIfAtLeastOneImageSelected() {
        let imageSelected = false;
        checkboxes.forEach((checkbox) => {
            imageSelected ||= checkbox.checked;
        });

        return imageSelected;
    }
</script>
