// The fields of the form that holds the strength meter: the login form of the top bar has
// a password field too (.js-password-input), and comes first in the page.
const passwordStrength = document.querySelector('.js-password-strength');
const scope = (passwordStrength && passwordStrength.closest('form')) || document;
const passwordField = scope.querySelector('.js-password-input');
const username = scope.querySelector('.js-username') || document.querySelector('.js-username');
const email = scope.querySelector('.js-email-address') || document.querySelector('.js-email-address');

function resetBackgroundColor() {
    for (let i = 0; i < 5; i++) {
        passwordStrength.children.item(i).classList.add('u:bg-gray-300');
        passwordStrength.children.item(i).classList.remove('u:bg-red-700');
        passwordStrength.children.item(i).classList.remove('u:bg-bewelcome');
        passwordStrength.children.item(i).classList.remove('u:bg-bewelcome-dark');
        passwordStrength.children.item(i).classList.remove('u:bg-green-600');
        passwordStrength.children.item(i).classList.remove('u:bg-green-800');
    }
}

// The check that waits for its answer, if any: a new one, or sending the form, ends it.
let pending = null;

async function getPasswordScore() {
    // Collect form data (username, email address and password)
    // Send to server to calculate score
    // Change colors
    if (pending) {
        pending.abort();
    }
    const check = new AbortController();
    pending = check;
    const formData = new FormData();
    formData.append('username', username ? username.value : '');
    formData.append('email', email ? email.value : '');
    formData.append('password', passwordField.value);

    let score = 0;
    try {
        const response = await fetch("/password/check/", {
            method: "POST",
            // Set the FormData instance as the request body
            body: formData,
            signal: check.signal,
        });
        const json = await response.json();
        score = json.score;
    } catch (e) {
        if (check.signal.aborted) {
            // overtaken by a newer check, or the form was sent: nothing to show
            return;
        }
        console.error(e);
    }
    if (pending === check) {
        pending = null;
    }

    let backgroundColor;
    switch(score) {
        case 0: backgroundColor = 'u:bg-red-700'; break;
        case 1: backgroundColor = 'u:bg-bewelcome-dark'; break;
        case 2: backgroundColor = 'u:bg-bewelcome'; break;
        case 3: backgroundColor = 'u:bg-green-600'; break;
        case 4: backgroundColor = 'u:bg-green-800'; break;
    }

    resetBackgroundColor();
    for (let i = 0; i < 5; i++) {
        if (i <= score) {
            passwordStrength.children.item(i).classList.remove('u:bg-gray-300');
            passwordStrength.children.item(i).classList.add(backgroundColor);
        }
    }
}

let timeout;

if (passwordField && passwordStrength) {
    // "input" also catches a pasted or filled in password, not only keys
    passwordField.addEventListener('input', () => {
        clearTimeout(timeout);
        if (passwordField.value == '') {
            resetBackgroundColor();
        } else {
            timeout = setTimeout(() => { getPasswordScore(); }, 500);
        }
    });

    if (passwordField.value != '') {
        getPasswordScore();
    }

    // Sending the form ends a check that still waits and the one about to start: the page is leaving, and an
    // answer that comes after the form's would only get in its way.
    const form = passwordField.closest('form');
    if (form) {
        form.addEventListener('submit', () => {
            clearTimeout(timeout);
            if (pending) {
                pending.abort();
            }
        });
    }
}
