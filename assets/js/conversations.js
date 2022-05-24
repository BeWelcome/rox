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
