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

$activeSet = $this->activeSet ?? null;
$setStatement = $this->setStatement ?? null;
$forcedTab = isset($_GET['tab']) ? $_GET['tab'] : null;
$defaultTab = $activeSet ? 'albums' : ($forcedTab ?? ($hasPhotos ? 'photos' : 'upload'));
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
<?php if ($activeSet): ?>
    <div class="p-gallery-set-view">
        <div class="p-gallery-set-nav">
            <a href="/gallery/manage?tab=albums" class="p-gallery-set-nav__back">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
                <?= $words->get('GalleryTitleSets') ?: 'Albums' ?>
            </a>
            <span class="p-gallery-set-nav__sep" aria-hidden="true">/</span>
            <span class="p-gallery-set-nav__title"><?= htmlspecialchars((string) $activeSet->title) ?></span>
            <button type="button"
                    class="p-gallery-set-nav__delete"
                    data-set-id="<?= (int) $activeSet->id ?>"
                    data-set-title="<?= htmlspecialchars((string) $activeSet->title, ENT_QUOTES, 'UTF-8') ?>">
                <i class="fas fa-trash" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Delete album modal -->
        <div id="delete-album-modal" class="c-confirm-modal" aria-hidden="true" role="dialog">
            <div class="c-confirm-modal__backdrop"></div>
            <div class="c-confirm-modal__dialog">
                <p class="c-confirm-modal__text">
                    <?= $words->get('gallery.album.delete.confirm') ?: 'Delete this album? Photos will not be deleted.' ?>
                </p>
                <div class="c-confirm-modal__actions">
                    <button type="button" class="c-confirm-modal__btn c-confirm-modal__btn--cancel" id="delete-album-cancel">
                        <?= $words->get('Cancel') ?: 'Cancel' ?>
                    </button>
                    <button type="button" class="c-confirm-modal__btn c-confirm-modal__btn--danger" id="delete-album-confirm">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                        <?= $words->get('Delete') ?: 'Delete' ?>
                    </button>
                </div>
            </div>
        </div>
        <?php if ($setStatement): ?>
        <div class="p-gallery-photo-grid" data-gallery="set-<?= $activeSet->id ?>">
            <?php foreach ($setStatement as $d): ?>
            <div class="p-gallery-photo-item"
                 data-photo-id="<?= (int) $d->id ?>"
                 data-photo-title="<?= htmlspecialchars((string) $d->title, ENT_QUOTES, 'UTF-8') ?>"
                 data-photo-thumb="gallery/thumbimg?id=<?= $d->id ?>&t=2"
                 data-photo-edit="gallery/show/image/<?= $d->id ?>/edit"
                 data-photo-delete="gallery/show/image/<?= $d->id ?>/delete">
                <a href="gallery/img?id=<?= $d->id ?>"
                   class="p-gallery-photo-item__link"
                   data-toggle="lightbox"
                   data-type="image"
                   data-title="<?= htmlspecialchars((string) $d->title) ?>"
                   data-gallery="set-<?= $activeSet->id ?>">
                    <img src="gallery/thumbimg?id=<?= $d->id ?>&t=2"
                         alt="<?= htmlspecialchars((string) $d->title) ?>"
                         loading="lazy">
                </a>
                <button type="button"
                        class="p-gallery-photo-item__menu-btn"
                        aria-label="<?= htmlspecialchars($words->get('Options') ?: 'Options', ENT_QUOTES, 'UTF-8') ?>">
                    <i class="fas fa-ellipsis-v" aria-hidden="true"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Photo action sheet (single instance, shared across all photos) -->
        <div id="photo-action-sheet" class="p-photo-sheet" aria-hidden="true" role="dialog" aria-modal="true"
             aria-label="<?= htmlspecialchars($words->get('Options') ?: 'Photo options', ENT_QUOTES, 'UTF-8') ?>">
            <div class="p-photo-sheet__backdrop"></div>
            <div class="p-photo-sheet__panel">
                <div class="p-photo-sheet__handle" aria-hidden="true"></div>
                <button type="button" class="p-photo-sheet__close" aria-label="<?= htmlspecialchars($words->get('Close') ?: 'Close', ENT_QUOTES, 'UTF-8') ?>">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <div class="p-photo-sheet__preview">
                    <img id="photo-sheet-thumb" src="" alt="" class="p-photo-sheet__img">
                </div>
                <p id="photo-sheet-title" class="p-photo-sheet__title"></p>
                <div class="p-photo-sheet__actions">
                    <a id="photo-sheet-edit" href="#" class="p-photo-sheet__btn p-photo-sheet__btn--primary">
                        <i class="fas fa-edit" aria-hidden="true"></i>
                        <span><?= htmlspecialchars($words->get('Edit') ?: 'Edit', ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                    <button type="button" id="photo-sheet-delete" class="p-photo-sheet__btn p-photo-sheet__btn--danger">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                        <span><?= htmlspecialchars($words->get('Delete') ?: 'Delete', ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                </div>
            </div>
        </div>

        <?php else: ?>
        <p class="p-profile-empty-hint" style="margin-top:1rem;"><?= $words->get('gallery.manage.empty.title') ?: 'No photos in this album yet.' ?></p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="p-gallery-albums-wrap">
        <?php $inManage = true;
            require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
        ?>
    </div>
<?php endif; ?>
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
    let hadUploads = false;

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

    /* Clean up ?tab= from URL after activation */
    if (location.search.includes('tab=') && history.replaceState) {
        history.replaceState(null, '', location.pathname + '#' + (initialHash || defaultTab));
    }


    /* ── Manage form (Photos tab) ── */
    const manageForm = document.getElementById('manage');
    if (manageForm) {
        const deleteImages  = document.getElementById('deleteImages');
        const moveImages    = document.getElementById('moveImages');
        const selectAll     = document.getElementById('selectAll');
        const checkboxes    = document.getElementsByName('imageId[]');
        const deleteOrMove  = document.getElementById('deleteOrMove');
        const existingRadio = document.getElementById('existingAlbum');  // may be null (no albums yet)
        const newRadio      = document.getElementById('newAlbum');
        const newAlbumTitle = document.getElementById('newAlbumTitle');
        const albumSelect   = document.getElementById('albums');

        /* Auto-select the "New" radio when user types in the text field */
        if (newAlbumTitle) {
            newAlbumTitle.addEventListener('input', () => {
                if (newRadio) newRadio.checked = true;
                if (existingRadio) existingRadio.checked = false;
                updateManageButtons();
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
            const existingChosen = existingRadio && existingRadio.checked;
            const newChosen      = newRadio && newRadio.checked;
            if (!existingChosen && !newChosen) {
                alert(<?= json_encode($words->get('gallery.move.not.possible')) ?>);
                return;
            }
            if (existingChosen && albumSelect && albumSelect.value === '') {
                alert(<?= json_encode($words->get('gallery.no.album.selected')) ?>);
                return;
            }
            if (newChosen && newAlbumTitle && newAlbumTitle.value.trim() === '') {
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

        if (albumSelect) {
            albumSelect.addEventListener('change', () => {
                if (albumSelect.value !== '') {
                    if (existingRadio) existingRadio.checked = true;
                    if (newRadio) newRadio.checked = false;
                }
                updateManageButtons();
            });
        }

        if (existingRadio) existingRadio.addEventListener('change', updateManageButtons);
        if (newRadio)      newRadio.addEventListener('change', updateManageButtons);

        function updateManageButtons() {
            const hasSelection      = Array.from(checkboxes).some(cb => cb.checked);
            if (deleteImages) deleteImages.disabled = !hasSelection;

            const existingAlbumChosen = existingRadio && existingRadio.checked && albumSelect && albumSelect.value !== '';
            const newAlbumChosen      = newRadio && newRadio.checked && newAlbumTitle && newAlbumTitle.value.trim() !== '';
            const albumReady          = existingAlbumChosen || newAlbumChosen;

            if (moveImages) moveImages.disabled = !(hasSelection && albumReady);

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

    /* ── Photo action sheet ── */
    const photoSheet = document.getElementById('photo-action-sheet');
    if (photoSheet) {
        const sheetPanel   = photoSheet.querySelector('.p-photo-sheet__panel');
        const sheetBackdrop= photoSheet.querySelector('.p-photo-sheet__backdrop');
        const sheetClose   = photoSheet.querySelector('.p-photo-sheet__close');
        const sheetThumb   = document.getElementById('photo-sheet-thumb');
        const sheetTitle   = document.getElementById('photo-sheet-title');
        const sheetEdit    = document.getElementById('photo-sheet-edit');
        const sheetDelete  = document.getElementById('photo-sheet-delete');
        const lblDelete    = <?= json_encode($words->get('Delete') ?: 'Delete') ?>;
        const lblConfirm   = <?= json_encode($words->get('gallery.photo.delete.confirm') ?: 'Confirm delete') ?>;
        const lblDeleting  = <?= json_encode($words->get('gallery.photo.deleting') ?: 'Deleting…') ?>;

        let currentItem = null;
        let confirmPending = false;

        function openSheet(item) {
            currentItem = item;
            confirmPending = false;
            sheetDelete.disabled = false;
            sheetDelete.classList.remove('p-photo-sheet__btn--danger-confirm');
            sheetDelete.innerHTML = '<i class="fas fa-trash" aria-hidden="true"></i><span>' + lblDelete + '</span>';
            sheetThumb.src  = item.dataset.photoThumb;
            sheetThumb.alt  = item.dataset.photoTitle;
            sheetTitle.textContent = item.dataset.photoTitle;
            sheetEdit.href  = item.dataset.photoEdit;
            photoSheet.setAttribute('aria-hidden', 'false');
            photoSheet.classList.add('p-photo-sheet--open');
            document.body.style.overflow = 'hidden';
        }

        function closeSheet() {
            photoSheet.setAttribute('aria-hidden', 'true');
            photoSheet.classList.remove('p-photo-sheet--open');
            document.body.style.overflow = '';
            confirmPending = false;
            currentItem = null;
        }

        document.querySelectorAll('.p-gallery-photo-item__menu-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                openSheet(btn.closest('.p-gallery-photo-item'));
            });
        });

        sheetClose.addEventListener('click', closeSheet);
        sheetBackdrop.addEventListener('click', closeSheet);

        sheetDelete.addEventListener('click', () => {
            if (!confirmPending) {
                confirmPending = true;
                sheetDelete.classList.add('p-photo-sheet__btn--danger-confirm');
                sheetDelete.innerHTML = '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i><span>' + lblConfirm + '</span>';
                return;
            }
            const item = currentItem;
            sheetDelete.disabled = true;
            sheetDelete.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i><span>' + lblDeleting + '</span>';
            fetch(item.dataset.photoDelete, { credentials: 'same-origin' })
                .then(() => {
                    item.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => item.remove(), 260);
                    closeSheet();
                })
                .catch(() => {
                    sheetDelete.disabled = false;
                    sheetDelete.classList.remove('p-photo-sheet__btn--danger-confirm');
                    sheetDelete.innerHTML = '<i class="fas fa-trash" aria-hidden="true"></i><span>' + lblDelete + '</span>';
                    confirmPending = false;
                });
        });

        /* Swipe down to dismiss */
        let touchStartY = 0;
        sheetPanel.addEventListener('touchstart', e => { touchStartY = e.touches[0].clientY; }, { passive: true });
        sheetPanel.addEventListener('touchmove', e => { if (e.touches[0].clientY - touchStartY > 60) closeSheet(); }, { passive: true });
    }

    /* ── Delete album modal ── */
    const deleteAlbumModal = document.getElementById('delete-album-modal');
    const deleteAlbumConfirm = document.getElementById('delete-album-confirm');
    const deleteAlbumCancel = document.getElementById('delete-album-cancel');
    const deleteAlbumBtn = document.querySelector('.p-gallery-set-nav__delete');

    if (deleteAlbumBtn && deleteAlbumModal) {
        const openModal = () => {
            deleteAlbumModal.setAttribute('aria-hidden', 'false');
            deleteAlbumModal.classList.add('c-confirm-modal--open');
        };
        const closeModal = () => {
            deleteAlbumModal.setAttribute('aria-hidden', 'true');
            deleteAlbumModal.classList.remove('c-confirm-modal--open');
        };

        deleteAlbumBtn.addEventListener('click', openModal);
        deleteAlbumCancel.addEventListener('click', closeModal);
        deleteAlbumModal.querySelector('.c-confirm-modal__backdrop').addEventListener('click', closeModal);

        deleteAlbumConfirm.addEventListener('click', () => {
            const setId = deleteAlbumBtn.dataset.setId;
            deleteAlbumConfirm.disabled = true;
            deleteAlbumConfirm.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i>';
            fetch('gallery/show/sets/' + setId + '/delete/true', { credentials: 'same-origin' })
                .then(() => { window.location.href = '/gallery/manage?tab=albums'; })
                .catch(() => { window.location.href = '/gallery/manage?tab=albums'; });
        });
    }

})();
</script>
