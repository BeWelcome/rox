import MicroModal from 'micromodal';

const editLanguages = document.querySelectorAll("[data-edit-language]");

editLanguages.forEach(editLanguage => {
    editLanguage.addEventListener("click", e => {
        editLanguages.forEach(editLanguage => {
            editLanguage.classList.remove('p-edit-header__lang-tab--active')
            editLanguage.setAttribute('aria-pressed', 'false')
        })

        const languages = document.querySelectorAll('[id^=profile-language-]')
        languages.forEach(language => {
            language.classList.add('u:hidden!')
        })

        const language = e.currentTarget.dataset.editLanguage
        const activeLanguage = document.getElementById("profile-language-" + language)
        const editLanguageButton = e.currentTarget

        activeLanguage.classList.remove('u:hidden!')
        editLanguageButton.classList.add("p-edit-header__lang-tab--active")
        editLanguageButton.setAttribute('aria-pressed', 'true')
    })
})

const deleteLanguages = document.querySelectorAll("[data-delete-language]")

deleteLanguages.forEach( deleteLanguage => {
    deleteLanguage.addEventListener('click', (e) => {
        const modalId = 'delete-' + e.currentTarget.dataset.deleteLanguage;
        MicroModal.show(modalId);
    })
})

// "Can you host?", the number of guests and the hosting interest: saved as soon as they
// change, like the old accommodation form did (ProfileController::setAccommodation...).
const post = (url, body) => fetch(url, {
    method: 'POST',
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
});

document.querySelectorAll('.js-pe-accommodation').forEach((card) => {
    const hostButtons = card.querySelectorAll('.js-pe-host');
    const interest = card.querySelector('.js-pe-interest');
    const guests = card.querySelector('.js-pe-guests');
    const steps = card.querySelectorAll('.js-pe-guests-step');

    const showHosting = () => {
        card.classList.toggle('is-not-hosting', card.dataset.accommodation === 'no');
    };
    const saveAccommodation = () => {
        if (!card.dataset.accommodation) {
            return;
        }
        post('/members/update/accommodation', {
            accommodation: card.dataset.accommodation,
            hostingInterest: interest ? +interest.value : 0,
        });
    };

    hostButtons.forEach((button) => {
        button.addEventListener('click', () => {
            card.dataset.accommodation = button.dataset.value;
            hostButtons.forEach((other) => {
                const active = other === button;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-checked', active ? 'true' : 'false');
            });
            showHosting();
            saveAccommodation();
        });
    });

    // Hosting interest: the tile opens a small window with 10 steps; "Done" saves, the
    // cross, Escape or a click outside leave it as it was.
    const tile = card.querySelector('.js-pe-interest-tile');
    if (tile && interest) {
        const names = JSON.parse(tile.dataset.names || '[]');
        const opener = tile.querySelector('.js-pe-interest-open');
        const pop = tile.querySelector('.js-pe-interest-pop');
        const stepButtons = tile.querySelectorAll('.js-pe-interest-step[data-value]:not([data-value="0"])');
        const popName = tile.querySelector('.js-pe-interest-pop-name');
        const popScore = tile.querySelector('.js-pe-interest-pop-score');
        const tileName = tile.querySelector('.js-pe-interest-name');
        const segments = tile.querySelectorAll('.p-pe-interest__seg');
        let picked = +interest.value;

        const showPicked = () => {
            popName.textContent = names[picked] || '';
            popName.classList.toggle('is-empty', picked === 0);
            popScore.textContent = picked ? picked + ' / 10' : '';
            stepButtons.forEach((button) => {
                const value = +button.dataset.value;
                button.classList.toggle('is-on', value <= picked);
                button.classList.toggle('is-picked', value === picked);
                button.setAttribute('aria-checked', value === picked ? 'true' : 'false');
            });
        };
        const showTile = () => {
            const value = +interest.value;
            tileName.textContent = names[value] || '';
            tileName.classList.toggle('is-empty', value === 0);
            segments.forEach((segment, index) => segment.classList.toggle('is-on', index < value));
        };
        const close = () => {
            pop.hidden = true;
            tile.classList.remove('is-open');
            opener.setAttribute('aria-expanded', 'false');
        };
        const open = () => {
            picked = +interest.value;
            showPicked();
            pop.hidden = false;
            tile.classList.add('is-open');
            opener.setAttribute('aria-expanded', 'true');
        };

        opener.addEventListener('click', () => (pop.hidden ? open() : close()));
        tile.querySelectorAll('.js-pe-interest-step').forEach((button) => {
            button.addEventListener('click', () => {
                picked = +button.dataset.value;
                showPicked();
            });
        });
        tile.querySelector('.js-pe-interest-cancel').addEventListener('click', close);
        tile.querySelector('.js-pe-interest-done').addEventListener('click', () => {
            if (picked !== +interest.value) {
                interest.value = picked;
                showTile();
                saveAccommodation();
            }
            close();
        });
        document.addEventListener('click', (event) => {
            if (!pop.hidden && !tile.contains(event.target)) {
                close();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !pop.hidden) {
                close();
                opener.focus();
            }
        });
    }

    if (guests) {
        const min = parseInt(guests.min, 10) || 1;
        const max = parseInt(guests.max, 10) || 20;
        let timer = null;
        const refresh = () => {
            const value = parseInt(guests.value, 10) || min;
            steps.forEach((step) => {
                const delta = parseInt(step.dataset.step, 10);
                step.disabled = (delta < 0 && value <= min) || (delta > 0 && value >= max);
            });
        };
        // A few quick clicks make one save
        const save = () => {
            clearTimeout(timer);
            timer = setTimeout(() => post('/members/update/maxguests', { maxGuests: parseInt(guests.value, 10) || min }), 400);
        };
        steps.forEach((step) => {
            step.addEventListener('click', () => {
                const value = (parseInt(guests.value, 10) || min) + parseInt(step.dataset.step, 10);
                guests.value = Math.min(max, Math.max(min, value));
                refresh();
                save();
            });
        });
        guests.addEventListener('change', () => {
            guests.value = Math.min(max, Math.max(min, parseInt(guests.value, 10) || min));
            refresh();
            save();
        });
        refresh();
    }

    showHosting();
});

