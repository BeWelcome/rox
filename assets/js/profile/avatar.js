import Croppie from 'croppie';
import 'croppie/croppie.css';

/*
 * The profile picture (templates/profile/edit/avatar.html.twig): the photo in a round
 * frame, dragged and zoomed with Croppie (its zoom slider is moved under the photo), a
 * rotate button, the gallery's photos and a file to pick another one. "Save photo" sends
 * a 500 px square; then the edit page is loaded again (the whole window when the page is
 * in the side panel of the edit page).
 */
const member = document.getElementById('member').value;
const avatarContainer = document.getElementById('avatar_container');
const stage = avatarContainer.parentElement;
const editUrl = '/members/' + member + '/edit';

// The round frame: as big as the stage allows, with a margin around it. Started once the
// stage has its size (the side panel may still be sliding in).
let croppie = null;
let zoomer = null;

// The part of the slider already passed shows in orange
const paintZoomer = () => {
    if (!zoomer) {
        return;
    }
    const min = parseFloat(zoomer.min) || 0;
    const max = parseFloat(zoomer.max) || 1;
    const value = parseFloat(zoomer.value) || 0;
    const percent = max > min ? ((value - min) / (max - min)) * 100 : 0;
    zoomer.style.setProperty('--fill', percent + '%');
};

const start = () => {
    const width = stage.clientWidth;
    const height = stage.clientHeight;
    const size = Math.max(160, Math.min(width, height) - 56);
    try {
        croppie = new Croppie(avatarContainer, {
            url: '/members/avatar/' + member + '/original',
            viewport: { width: size, height: size, type: 'circle' },
            boundary: { width, height },
            enableOrientation: true,
            showZoomer: true,
        });
    } catch (error) {
        console.error('[avatar] the photo frame could not start', error);
        return;
    }

    // Croppie's zoom slider, between the two zoom icons
    const zoomHolder = document.querySelector('.js-avatar-zoom');
    const zoomerWrap = avatarContainer.querySelector('.cr-slider-wrap');
    if (zoomHolder && zoomerWrap) {
        zoomHolder.appendChild(zoomerWrap);
    }
    zoomer = zoomHolder ? zoomHolder.querySelector('.cr-slider') : null;
    avatarContainer.addEventListener('update', paintZoomer);
    if (zoomer) {
        zoomer.addEventListener('input', paintZoomer);
    }
};

const startWhenSized = () => {
    if (stage.clientWidth > 0 && stage.clientHeight > 0) {
        start();
        return;
    }
    const observer = new ResizeObserver(() => {
        if (stage.clientWidth > 0 && stage.clientHeight > 0) {
            observer.disconnect();
            start();
        }
    });
    observer.observe(stage);
};
startWhenSized();

const bind = (url) => (croppie ? croppie.bind({ url }).then(paintZoomer).catch((error) => console.error('[avatar] the photo could not be shown', error)) : null);

document.getElementById('avatar-rotate').addEventListener('click', () => croppie && croppie.rotate(-90));

document.querySelectorAll('[data-image-id]').forEach((image) => {
    image.addEventListener('click', () => {
        document.querySelectorAll('[data-image-id].is-active').forEach((other) => other.classList.remove('is-active'));
        image.classList.add('is-active');
        bind('gallery/img?id=' + image.dataset.imageId);
    });
});

document.getElementById('avatar-file').addEventListener('change', (event) => {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (loaded) => bind(loaded.target.result);
        reader.readAsDataURL(input.files[0]);
    }
});

const save = document.getElementById('avatar-update');
save.addEventListener('click', () => {
    if (!croppie) {
        return;
    }
    save.disabled = true;
    croppie.result({
        type: 'rawcanvas',
        circle: false,
        size: { width: 500, height: 500 },
        format: 'png',
    }).then((canvas) => {
        const formData = new FormData();
        formData.append('avatar', canvas.toDataURL());

        return fetch('/members/uploadavatar', {
            method: 'POST',
            body: formData,
        });
    }).then((response) => {
        if (response.status !== 200) {
            throw new Error(response.status + ' ' + response.statusText);
        }
        if (window.parent !== window) {
            window.parent.postMessage({ type: 'bw-embed-leave', url: editUrl }, window.location.origin);
        } else {
            window.location.href = editUrl;
        }
    }).catch((error) => {
        save.disabled = false;
        console.log('something went wrong: ' + error.message);
    });
});
