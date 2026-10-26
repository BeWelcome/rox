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

// Phones: "About me" is cut after a few lines; "Read more" shows the whole text.
const aboutUpdates = [];
document.querySelectorAll('.js-pro-about').forEach(about => {
    const more = about.parentElement.querySelector('.js-pro-about-more');
    if (!more) {
        return;
    }
    const update = () => {
        if (!about.classList.contains('is-open')) {
            more.hidden = about.scrollHeight <= about.clientHeight + 1;
        }
    };
    more.addEventListener('click', () => {
        about.classList.add('is-open');
        more.hidden = true;
    });
    aboutUpdates.push(update);
    update();
});
const updateAbout = () => aboutUpdates.forEach(update => update());
window.addEventListener('resize', updateAbout);
// Another profile language shown: its text is measured once visible.
document.addEventListener('click', event => {
    if (event.target.closest('.p-lang-option')) {
        window.requestAnimationFrame(updateAbout);
    }
});
