import { Tooltip } from 'bootstrap';

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new Tooltip(el));

document.querySelectorAll('.p-lang-pill').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[id^=profile-language-]').forEach(el => el.classList.add('u:hidden!'));
        document.getElementById('profile-language-' + btn.dataset.lang).classList.remove('u:hidden!');
        document.querySelectorAll('.p-lang-pill').forEach(b => b.classList.remove('p-lang-pill--active'));
        btn.classList.add('p-lang-pill--active');
    });
});