// The cog of the head: a short menu (account, preferences, my data); each one opens in
// the side panel (js-contact-open), which closes the menu.
document.querySelectorAll('.js-pe-settings').forEach((settings) => {
    const button = settings.querySelector('.js-pe-settings-open');
    const menu = settings.querySelector('.js-pe-settings-menu');
    const show = (open) => {
        menu.hidden = !open;
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    button.addEventListener('click', () => show(menu.hidden));
    menu.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            show(false);
        }
    });
    document.addEventListener('click', (event) => {
        if (!menu.hidden && !settings.contains(event.target)) {
            show(false);
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menu.hidden) {
            show(false);
            button.focus();
        }
    });
});

// The eyes of the account block: show or hide the name, the age, the gender to the other
// members. There is no call for that alone: the account form is loaded, the box switched
// and the form sent as it is (with its token). If it fails, the eye goes back.
document.querySelectorAll('.js-pe-account').forEach((account) => {
    const url = account.dataset.accountUrl;

    const paint = (field, shown) => {
        document.querySelectorAll('.js-pe-seen[data-field="' + field + '"]').forEach((eye) => {
            const text = shown ? account.dataset.shown : account.dataset.hidden;
            eye.classList.toggle('is-shown', shown);
            eye.setAttribute('aria-pressed', shown ? 'true' : 'false');
            eye.title = text;
            eye.setAttribute('aria-label', text);
        });
    };

    const save = async (field, shown) => {
        const page = await fetch(url, { credentials: 'same-origin' });
        if (!page.ok) {
            throw new Error(page.status);
        }
        const doc = new DOMParser().parseFromString(await page.text(), 'text/html');
        const box = doc.querySelector('input[type="checkbox"][name$="[' + field + ']"]');
        const form = box ? box.form : null;
        if (!form) {
            throw new Error('no form');
        }
        box.checked = shown;
        const data = new FormData(form);
        const sent = await fetch(form.getAttribute('action') || url, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
        });
        // Saved: the form sends to the profile. Shown again (still /account): refused.
        if (!sent.ok || new URL(sent.url).pathname === new URL(url, window.location.href).pathname) {
            throw new Error('refused');
        }
    };

    account.querySelectorAll('.js-pe-seen').forEach((eye) => {
        eye.addEventListener('click', async () => {
            if (eye.classList.contains('is-busy')) {
                return;
            }
            const field = eye.dataset.field;
            const shown = !eye.classList.contains('is-shown');
            paint(field, shown);
            eye.classList.add('is-busy');
            try {
                await save(field, shown);
            } catch (error) {
                paint(field, !shown);
                eye.classList.add('is-failed');
                setTimeout(() => eye.classList.remove('is-failed'), 1200);
            } finally {
                eye.classList.remove('is-busy');
            }
        });
    });
});
