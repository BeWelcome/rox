require('ekko-lightbox');

document.getElementById("language-switch").addEventListener("change", e => {
    const languages = document.querySelectorAll('[id^=profile-language-]');
    languages.forEach(language => {
        language.style.display = 'none';
    })
    const current = document.getElementById("profile-language-" + e.target.value);
    current.style.display = '';
})
