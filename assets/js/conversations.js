let options = document.getElementsByClassName('show_options');
let showMessages = document.getElementById('show_messages');
let showRequests = document.getElementById('show_requests');
let showInvitations = document.getElementById('show_invitations');
let showUnreadOnly = document.getElementById('show_unread_only');
let showStartedByMe = document.getElementById('show_started_by_me');
let showStartedBySomeoneElse = document.getElementById('show_started_by_someone_else');

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
    const messages = showMessages.checked;
    const requests = showRequests.checked;
    const invitations = showInvitations.checked;
    const unread = showUnreadOnly.checked;
    const startedByMe = showStartedByMe.checked;
    const startedBySomeoneElse = showStartedBySomeoneElse.checked;

    let parameters = '?page=1';
    if (!(messages && requests && invitations)) {
        if (messages) {
            parameters += '&messages=1'
        }
        if (requests) {
            parameters += '&requests=1'
        }
        if (invitations) {
            parameters += '&invitations=1'
        }
    }
    if (unread) {
        parameters += '&unread_only=1'
    }
    if (!(startedByMe && startedBySomeoneElse)) {
        if (startedByMe) {
            parameters += '&startedByMe=1'
        }
        if (startedBySomeoneElse) {
            parameters += '&startedBySomeone=1'
        }
    }

    parameters = parameters.replace('?&', '?');
    console.log(parameters);
    window.location.search = parameters;
}
