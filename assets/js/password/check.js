const passwordField = document.querySelector('.js-password-input');
const passwordStrength = document.querySelector('.js-password-strength');
const username = document.querySelector('.js-username');
const email = document.querySelector('.js-email-address');

function resetBackgroundColor() {
    for (let i = 0; i < 5; i++) {
        passwordStrength.children.item(i).classList.add('u-bg-gray-20');
        passwordStrength.children.item(i).classList.remove('u-bg-red');
        passwordStrength.children.item(i).classList.remove('u-bg-bewelcome');
        passwordStrength.children.item(i).classList.remove('u-bg-bewelcome-dark');
        passwordStrength.children.item(i).classList.remove('u-bg-green');
        passwordStrength.children.item(i).classList.remove('u-bg-green-dark');
    }
}
async function getPasswordScore() {
    // Collect form data (username, email address and password)
    // Send to server to calculate score
    // Change colors
    const formData = new FormData();
    formData.append('username', username.value);
    formData.append('email', email.value);
    formData.append('password', passwordField.value);

    let score = 0;
    try {
        const response = await fetch("/password/check/", {
            method: "POST",
            // Set the FormData instance as the request body
            body: formData,
        });
        const json = await response.json();
        score = json.score;
    } catch (e) {
        console.error(e);
    }

    let backgroundColor;
    switch(score) {
        case 0: backgroundColor = 'u-bg-red'; break;
        case 1: backgroundColor = 'u-bg-bewelcome-dark'; break;
        case 2: backgroundColor = 'u-bg-bewelcome'; break;
        case 3: backgroundColor = 'u-bg-green'; break;
        case 4: backgroundColor = 'u-bg-green-dark'; break;
    }

    resetBackgroundColor();
    for (let i = 0; i < 5; i++) {
        if (i <= score) {
            passwordStrength.children.item(i).classList.remove('u-bg-gray-20');
            passwordStrength.children.item(i).classList.add(backgroundColor);
        }
    }
}

let timeout;

passwordField.addEventListener('keyup', () => {
    clearTimeout(timeout);
    if (passwordField.value == '') {
        for (let i = 0; i < 5; i++) {
            resetBackgroundColor();
        }
    } else {
        timeout = setTimeout(() => { getPasswordScore(); }, 500);
    }
});

if (passwordField.value != '') {
    g
}
