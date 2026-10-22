import dayjs from 'dayjs';
import Litepicker from 'litepicker';
import {disableButtonOnSubmit} from './submit_button_disable';
import {initPlainTextFields} from './contact/plain_text';
import {initContactSendState} from './contact/send_state';

import '../scss/_daterangepicker.scss';

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementsByClassName('js-litepicker')[0];
    if (input !== undefined) {
        const parent = input.id.replace('_duration', '');

        const picker = new Litepicker({
            element: input,
            singleMode: false,
            minDate: dayjs(),
            numberOfMonths: 2,
            numberOfColumns: 2,
            lang: document.documentElement.lang,
            showTooltip: false,
            setup: (picker) => {
                picker.on('selected', (start, end) => {
                    const arrival = document.getElementById(parent + '_arrival');
                    arrival.value = start ? start.format('YYYY-MM-DD') : '';
                    const departure = document.getElementById(parent + '_departure');
                    departure.value = end ? end.format('YYYY-MM-DD') : '';

                    let original = document.getElementById('duration-original');
                    if (null !== original) {
                        original.classList.remove("d-none");
                    }
                });
            }
        });

        const arrival = document.getElementById(parent + '_arrival').value;
        const departure = document.getElementById(parent + '_departure').value;

        if (arrival !== "") {
            picker.setDateRange(arrival, departure);
        }
    }
});

// Arrival and departure (new request, guest reply, host suggesting dates): the departure
// can't be before the arrival, the nights are counted and the hidden duration is filled.
const DAY = 24 * 60 * 60 * 1000;
const parseDay = (value) => (value ? new Date(value + 'T00:00:00Z') : null);
const formatDay = (date) => date.toISOString().slice(0, 10);

document.querySelectorAll('.js-request-dates').forEach((dates) => {
    const form = dates.closest('form');
    const arrival = dates.querySelector('.js-request-arrival');
    const departure = dates.querySelector('.js-request-departure');
    const duration = form.querySelector('.js-request-duration');
    const nights = form.querySelector('.js-request-nights');
    const plural = new Intl.PluralRules(document.documentElement.lang || 'en');

    const update = () => {
        const start = parseDay(arrival.value);
        if (start) {
            const firstDeparture = formatDay(new Date(start.getTime() + DAY));
            departure.min = firstDeparture;
            if (!departure.value || departure.value < firstDeparture) {
                departure.value = firstDeparture;
            }
        }
        const end = parseDay(departure.value);
        if (duration) {
            duration.value = start && end ? arrival.value + ' - ' + departure.value : '';
        }
        if (nights) {
            if (start && end && end > start) {
                const count = Math.round((end - start) / DAY);
                const text = count === 0 ? nights.dataset.zero
                    : (plural.select(count) === 'one' ? nights.dataset.one : nights.dataset.other);
                nights.textContent = text.replace('{n}', count);
            } else {
                nights.textContent = '';
            }
        }
    };

    arrival.addEventListener('change', update);
    departure.addEventListener('change', update);
    update();
});

// Number of travellers: the minus and plus buttons, kept between min and max.
document.querySelectorAll('.js-request-stepper').forEach((stepper) => {
    const input = stepper.querySelector('input');
    const min = parseInt(input.min, 10) || 1;
    const max = parseInt(input.max, 10) || 20;
    const buttons = stepper.querySelectorAll('.js-request-step');

    const refresh = () => {
        const value = parseInt(input.value, 10) || min;
        buttons.forEach((button) => {
            const step = parseInt(button.dataset.step, 10);
            button.disabled = (step < 0 && value <= min) || (step > 0 && value >= max);
        });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const value = (parseInt(input.value, 10) || min) + parseInt(button.dataset.step, 10);
            input.value = Math.min(max, Math.max(min, value));
            refresh();
        });
    });
    input.addEventListener('input', refresh);
    refresh();
});

