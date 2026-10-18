import { Tooltip } from 'bootstrap';

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new Tooltip(el));

const langSwitch = document.querySelector('.p-lang-switch');

if (langSwitch) {
    const options = langSwitch.querySelectorAll('.p-lang-option');
    options.forEach(option => {
        option.addEventListener('click', () => {
            const lang = option.dataset.lang;
            document.querySelectorAll('[id^=profile-language-]').forEach(el => el.classList.add('u:hidden!'));
            document.getElementById('profile-language-' + lang)?.classList.remove('u:hidden!');
            options.forEach(o => o.classList.remove('p-lang-option--active'));
            option.classList.add('p-lang-option--active');
            langSwitch.querySelector('[data-lang-current-name]').textContent = option.textContent;
            langSwitch.querySelector('[data-lang-current-code]').textContent = lang.toUpperCase();
        });
    });
}
