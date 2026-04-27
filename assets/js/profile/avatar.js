import Croppie from 'croppie';
import 'croppie/croppie.css';

const member = document.getElementById('member').value
const avatarContainer = document.getElementById('avatar_container')

const croppie = new Croppie(avatarContainer, {
    url: '/members/avatar/' + member + '/original',
    viewport: {
        width: avatarContainer.clientWidth,
        height: avatarContainer.clientHeight,
        type: 'circle'
    },
})

const images = document.querySelectorAll('[data-image-id]');

images.forEach(image => {
    image.addEventListener('click', function() {
        updateCroppieImage(this.dataset.imageId)
    })
})

function updateCroppieImage(image) {
    croppie.bind({url: 'gallery/img?id=' + image})
}

const avatarFile = document.getElementById('avatar-file')
const avatarUpdate = document.getElementById('avatar-update')

avatarFile.addEventListener('change', function(e) {
    const input = e.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            // document.querySelector('.upload-demo').classList.add('ready');
            croppie.bind({
                url: e.target.result
            }).then(function(){
                console.log('bound');
            });

        }

        reader.readAsDataURL(input.files[0]);
    }
})

avatarUpdate.addEventListener('click', function() {
    croppie.result({
        type: 'rawcanvas',
        circle: false,
        size: { width: 500, height: 500 },
        format: 'png'
    }).then(function (canvas) {
        const formData = new FormData();
        formData.append('avatar', canvas.toDataURL());

        fetch('/members/uploadavatar', {
            method: 'POST',
            body: formData,
        }).then((response) => {
            // Update local instances as well.
            if (response.status === 200) {
                location.href = '/members/' + member + '/edit';
            } else {
                console.log('something went wrong: ' + response.status + ' ' + response.statusText + '')
            }
        })
    })
})
