require('ekko-lightbox');

const languageSwitch = document.getElementById("language-switch");

languageSwitch.addEventListener("change", e => {
    const languages = document.querySelectorAll('[id^=profile-language-]');
    languages.forEach(language => {
        language.classList.add('u:hidden!');
    })
    const current = document.getElementById("profile-language-" + e.target.value);
    current.classList.remove('u:hidden!')
})
