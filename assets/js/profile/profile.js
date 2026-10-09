require('ekko-lightbox');

// The comments page loads this bundle too but has no language switch.
const languageSwitch = document.getElementById("language-switch");
languageSwitch?.addEventListener("change", e => {
    const languages = document.querySelectorAll('[id^=profile-language-]');
    languages.forEach(language => {
        language.style.display = 'none';
    })
    const current = document.getElementById("profile-language-" + e.target.value);
    current.style.display = '';
})
