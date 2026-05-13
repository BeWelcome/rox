<?php

$member = $this->member;
$words = $this->words;
$cntPictures = (int) $this->cnt_pictures;
$hasPhotos = $cntPictures > 0;
$uploadUrl = 'gallery/upload_multiple';
$backLabel = htmlspecialchars((string) $words->get('profile.back'), ENT_QUOTES, 'UTF-8');
$backHrefEsc = htmlspecialchars($backToEditHref, ENT_QUOTES, 'UTF-8');

$gmkToBytes = static fn (string $v): int => match (strtolower(substr($v, -1))) {
    'g' => (int) $v * 1073741824,
    'm' => (int) $v * 1048576,
    'k' => (int) $v * 1024,
    default => (int) $v,
};
$maxUploadBytes = min($gmkToBytes(ini_get('upload_max_filesize')), $gmkToBytes(ini_get('post_max_size')));
$maxUploadMB = max(1, (int) round($maxUploadBytes / 1048576));

$defaultTab = $hasPhotos ? 'photos' : 'upload';
?>

<div class="p-gallery-manage__pagehead">
    <div class="p-gallery-manage__pagehead-row">
        <div class="p-gallery-manage__pagehead-copy">
            <div class="p-gallery-manage__pagehead-heading">
                <a href="<?= $backHrefEsc ?>"
                   class="p-edit-subpage__back"
                   aria-label="<?= $backLabel ?>">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </a>
                <div>
                    <p class="p-gallery-manage__pagehead-eyebrow"><?= $words->get('Gallery') ?></p>
                    <h1 class="p-gallery-manage__pagehead-title"><?= $words->get('GalleryManage') ?></h1>
            <?php if ($hasPhotos):
                $countKey = $cntPictures > 1
                    ? 'gallery.manage.photo.count.many'
                    : 'gallery.manage.photo.count.one';
            ?>
                <p class="p-gallery-manage__subtitle">
                    <?= $words->get($countKey, $cntPictures) ?>
                </p>
            <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<nav class="p-gallery-tabs" aria-label="Gallery sections">
    <button class="p-gallery-tab-btn" data-tab="photos" type="button">
        <i class="fas fa-images" aria-hidden="true"></i>
        <span><?= $words->get('Photos') ?: 'Photos' ?></span>
    </button>
    <button class="p-gallery-tab-btn" data-tab="albums" type="button">
        <i class="fas fa-layer-group" aria-hidden="true"></i>
        <span><?= $words->get('GalleryTitleSets') ?: 'Albums' ?></span>
    </button>
    <button class="p-gallery-tab-btn" data-tab="upload" type="button">
        <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
        <span><?= $words->get('galleryupload') ?: 'Upload' ?></span>
    </button>
</nav>

<?php /* ── PHOTOS TAB ── */ ?>
<div id="tab-photos" class="p-gallery-tab-panel">

<?php if (!$hasPhotos): ?>

    <section class="p-gallery-manage__empty">
        <div class="p-gallery-manage__empty-icon" aria-hidden="true">
            <i class="fas fa-images"></i>
        </div>
        <h2 class="p-gallery-manage__empty-title"><?= $words->get('gallery.manage.empty.title') ?: 'No photos yet' ?></h2>
        <p class="p-gallery-manage__empty-text">
            <?= $words->get('gallery.manage.empty.text') ?: 'Share your travels and memories with the community by uploading your first photos.' ?>
        </p>
        <button class="p-gallery-manage__empty-btn" type="button" data-goto-tab="upload">
            <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
            <span><?= $words->get('galleryupload') ?></span>
        </button>
    </section>

<?php else: ?>

