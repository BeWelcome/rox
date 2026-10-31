// The member's own photos on /gallery/manage (templates/gallery/manage.html.twig): add photos (Add, the first
// tile, or drop them on the page), open one in the sheet to change its title, text or album (arrows and swipe
// go to the next one), pick several with Select to move or delete them, and create, rename or delete albums.
// Every change is a small JSON call; the page updates in place.

const root = document.querySelector('.js-gm');

if (root) {
    const data = root.dataset;
    const grid = root.querySelector('.js-gm-grid');
    const empty = root.querySelector('.js-gm-empty');
    const bar = root.querySelector('.js-gm-bar');
    const sheet = root.querySelector('.js-gm-panel');
    const dialog = root.querySelector('.js-gm-dialog');
    const activeAlbum = data.album ? parseInt(data.album, 10) : null;
    let selecting = false;
    let current = null;

    // Texts with a number: data-t-<name>-0, -1 and -n (with {count}); dataset keeps "-0" and "-1" as they are
    const plural = (name, count) => {
        let text = data['t' + name + 'N'];
        if (count === 0 && data['t' + name + '-0'] !== undefined) {
            text = data['t' + name + '-0'];
        } else if (count === 1) {
            text = data['t' + name + '-1'];
        }
        return (text || '').replace('{count}', count);
    };
    const albumOf = (tile) => (tile.dataset.album === '' ? null : parseInt(tile.dataset.album, 10));
    const withId = (url, id) => url.replace(/\/0(?=$|\/)/, '/' + id);
    const thumb = (id, big) => {
        const url = new URL(big ? data.urlBig : data.urlThumb, window.location.origin);
        url.searchParams.set('id', id);
        return url.pathname + url.search;
    };
    const tiles = () => Array.from(grid.querySelectorAll('.js-gm-tile'));
    const ready = () => tiles().filter((tile) => tile.dataset.id && !tile.classList.contains('is-uploading') && !tile.classList.contains('is-failed'));
    const selected = () => tiles().filter((tile) => tile.classList.contains('is-selected'));

    function send(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-Token': data.token, Accept: 'application/json'},
            credentials: 'same-origin',
            body: JSON.stringify(body || {}),
        }).then((response) => {
            if (!response.ok) {
                throw new Error(String(response.status));
            }
            return response.json();
        });
    }

    function failed() {
        window.alert(data.tFailed);
    }

    // Counts in the head, on the "All" tile and in the album bar; the empty line and the Select button
    function refresh() {
        const count = ready().length;
        empty.classList.toggle('d-none', tiles().length > 0);
        root.querySelector('.js-gm-select').classList.toggle('d-none', count === 0);
        if (activeAlbum === null) {
            root.querySelector('.js-gm-count').textContent = plural('Count', count);
            root.querySelector('.js-gm-total').textContent = count;
        } else {
            const photos = root.querySelector('.js-gm-album-photos');
            if (photos) {
                photos.textContent = plural('Count', count);
            }
        }
        if (count === 0 && selecting) {
            setSelecting(false);
        }
    }

    function bumpAlbum(id, change) {
        if (id === null || Number.isNaN(id)) {
            return;
        }
        const count = root.querySelector('.p-gm-albums [data-album-id="' + id + '"] .js-gm-album-count');
        if (count) {
            count.textContent = Math.max(0, parseInt(count.textContent, 10) + change);
        }
    }

    function bumpTotal(change) {
        if (activeAlbum === null) {
            return;
        }
        const total = root.querySelector('.js-gm-total');
        total.textContent = Math.max(0, parseInt(total.textContent, 10) + change);
        root.querySelector('.js-gm-count').textContent = plural('Count', parseInt(total.textContent, 10));
    }

    function addTile() {
        const tile = document.createElement('li');
        tile.className = 'p-gm-tile js-gm-tile';
        tile.dataset.id = '';
        tile.dataset.title = '';
        tile.dataset.description = '';
        tile.dataset.album = activeAlbum === null ? '' : activeAlbum;
        tile.innerHTML = '<button type="button" class="p-gm-tile__open js-gm-open"><img alt=""></button>'
            + '<span class="p-gm-tile__check" aria-hidden="true"><i class="fas fa-check"></i></span>';
        grid.querySelector('.p-gm-grid__add').after(tile);
        return tile;
    }

    function setCaption(tile) {
        let caption = tile.querySelector('.js-gm-caption');
        if (!tile.dataset.title) {
            if (caption) {
                caption.remove();
            }
            return;
        }
        if (!caption) {
            caption = document.createElement('span');
            caption.className = 'p-gm-tile__caption js-gm-caption';
            tile.append(caption);
        }
        caption.textContent = tile.dataset.title;
    }

    function removeTiles(ids) {
        ids.forEach((id) => {
            const tile = grid.querySelector('.js-gm-tile[data-id="' + id + '"]');
            if (tile) {
                tile.classList.add('is-leaving');
                setTimeout(() => {
                    tile.remove();
                    refresh();
                }, 250);
            }
        });
    }

    // ── Adding photos: each one gets its tile at once, with a progress bar until it is saved ──
    const files = root.querySelector('.js-gm-files');

    function upload(file) {
        const tile = addTile();
        tile.classList.add('is-uploading');
        tile.querySelector('img').src = URL.createObjectURL(file);
        tile.insertAdjacentHTML('beforeend', '<span class="p-gm-tile__progress"><span></span></span>');
        const progress = tile.querySelector('.p-gm-tile__progress span');
        const fail = (message) => {
            tile.classList.remove('is-uploading');
            tile.classList.add('is-failed');
            const bubble = tile.querySelector('.p-gm-tile__progress');
            if (bubble) {
                bubble.remove();
            }
            tile.insertAdjacentHTML('beforeend', '<button type="button" class="p-gm-tile__error"><i class="fas fa-exclamation-circle"></i> <span></span></button>');
            tile.querySelector('.p-gm-tile__error span').textContent = message;
            tile.querySelector('.p-gm-tile__error').addEventListener('click', () => {
                tile.remove();
                refresh();
            });
            refresh();
        };
        refresh();

        if (!/^image\/(jpeg|png|gif)$/.test(file.type)) {
            fail(data.tNotImage);
            return;
        }
        if (file.size > parseInt(data.maxUpload, 10)) {
            fail(data.tTooBig);
            return;
        }

        const form = new FormData();
        form.append('file', file);
        if (activeAlbum !== null) {
            form.append('albumId', activeAlbum);
        }
        const request = new XMLHttpRequest();
        request.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) {
                progress.style.width = Math.round(event.loaded / event.total * 100) + '%';
            }
        });
        request.addEventListener('load', () => {
            let answer = null;
            try {
                answer = JSON.parse(request.responseText);
            } catch (error) {
                answer = null;
            }
            if (!answer || !answer.success) {
                fail(answer && answer.error ? answer.error : data.tFailed);
                return;
            }
            tile.dataset.id = answer.imageId;
            tile.classList.remove('is-uploading');
            tile.querySelector('.p-gm-tile__progress').remove();
            bumpAlbum(activeAlbum, 1);
            bumpTotal(1);
            refresh();
        });
        request.addEventListener('error', () => fail(data.tFailed));
        request.open('POST', data.urlUpload);
        request.send(form);
    }

    const uploadAll = (list) => Array.from(list).forEach(upload);

    files.addEventListener('change', () => {
        uploadAll(files.files);
        files.value = '';
    });

    // Drop photos anywhere on the page
    const drop = root.querySelector('.js-gm-drop');
    let dragDepth = 0;
    const hasFiles = (event) => event.dataTransfer && Array.from(event.dataTransfer.types || []).includes('Files');
    document.addEventListener('dragenter', (event) => {
        if (hasFiles(event)) {
            dragDepth += 1;
            drop.classList.add('is-visible');
        }
    });
    document.addEventListener('dragleave', (event) => {
        if (hasFiles(event)) {
            dragDepth = Math.max(0, dragDepth - 1);
            if (dragDepth === 0) {
                drop.classList.remove('is-visible');
            }
        }
    });
    document.addEventListener('dragover', (event) => {
        if (hasFiles(event)) {
            event.preventDefault();
        }
    });
    document.addEventListener('drop', (event) => {
        if (!hasFiles(event)) {
            return;
        }
        event.preventDefault();
        dragDepth = 0;
        drop.classList.remove('is-visible');
        uploadAll(event.dataTransfer.files);
    });

    // ── Small dialog: a name, "are you sure", or an album to pick ──
    const dialogForm = dialog.querySelector('.js-gm-dialog-form');
    const dialogInput = dialog.querySelector('.js-gm-dialog-input');
    const dialogAlbums = dialog.querySelector('.js-gm-dialog-albums');
    const dialogOk = dialog.querySelector('.js-gm-dialog-ok');
    let dialogDone = null;

    // kind: 'name' (an input), 'confirm' (a red button) or 'album' (album chips, no button)
    function ask(text, okLabel, kind, value) {
        return new Promise((resolve) => {
            dialog.querySelector('.js-gm-dialog-text').textContent = text;
            dialogOk.textContent = okLabel || '';
            dialog.dataset.kind = kind;
            dialogInput.classList.toggle('d-none', kind !== 'name');
            dialogAlbums.classList.toggle('d-none', kind !== 'album');
            dialogOk.classList.toggle('d-none', kind === 'album');
            dialogInput.value = value || '';
            dialog.classList.add('is-open');
            dialog.setAttribute('aria-hidden', 'false');
            dialogDone = resolve;
            if (kind === 'name') {
                setTimeout(() => dialogInput.focus(), 50);
            }
        });
    }

    function closeDialog(answer) {
        dialog.classList.remove('is-open');
        dialog.setAttribute('aria-hidden', 'true');
        if (dialogDone) {
            const done = dialogDone;
            dialogDone = null;
            done(answer);
        }
    }

    dialogForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (dialog.dataset.kind === 'name') {
            const name = dialogInput.value.trim();
            if (name === '') {
                dialogInput.focus();
                return;
            }
            closeDialog(name);
            return;
        }
        closeDialog(true);
    });
    dialog.querySelectorAll('.js-gm-dialog-cancel').forEach((button) => button.addEventListener('click', () => closeDialog(null)));
    dialogAlbums.addEventListener('click', (event) => {
        const chip = event.target.closest('.p-gm-chip');
        if (chip) {
            closeDialog({album: chip.dataset.album});
        }
    });

    function newAlbum() {
        return ask(data.tNewAlbum, data.tCreate, 'name').then((name) => (name ? send(data.urlAlbumCreate, {title: name}) : null));
    }

    // A new album goes into both chip lists and gets its tile in the album row
    function addAlbumEverywhere(album) {
        root.querySelectorAll('.js-gm-panel-albums, .js-gm-dialog-albums').forEach((list) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'p-gm-chip';
            chip.dataset.album = album.id;
            chip.innerHTML = '<i class="fas fa-folder" aria-hidden="true"></i> <span></span>';
            chip.querySelector('span').textContent = album.title;
            const before = list.classList.contains('js-gm-panel-albums') ? list.querySelector('[data-album="new"]') : list.querySelector('[data-album=""]');
            list.insertBefore(chip, before);
        });
        const item = document.createElement('a');
        item.className = 'p-gm-albums__item';
        item.href = data.urlPage + '?album=' + album.id;
        item.dataset.albumId = album.id;
        item.innerHTML = '<span class="p-gm-albums__thumb"><i class="fas fa-folder" aria-hidden="true"></i></span>'
            + '<span class="p-gm-albums__name"><span class="js-gm-album-title"></span> <small class="js-gm-album-count">0</small></span>';
        item.querySelector('.js-gm-album-title').textContent = album.title;
        root.querySelector('.js-gm-album-new').before(item);
    }

    // ── Albums ──
    root.querySelector('.js-gm-album-new').addEventListener('click', () => {
        newAlbum().then((album) => {
            if (album) {
                window.location.href = data.urlPage + '?album=' + album.id;
            }
        }).catch(failed);
    });

    const menu = root.querySelector('.p-gm-menu');
    if (menu) {
        const menuButton = menu.querySelector('.js-gm-menu');
        const setMenu = (open) => {
            menu.classList.toggle('is-open', open);
            menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        menuButton.addEventListener('click', (event) => {
            event.stopPropagation();
            setMenu(!menu.classList.contains('is-open'));
        });
        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target)) {
                setMenu(false);
            }
        });

        menu.querySelector('.js-gm-album-rename').addEventListener('click', () => {
            setMenu(false);
            const name = root.querySelector('.js-gm-album-name');
            ask(data.tRenameAlbum, data.tSave, 'name', name.textContent.trim()).then((title) => {
                if (!title) {
                    return;
                }
                send(withId(data.urlAlbum, activeAlbum), {title}).then((album) => {
                    name.textContent = album.title;
                    root.querySelectorAll('[data-album-id="' + activeAlbum + '"] .js-gm-album-title').forEach((label) => {
                        label.textContent = album.title;
                    });
                    root.querySelectorAll('.p-gm-chip[data-album="' + activeAlbum + '"]').forEach((chip) => {
                        chip.innerHTML = '<i class="fas fa-folder" aria-hidden="true"></i> <span></span>';
                        chip.querySelector('span').textContent = album.title;
                    });
                }).catch(failed);
            });
        });

        const deleteAlbum = menu.querySelector('.js-gm-album-delete');
        deleteAlbum.addEventListener('click', () => {
            setMenu(false);
            ask(data.tDeleteAlbum, deleteAlbum.textContent.trim(), 'confirm').then((yes) => {
                if (yes) {
                    send(withId(data.urlAlbum, activeAlbum) + '/delete').then(() => {
                        window.location.href = data.urlPage;
                    }).catch(failed);
                }
            });
        });
    }

    // ── One photo in the sheet ──
    const sheetForm = sheet.querySelector('.js-gm-panel-form');
    const sheetTitle = sheet.querySelector('.js-gm-panel-title');
    const sheetDescription = sheet.querySelector('.js-gm-panel-description');
    const sheetAlbums = sheet.querySelector('.js-gm-panel-albums');
    const sheetSave = sheet.querySelector('.js-gm-panel-save');
    let sheetAlbum = '';

    function pickChip(value) {
        sheetAlbum = value === null ? '' : String(value);
        sheetAlbums.querySelectorAll('.p-gm-chip').forEach((chip) => {
            chip.classList.toggle('is-active', chip.dataset.album === sheetAlbum);
        });
    }

    function openSheet(tile) {
        current = tile;
        const list = ready();
        const index = list.indexOf(tile);
        sheet.querySelector('.js-gm-panel-img').src = thumb(tile.dataset.id, true);
        sheet.querySelector('.js-gm-panel-counter').textContent = (index + 1) + ' / ' + list.length;
        sheet.querySelector('.js-gm-panel-prev').disabled = index <= 0;
        sheet.querySelector('.js-gm-panel-next').disabled = index >= list.length - 1;
        sheetTitle.value = tile.dataset.title;
        sheetDescription.value = tile.dataset.description;
        pickChip(tile.dataset.album);
        sheetSave.textContent = data.tSave;
        sheetSave.disabled = false;
        sheet.classList.add('is-open');
        sheet.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('p-gm-locked');
    }

    function closeSheet() {
        sheet.classList.remove('is-open');
        sheet.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('p-gm-locked');
        current = null;
    }

    function step(by) {
        if (!current) {
            return;
        }
        const list = ready();
        const next = list[list.indexOf(current) + by];
        if (next) {
            openSheet(next);
        }
    }

    sheet.querySelectorAll('.js-gm-panel-close').forEach((button) => button.addEventListener('click', closeSheet));
    sheet.querySelector('.js-gm-panel-prev').addEventListener('click', () => step(-1));
    sheet.querySelector('.js-gm-panel-next').addEventListener('click', () => step(1));

    // Swipe left or right on the photo
    const photo = sheet.querySelector('.js-gm-panel-photo');
    let touchX = null;
    photo.addEventListener('touchstart', (event) => {
        touchX = event.touches[0].clientX;
    }, {passive: true});
    photo.addEventListener('touchend', (event) => {
        if (touchX === null) {
            return;
        }
        const moved = event.changedTouches[0].clientX - touchX;
        touchX = null;
        if (Math.abs(moved) > 50) {
            step(moved < 0 ? 1 : -1);
        }
    });

    sheetAlbums.addEventListener('click', (event) => {
        const chip = event.target.closest('.p-gm-chip');
        if (!chip) {
            return;
        }
        if (chip.dataset.album !== 'new') {
            pickChip(chip.dataset.album);
            return;
        }
        newAlbum().then((album) => {
            if (album) {
                addAlbumEverywhere(album);
                pickChip(album.id);
            }
        }).catch(failed);
    });

    sheetForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!current) {
            return;
        }
        const tile = current;
        const album = sheetAlbum === '' ? null : parseInt(sheetAlbum, 10);
        const before = albumOf(tile);
        sheetSave.disabled = true;
        send(withId(data.urlPicture, tile.dataset.id), {
            title: sheetTitle.value,
            description: sheetDescription.value,
            album,
        }).then((picture) => {
            tile.dataset.title = picture.title;
            tile.dataset.description = picture.description;
            tile.dataset.album = picture.album === null ? '' : picture.album;
            setCaption(tile);
            if (before !== album) {
                bumpAlbum(before, -1);
                bumpAlbum(album, 1);
            }
            sheetSave.textContent = data.tSaved;
            setTimeout(closeSheet, 450);
            // Moved out of the album shown: it leaves the grid
            if (activeAlbum !== null && album !== activeAlbum) {
                removeTiles([picture.id]);
            }
        }).catch(() => {
            sheetSave.disabled = false;
            failed();
        });
    });

    sheet.querySelector('.js-gm-panel-delete').addEventListener('click', () => {
        if (!current) {
            return;
        }
        const tile = current;
        ask(data.tDeleteOne, sheet.querySelector('.js-gm-panel-delete').getAttribute('aria-label'), 'confirm').then((yes) => {
            if (!yes) {
                return;
            }
            send(data.urlDelete, {pictures: [parseInt(tile.dataset.id, 10)]}).then((answer) => {
                closeSheet();
                bumpAlbum(albumOf(tile), -1);
                bumpTotal(-1);
                removeTiles(answer.deleted);
            }).catch(failed);
        });
    });

    // ── Picking several photos ──
    function updateBar() {
        const count = selected().length;
        bar.querySelector('.js-gm-selected').textContent = plural('Selected', count);
        bar.classList.toggle('has-selection', count > 0);
    }

    function setSelecting(on) {
        selecting = on;
        root.classList.toggle('is-selecting', on);
        bar.classList.toggle('is-open', on);
        bar.setAttribute('aria-hidden', on ? 'false' : 'true');
        if (!on) {
            tiles().forEach((tile) => tile.classList.remove('is-selected'));
        }
        updateBar();
    }

    root.querySelector('.js-gm-select').addEventListener('click', () => setSelecting(true));
    root.querySelector('.js-gm-select-done').addEventListener('click', () => setSelecting(false));

    grid.addEventListener('click', (event) => {
        const open = event.target.closest('.js-gm-open');
        if (!open) {
            return;
        }
        const tile = open.closest('.js-gm-tile');
        if (!ready().includes(tile)) {
            return;
        }
        if (selecting) {
            tile.classList.toggle('is-selected');
            updateBar();
            return;
        }
        openSheet(tile);
    });

    bar.querySelector('.js-gm-move').addEventListener('click', () => {
        const picked = selected();
        if (picked.length === 0) {
            return;
        }
        ask(data.tMoveTo, '', 'album').then((answer) => {
            if (!answer) {
                return null;
            }
            if (answer.album === 'new') {
                return newAlbum().then((album) => {
                    if (album) {
                        addAlbumEverywhere(album);
                    }
                    return album ? album.id : undefined;
                });
            }
            return answer.album === '' ? null : parseInt(answer.album, 10);
        }).then((album) => {
            if (album === undefined) {
                return null;
            }
            const ids = picked.map((tile) => parseInt(tile.dataset.id, 10));
            return send(data.urlMove, {pictures: ids, album}).then(() => {
                picked.forEach((tile) => {
                    bumpAlbum(albumOf(tile), -1);
                    bumpAlbum(album, 1);
                    tile.dataset.album = album === null ? '' : album;
                    tile.classList.add('is-moved');
                    setTimeout(() => tile.classList.remove('is-moved'), 900);
                });
                if (activeAlbum !== null && album !== activeAlbum) {
                    removeTiles(ids);
                }
                setSelecting(false);
            });
        }).catch(failed);
    });

    bar.querySelector('.js-gm-delete-selected').addEventListener('click', () => {
        const picked = selected();
        if (picked.length === 0) {
            return;
        }
        const label = bar.querySelector('.js-gm-delete-selected').textContent.trim();
        const question = picked.length === 1 ? data.tDeleteOne : data.tDeleteMany.replace('{count}', picked.length);
        ask(question, label, 'confirm').then((yes) => {
            if (!yes) {
                return;
            }
            send(data.urlDelete, {pictures: picked.map((tile) => parseInt(tile.dataset.id, 10))}).then((answer) => {
                picked.forEach((tile) => bumpAlbum(albumOf(tile), -1));
                bumpTotal(-answer.deleted.length);
                removeTiles(answer.deleted);
                setSelecting(false);
            }).catch(failed);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (dialog.classList.contains('is-open')) {
            if (event.key === 'Escape') {
                closeDialog(null);
            }
            return;
        }
        if (sheet.classList.contains('is-open')) {
            if (event.key === 'Escape') {
                closeSheet();
            } else if (event.key === 'ArrowRight' && event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA') {
                step(1);
            } else if (event.key === 'ArrowLeft' && event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA') {
                step(-1);
            }
            return;
        }
        if (event.key === 'Escape' && selecting) {
            setSelecting(false);
        }
    });

    refresh();
}
