let options = document.getElementsByClassName(' js-switch-option');
const showUnreadOnly = document.getElementById('show_unread_only');
const switchInitiator = document.getElementById('initiator');

for(let i=0; i < options.length; i++)
{
    options[i].addEventListener('change',
        function () {
            setTimeout(updateConversations, 500);
        }
    );
}

function updateConversations()
{
    const unread = showUnreadOnly.checked;
    const initiator = switchInitiator.value;

    let parameters = '?page=1';
    if (unread) {
        parameters += '&unread_only=1'
    }
    parameters += '&initiator=' + initiator;

    parameters = parameters.replace('?&', '?');
    window.location.search = parameters;
}

const checkAll = document.getElementById('checkAll');
const checkAllLabel = document.querySelector('label[for="checkAll"]');
const checkAllLabelText = document.getElementById('checkAllLabelText').value;
const uncheckAllLabelText = document.getElementById('uncheckAllLabelText').value;
checkAll.addEventListener('click', (e) => {
    const checkboxes = document.querySelectorAll('input[type=checkbox].checkable');
    checkboxes.forEach((checkbox) => {checkbox.checked = checkAll.checked;
    });
    if (checkAll.checked) {
        checkAllLabel.innerText = uncheckAllLabelText;
    } else {
        checkAllLabel.innerText = checkAllLabelText;
    }
})