// The host's answer, in two steps (request/_respond_host.html.twig): pick Accept,
// Maybe, Decline or "Just reply", then write the message. Send submits as the picked
// button; "Change" goes back to the choice. The dates (flexible guest) show for Accept
// and Maybe; "Decline without message" for Decline.
document.querySelectorAll('.js-respond').forEach((form) => {
    const send = form.querySelector('.js-respond-send');
    const declineLink = form.querySelector('.js-respond-decline-link');
    const steps = form.querySelectorAll('.js-decide-step');
    const write = form.querySelector('.js-decide-step[data-step="write"]');
    const stayDates = form.querySelector('.js-decide-stay');
    const message = form.querySelector('.js-respond-text');

    const show = (step) => steps.forEach((element) => { element.hidden = element.dataset.step !== step; });
    const resetStay = () => {
        if (!stayDates) {
            return;
        }
        stayDates.querySelectorAll('input').forEach((input) => {
            if (input.type === 'radio' || input.type === 'checkbox') {
                input.checked = input.defaultChecked;
            } else {
                input.value = input.defaultValue;
            }
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    };
    const choose = (choice, button) => {
        if (write) {
            write.dataset.choice = choice;
        }
        form.querySelectorAll('.js-decide-picked, .js-decide-send').forEach((element) => {
            element.hidden = element.dataset.choice !== choice;
        });
        if (send && (button || send.dataset.button)) {
            send.name = button || send.dataset.button;
        }
        if (declineLink) {
            declineLink.hidden = choice !== 'decline';
        }
        if (stayDates) {
            const withDates = choice === 'accept' || choice === 'maybe';
            if (!withDates) {
                resetStay();
            }
            stayDates.hidden = !withDates;
        }
        if (message) {
            message.placeholder = message.dataset['placeholder' + choice.charAt(0).toUpperCase() + choice.slice(1)] || message.placeholder;
        }
    };

    form.querySelectorAll('.js-decide-pick').forEach((pick) => {
        pick.addEventListener('click', () => {
            choose(pick.dataset.choice, pick.dataset.button);
            show('write');
            if (message) {
                message.focus();
            }
        });
    });
    const change = form.querySelector('.js-decide-change');
    if (change) {
        change.addEventListener('click', () => show('decide'));
    }
    if (write) {
        choose(write.dataset.choice);
    }

    // The message is a plain text field: it grows with the text, the reply templates
    // fill it, and on send the text is turned into paragraphs (the field stores HTML).
    const text = form.querySelector('.js-respond-text');
    if (text) {
        const resize = () => {
            text.style.height = 'auto';
            text.style.height = Math.min(text.scrollHeight + 2, 260) + 'px';
        };
        text.addEventListener('input', () => {
            text.classList.remove('is-invalid');
            resize();
        });
        resize();

        form.querySelectorAll('.js-conversation-template').forEach((button) => {
            button.addEventListener('click', () => {
                const holder = document.createElement('div');
                holder.innerHTML = button.dataset.conversationTemplate
                    .replace(/<\/p>\s*/gi, '\n\n')
                    .replace(/<br\s*\/?>/gi, '\n');
                const template = holder.textContent.trim();
                text.value = text.value.trim() ? text.value.trim() + '\n\n' + template : template;
                resize();
                text.focus();
            });
        });

        const escapeHtml = (value) => value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
        const toHtml = (value) => value
            .trim()
            .split(/\n\s*\n/)
            .map((paragraph) => '<p>' + escapeHtml(paragraph.trim()).replace(/\n/g, '<br>') + '</p>')
            .join('');

        form.addEventListener('submit', (event) => {
            if (text.value.trim() === '') {
                // A message is needed (the server would refuse it anyway).
                event.preventDefault();
                event.stopImmediatePropagation();
                text.classList.add('is-invalid');
                text.focus();
                return;
            }
            const html = document.createElement('input');
            html.type = 'hidden';
            html.name = text.name;
            html.value = toHtml(text.value);
            text.removeAttribute('name');
            form.appendChild(html);
        });
    }

    // The guest opens the dates and travellers on demand.
    const toggle = form.querySelector('.js-respond-stay-toggle');
    const stay = form.querySelector('.js-respond-stay');
    if (toggle && stay) {
        const show = (open) => {
            stay.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        toggle.addEventListener('click', () => show(stay.hidden));
        const close = stay.querySelector('.js-respond-stay-close');
        if (close) {
            close.addEventListener('click', () => { show(false); toggle.focus(); });
        }
    }
});

disableButtonOnSubmit();

initPlainTextFields();
initContactSendState();
