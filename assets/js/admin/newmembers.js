/*
 * The new members (templates/admin/newmembers/list.html.twig): an added country filters at once, members are selected
 * for the standard greeting (the dialog lists them and posts to the local or the global greeting), a single greeting
 * opens the message in a new tab and shows as sent here, and a status's Save is enabled once the status changed.
 */
const LIMIT = 50;

document.querySelectorAll('.js-newmembers-add').forEach((select) => {
    select.addEventListener('change', () => {
        if (select.value !== '') {
            select.form.requestSubmit();
        }
    });
});

document.querySelectorAll('.js-newmembers-greeting').forEach((form) => {
    form.addEventListener('submit', () => {
        // after the browser took the form to the new tab
        setTimeout(() => {
            const sent = document.createElement('span');
            sent.className = 'c-newmembers__sent';
            sent.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i>';
            sent.append(form.dataset.sent);
            form.replaceWith(sent);
        });
    });
});

document.querySelectorAll('.js-newmembers-status').forEach((form) => {
    const select = form.querySelector('select');
    const save = form.querySelector('button');
    const initial = select.value;
    const update = () => {
        const changed = select.value !== initial;
        save.disabled = !changed;
        save.classList.toggle('is-changed', changed);
    };
    select.addEventListener('change', update);
    update();
});

const picks = [...document.querySelectorAll('.js-newmembers-pick')];
const all = document.querySelector('.js-newmembers-all');
const allLabel = document.querySelector('.js-newmembers-all-label');
const open = document.querySelector('.js-newmembers-open');
const dialog = document.querySelector('.js-newmembers-dialog');

const selected = () => picks.filter((pick) => pick.checked);

const updateSelection = () => {
    const count = selected().length;
    if (all) {
        all.checked = count > 0 && count === picks.length;
        all.indeterminate = count > 0 && count < picks.length;
        allLabel.textContent = count === 0 ? allLabel.dataset.none : allLabel.dataset.some.replace('%count%', count);
    }
    if (open) {
        open.disabled = count === 0;
        open.querySelector('span').textContent = count === 0 ? open.dataset.none : open.dataset.some.replace('%count%', count);
    }
};

picks.forEach((pick) => {
    pick.addEventListener('change', () => {
        if (pick.checked && selected().length > LIMIT) {
            pick.checked = false;
        }
        updateSelection();
    });
});

all?.addEventListener('change', () => {
    picks.forEach((pick, index) => {
        pick.checked = all.checked && index < LIMIT;
    });
    updateSelection();
});

if (dialog) {
    const form = dialog.querySelector('form');
    const title = dialog.querySelector('.c-newmembers-dialog__title');
    const chips = dialog.querySelector('.js-newmembers-chips');
    const submit = dialog.querySelector('.js-newmembers-submit');

    open.addEventListener('click', () => {
        const members = selected();
        const counted = (element) => (members.length === 1 ? element.dataset.one : element.dataset.label.replace('%count%', members.length));
        title.textContent = counted(title);
        submit.textContent = counted(submit);
        chips.replaceChildren(...members.map((pick) => {
            const chip = document.createElement('span');
            chip.className = 'c-newmembers-dialog__chip';
            const avatar = pick.closest('.js-new-member').querySelector('.c-newmembers__avatar');
            const img = document.createElement('img');
            img.src = avatar.src;
            img.alt = '';
            img.width = 20;
            img.height = 20;
            chip.append(img, pick.value);

            return chip;
        }));
        dialog.showModal();
    });

    dialog.querySelectorAll('.js-newmembers-close').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    dialog.querySelectorAll('.js-newmembers-kind').forEach((radio) => {
        radio.addEventListener('change', () => {
            form.action = form.dataset[radio.value];
            dialog.querySelectorAll('.js-newmembers-kind').forEach((other) => {
                other.closest('label').classList.toggle('is-active', other.checked);
            });
        });
    });
}

updateSelection();
