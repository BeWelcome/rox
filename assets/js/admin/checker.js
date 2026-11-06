/*
 * The checker's pages (templates/admin/checker/): on the messages, a decision per row (Mark all as presets every row),
 * a changed row says so and Save counts the changes; on the activities, Select all and Delete selected.
 */
const decisions = document.querySelector('.js-checker-decisions');
if (decisions) {
    const rows = [...decisions.querySelectorAll('.js-checker-row')];
    const save = decisions.querySelector('.js-checker-save');
    const saveLabel = save.querySelector('span');

    const decision = (row) => row.querySelector('input[type="radio"]:checked')?.value ?? '';

    const update = () => {
        let changed = 0;
        rows.forEach((row) => {
            const isChanged = decision(row) !== row.dataset.stored;
            row.classList.toggle('is-changed', isChanged);
            const label = row.querySelector('.js-checker-label');
            label.dataset.decision ??= label.textContent;
            label.textContent = isChanged ? label.dataset.changed : label.dataset.decision;
            changed += isChanged ? 1 : 0;
        });
        save.disabled = changed === 0;
        saveLabel.textContent = changed === 0 ? saveLabel.dataset.none
            : (changed === 1 ? saveLabel.dataset.one : saveLabel.dataset.some.replace('%count%', changed));
    };

    decisions.addEventListener('change', update);
    decisions.querySelectorAll('.js-checker-all').forEach((button) => {
        button.addEventListener('click', () => {
            rows.forEach((row) => {
                row.querySelector(`input[value="${button.dataset.decision}"]`).checked = true;
            });
            update();
        });
    });
}

const select = document.querySelector('.js-checker-select');
if (select) {
    const all = select.querySelector('.js-checker-select-all');
    const label = select.querySelector('.js-checker-select-label');
    const submit = select.querySelector('.js-checker-select-submit');
    const boxes = [...select.querySelectorAll('.js-checker-select-one')];

    const update = () => {
        const count = boxes.filter((box) => box.checked).length;
        all.checked = count > 0 && count === boxes.length;
        all.indeterminate = count > 0 && count < boxes.length;
        label.textContent = count === 0 ? label.dataset.none : label.dataset.some.replace('%count%', count);
        submit.disabled = count === 0;
    };

    all.addEventListener('change', () => {
        boxes.forEach((box) => {
            box.checked = all.checked;
        });
        update();
    });
    boxes.forEach((box) => box.addEventListener('change', update));
}