<form id="manage" method="POST" class="p-gallery-manage">

    <?= $callback_tag; ?>

    <section class="p-gallery-manage__panel">
        <div class="p-gallery-manage__panel-row">
            <label class="p-gallery-manage__select-all" for="selectAll">
                <input type="checkbox" id="selectAll" name="selectAll" class="p-gallery-manage__select-all-input checker">
                <span class="p-gallery-manage__select-all-box" aria-hidden="true">
                    <i class="fas fa-check"></i>
                </span>
                <span class="p-gallery-manage__select-all-text"><?= $words->get('gallery.select.all'); ?></span>
            </label>
        </div>

        <fieldset class="p-gallery-manage__panel-row p-gallery-manage__album">
            <legend class="visually-hidden"><?= $words->get('gallery.use.existing.album'); ?></legend>

            <?php if (isset($galleries) && $galleries): ?>
                <label class="p-gallery-manage__album-row" for="existingAlbum">
                    <input type="radio" id="existingAlbum" name="newOrExistingAlbum" value="Existing"
                           class="p-gallery-manage__album-radio"
                           aria-label="<?= $words->get('gallery.use.existing.album'); ?>">
                    <span class="p-gallery-manage__album-radio-dot" aria-hidden="true"></span>
                    <select id="albums" name="gallery" size="1" class="p-gallery-manage__album-select">
                        <option value="">- <?= $words->get('gallery.use.existing.album'); ?> -</option>
                        <?php
                        foreach ($galleries as $d) {
                            echo '<option value="'.$d->id.'">'.$d->title.'</option>';
                        }
                        ?>
                    </select>
                </label>
            <?php endif; ?>

            <label class="p-gallery-manage__album-row" for="newAlbum">
                <input type="radio" id="newAlbum" name="newOrExistingAlbum" value="New"
                       class="p-gallery-manage__album-radio"
                       aria-label="<?= $words->get('gallery.create.new.album'); ?>">
                <span class="p-gallery-manage__album-radio-dot" aria-hidden="true"></span>
                <input type="text"
                       class="p-gallery-manage__album-input"
                       id="newAlbumTitle" name="newAlbumTitle" maxlength="30"
                       aria-label="<?= $words->get('gallery.create.new.album'); ?>"
                       placeholder="<?= $words->get('gallery.create.new.album'); ?>">
            </label>
        </fieldset>

        <div class="p-gallery-manage__panel-row p-gallery-manage__buttons">
            <input type="submit"
                   class="p-gallery-manage__btn p-gallery-manage__btn--primary"
                   id="moveImages" name="moveImages"
                   disabled
                   value="<?= $words->getBuffered('Move images') ?>">
            <input type="submit"
                   class="p-gallery-manage__btn p-gallery-manage__btn--danger"
                   id="deleteImages" name="deleteImages"
                   disabled
                   value="<?= $words->getBuffered('Delete images') ?>">
        </div>

        <input name="deleteOrMove" id="deleteOrMove" type="hidden" value="Move">
        <input name="g-user" type="hidden" value="<?= $member->id ?>">
    </section>

    <div class="p-gallery-manage__grid-wrap">
        <?php
            require SCRIPT_BASE . 'build/gallery/templates/overview.php';
        ?>
    </div>
</form>

<?php endif; ?>
</div>

<?php /* ── ALBUMS TAB ── */ ?>
<div id="tab-albums" class="p-gallery-tab-panel">
    <div class="p-gallery-albums-wrap">
        <?php
            require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
        ?>
    </div>
</div>

<?php /* ── UPLOAD TAB ── */ ?>
<div id="tab-upload" class="p-gallery-tab-panel">
    <div class="p-upload">
        <div class="p-upload__card">
            <div class="p-upload__field">
                <label class="p-upload__label" for="upload-album-input">
                    <?= htmlspecialchars($words->get('gallery.upload_to_album') ?: 'Album (optional)', ENT_QUOTES, 'UTF-8') ?>
                </label>
                <input type="text"
                       id="upload-album-input"
                       class="p-upload__select"
                       list="upload-albums-list"
                       placeholder="<?= htmlspecialchars($words->get('gallery.upload_to_album') ?: 'Album name (optional)', ENT_QUOTES, 'UTF-8') ?>"
                       autocomplete="off">
                <datalist id="upload-albums-list">
                    <?php if (isset($galleries) && $galleries): ?>
                    <?php foreach ($galleries as $g): ?>
                    <option value="<?= htmlspecialchars((string) $g->title, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
            </div>

            <div id="upload-dropzone" class="p-upload__dropzone" role="button" tabindex="0" aria-label="Drop images here or click to select">
                <div class="p-upload__dropzone-icon" aria-hidden="true">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="p-upload__dropzone-title"><?= htmlspecialchars($words->get('gallery.upload.dropzone.title') ?: 'Click or drag & drop images here', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="p-upload__dropzone-subtitle"><?= htmlspecialchars($words->get('gallery.upload.dropzone.subtitle') ?: 'Supported formats: JPEG, PNG, GIF', ENT_QUOTES, 'UTF-8') ?></div>
                <input type="file" class="p-upload__file-input" multiple id="upload-file-input" accept="image/*">
                <div id="upload-dropzone-summary" class="p-upload__dropzone-summary d-none">
                    <i class="fas fa-images" aria-hidden="true"></i>
                    <span id="upload-summary-text"></span>
                    <button id="upload-dropzone-clear" type="button" class="p-upload__dropzone-clear" aria-label="Clear">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="p-upload__actions">
                <button id="upload-btn" class="p-upload__btn p-upload__btn--primary" type="button" disabled>
                    <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($words->get('upload') ?: 'Upload', ENT_QUOTES, 'UTF-8') ?></span>
                </button>
                <button id="upload-abort-btn" class="p-upload__btn p-upload__btn--ghost" type="button" disabled>
                    <i class="fas fa-times" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($words->get('abort') ?: 'Cancel', ENT_QUOTES, 'UTF-8') ?></span>
                </button>
            </div>
        </div>

        <section id="upload-queue" class="p-upload__queue d-none">
            <h2 class="p-upload__queue-title"><?= htmlspecialchars($words->get('gallery.upload.queue.title') ?: 'Upload queue', ENT_QUOTES, 'UTF-8') ?></h2>

            <div id="upload-image-progress-tpl" class="p-upload__item d-none">
                <div class="p-upload__item-thumb"><img src="" alt=""></div>
                <div class="p-upload__item-body">
                    <div class="p-upload__item-filename">{filename}</div>
                    <div class="p-upload__progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div class="p-upload__progress-bar" style="width: 0;"></div>
                    </div>
                </div>
            </div>

            <div id="upload-size-error-tpl" class="p-upload__item p-upload__item--error d-none">
                <div class="p-upload__item-thumb"><img src="" alt=""></div>
                <div class="p-upload__item-body">
                    <div class="p-upload__item-message p-upload__item-message--error"></div>
                </div>
            </div>

            <div id="upload-type-error-tpl" class="p-upload__item p-upload__item--error d-none">
                <div class="p-upload__item-thumb p-upload__item-thumb--placeholder" aria-hidden="true">
                    <i class="fas fa-file"></i>
                </div>
                <div class="p-upload__item-body">
                    <div class="p-upload__item-message p-upload__item-message--error"></div>
                </div>
            </div>

            <div id="upload-progressbars" class="p-upload__queue-list"></div>
        </section>
    </div>
</div>

<script type="text/javascript">
(function () {

    /* ── Tab switching ── */
    const tabs = document.querySelectorAll('.p-gallery-tab-btn');
    const panels = document.querySelectorAll('.p-gallery-tab-panel');
    const defaultTab = <?= json_encode($defaultTab) ?>;

    function activateTab(name) {
        const valid = ['photos', 'albums', 'upload'];
        const target = valid.includes(name) ? name : defaultTab;
        if (hadUploads && target !== 'upload') {
            location.href = location.pathname + '#' + target;
            location.reload();
            return;
        }
        tabs.forEach(t => t.classList.toggle('p-gallery-tab-btn--active', t.dataset.tab === target));
        panels.forEach(p => p.classList.toggle('p-gallery-tab-panel--active', p.id === 'tab-' + target));
        if (history.replaceState) {
            history.replaceState(null, '', location.pathname + '#' + target);
        }
    }

    tabs.forEach(t => t.addEventListener('click', () => activateTab(t.dataset.tab)));

    document.querySelectorAll('[data-goto-tab]').forEach(btn => {
        btn.addEventListener('click', () => activateTab(btn.dataset.gotoTab));
    });

    const initialHash = location.hash.replace('#', '');
    activateTab(initialHash || defaultTab);

    /* ── Manage form (Photos tab) ── */
    const manageForm = document.getElementById('manage');
    if (manageForm) {
        const deleteImages = document.getElementById('deleteImages');
        const moveImages = document.getElementById('moveImages');
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.getElementsByName('imageId[]');
        const deleteOrMove = document.getElementById('deleteOrMove');
        const newOrExistingAlbum = document.getElementsByName('newOrExistingAlbum');
        const newAlbumTitle = document.getElementById('newAlbumTitle');

        if (newAlbumTitle) {
            newAlbumTitle.addEventListener('change', () => {
                if (newOrExistingAlbum[0]) newOrExistingAlbum[0].checked = false;
                if (newOrExistingAlbum[1]) newOrExistingAlbum[1].checked = true;
            });
        }

        deleteImages && deleteImages.addEventListener('click', (e) => {
            e.preventDefault();
            if (deleteImages.disabled) return;
            deleteOrMove.value = 'Delete';
            manageForm.submit();
        });

        moveImages && moveImages.addEventListener('click', (e) => {
            e.preventDefault();
            if (moveImages.disabled) return;
            if ((!newOrExistingAlbum[0] || !newOrExistingAlbum[0].checked) &&
                (!newOrExistingAlbum[1] || !newOrExistingAlbum[1].checked)) {
                alert(<?= json_encode($words->get('gallery.move.not.possible')) ?>);
                return;
            }
            const albums = document.getElementById('albums');
            if (newOrExistingAlbum[0] && newOrExistingAlbum[0].checked && albums && albums.value === '') {
                alert(<?= json_encode($words->get('gallery.no.album.selected')) ?>);
                return;
            }
            if (newOrExistingAlbum[1] && newOrExistingAlbum[1].checked && newAlbumTitle && newAlbumTitle.value === '') {
                alert(<?= json_encode($words->get('gallery.no.album.given')) ?>);
                return;
            }
            deleteOrMove.value = 'Move';
            manageForm.submit();
        });

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
                updateManageButtons();
            });
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateManageButtons));

        function updateManageButtons() {
            const hasSelection = Array.from(checkboxes).some(cb => cb.checked);
            if (deleteImages) deleteImages.disabled = !hasSelection;
            if (moveImages) moveImages.disabled = !hasSelection;
            if (selectAll && checkboxes.length > 0) {
                selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
            }
        }

        updateManageButtons();
    }

    /* ── Upload (Upload tab) ── */
    const imageType = /image.*/;
    const maxSize = <?= $maxUploadBytes ?>;

    const uploadBtn = document.getElementById('upload-btn');
    const abortBtn = document.getElementById('upload-abort-btn');
    const dropzone = document.getElementById('upload-dropzone');
    const fileInput = document.getElementById('upload-file-input');
    const dropzoneSummary = document.getElementById('upload-dropzone-summary');
    const summaryText = document.getElementById('upload-summary-text');
    const dropzoneClear = document.getElementById('upload-dropzone-clear');
    const progressBars = document.getElementById('upload-progressbars');
    const queueSection = document.getElementById('upload-queue');
    const imageTpl = document.getElementById('upload-image-progress-tpl');
    const sizeTpl = document.getElementById('upload-size-error-tpl');
    const typeTpl = document.getElementById('upload-type-error-tpl');

    let uploadClients = [];
    let hadUploads = false;

    function updateDropzoneSummary() {
        const count = fileInput.files ? fileInput.files.length : 0;
        if (count > 0) {
            dropzoneSummary.classList.remove('d-none');
            summaryText.textContent = count === 1 ? '1 file selected' : count + ' files selected';
            uploadBtn.disabled = false;
        } else {
            dropzoneSummary.classList.add('d-none');
            uploadBtn.disabled = true;
        }
    }

    dropzone.addEventListener('click', e => {
        if (e.target.closest('#upload-dropzone-clear')) return;
        fileInput.click();
    });
    dropzone.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
    });
    ['dragenter', 'dragover'].forEach(evt => {
        dropzone.addEventListener(evt, e => {
            e.preventDefault(); e.stopPropagation();
            dropzone.classList.add('p-upload__dropzone--active');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, e => {
            e.preventDefault(); e.stopPropagation();
            dropzone.classList.remove('p-upload__dropzone--active');
        });
    });
    dropzone.addEventListener('drop', e => {
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            updateDropzoneSummary();
        }
    });
    fileInput.addEventListener('change', updateDropzoneSummary);
    dropzoneClear.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        fileInput.value = '';
        updateDropzoneSummary();
    });

    uploadBtn.addEventListener('click', uploadFiles);
    abortBtn.addEventListener('click', uploadAbort);
    updateDropzoneSummary();

    function uploadFiles() {
        if (progressBars.children.length !== 0) progressBars.replaceChildren();
        const files = fileInput.files;
        uploadBtn.disabled = true;
        abortBtn.disabled = false;
        let count = 0;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            let tpl;
            if (!file.type.match(imageType)) {
                tpl = getTypeErrorTpl(i, file);
            } else if (file.size > maxSize) {
                tpl = getSizeErrorTpl(i, file);
            } else {
                tpl = getProgressTpl(i, file);
            }
            tpl.classList.remove('d-none');
            progressBars.append(tpl);
            const fnEl = document.getElementById('upload-fn-' + i);
            if (!fnEl) continue;
            count++;
            fnEl.innerText = file.name;
            doUploadFile(i, file);
        }

        progressBars.classList.remove('d-none');
        queueSection.classList.remove('d-none');
        if (count === 0) resetUploadForm();
    }

    function doUploadFile(i, file) {
        const formData = new FormData();
        const client = new XMLHttpRequest();
        const album = document.getElementById('upload-album-input').value.trim();
        uploadClients.push(client);

        formData.append('file', file);
        formData.append('album', album);

        client.onerror = () => {
            alert(<?= json_encode($words->get('gallery.upload.error') ?: 'An error occurred during upload.') ?>);
            resetUploadForm();
        };

        client.onload = () => {
            const row = document.getElementById('upload-item-' + i);
            const progEl = document.getElementById('upload-prog-' + i);
            if (progEl) progEl.remove();
            let res;
            try { res = JSON.parse(client.responseText); } catch(e) { res = {success: false, error: 'Parse error', filename: file.name}; }
            const body = row && row.querySelector('.p-upload__item-body');
            const msg = document.createElement('div');
            msg.classList.add('p-upload__item-message');
            if (res.success) {
                const thumb = row.querySelector('.p-upload__item-thumb');
                const check = document.createElement('div');
                check.className = 'p-upload__item-thumb-check';
                check.innerHTML = '<i class="fas fa-circle-check" aria-hidden="true"></i>';
                thumb && thumb.appendChild(check);
                const fnEl = row.querySelector('.p-upload__item-filename');
                if (fnEl) fnEl.textContent = res.filename;
                msg.classList.add('p-upload__item-message--success');
                msg.textContent = <?= json_encode($words->get('gallery.upload.successful') ?: 'Image uploaded successfully.') ?>;
                hadUploads = true;
            } else {
                msg.classList.add('p-upload__item-message--error');
                msg.innerHTML = '<i class="fas fa-circle-exclamation" aria-hidden="true"></i> ' +
                    res.filename + ' — ' + (res.error || 'Error');
            }
            body && body.appendChild(msg);
            uploadClients = uploadClients.filter(c => c !== client);
            if (uploadClients.length === 0) resetUploadForm();
        };

        client.upload.onprogress = e => {
            const p = Math.round(100 / e.total * e.loaded);
            const pct = document.getElementById('upload-pct-' + i);
            if (pct) { pct.style.width = p + '%'; pct.setAttribute('aria-valuenow', p); }
        };

        client.onabort = () => {
            uploadClients = uploadClients.filter(c => c !== client);
        };

        client.open('POST', '/new/image/upload');
        client.send(formData);
    }

    function uploadAbort() {
        uploadClients.forEach((c, i) => {
            const item = document.getElementById('upload-item-' + i);
            if (item) item.remove();
            c.abort();
        });
        alert(<?= json_encode($words->get('upload.canceled') ?: 'Upload canceled.') ?>);
        resetUploadForm();
    }

    function getProgressTpl(i, file) {
        const el = imageTpl.cloneNode(true);
        el.id = 'upload-item-' + i;
        const body = el.children[1];
        const prog = body.children[1];
        prog.id = 'upload-prog-' + i;
        prog.children[0].id = 'upload-pct-' + i;
        body.children[0].id = 'upload-fn-' + i;
        setThumb(el, file);
        return el;
    }

    function getSizeErrorTpl(i, file) {
        const el = sizeTpl.cloneNode(true);
        el.id = 'upload-item-' + i;
        const err = el.querySelector('.p-upload__item-message');
        if (err) err.innerHTML = '<i class="fas fa-circle-exclamation" aria-hidden="true"></i> ' +
            file.name + ' — <?= addslashes($words->get('gallery.upload.size.error') ?: 'File too large') ?>';
        setThumb(el, file);
        return el;
    }

    function getTypeErrorTpl(i, file) {
        const el = typeTpl.cloneNode(true);
        el.id = 'upload-item-' + i;
        const err = el.querySelector('.p-upload__item-message');
        if (err) err.innerHTML = '<i class="fas fa-circle-exclamation" aria-hidden="true"></i> ' +
            file.name + ' — <?= addslashes($words->get('gallery.upload.type.error') ?: 'Not an image') ?>';
        return el;
    }

    function setThumb(el, file) {
        el.children[0].firstChild.src = window.URL.createObjectURL(file);
    }

    function resetUploadForm() {
        fileInput.value = '';
        updateDropzoneSummary();
        uploadBtn.disabled = true;
        abortBtn.disabled = true;
    }

})();
</script>
