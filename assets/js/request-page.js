/*
 * Hosting requests and invitations: the dates, the travellers, the host's answer and the
 * guest's reply. initRequests() runs when the page loads (requests.js) and again when the
 * inbox shows another conversation without reloading (conversations/inbox-nav.js).
 */
import {disableButtonOnSubmit} from './submit_button_disable';
import {initPlainTextFields} from './contact/plain_text';
import {hasPhotos, initBubblePhotos, initMessagePhotos, photosHtml} from './contact/photos';
import {initContactSendState} from './contact/send_state';
import {initStayDates} from './contact/stay_dates';
import {sendInPlace} from './conversation-send';

// Arrival and departure (new request, guest reply, host suggesting dates): the departure
// can't be before the arrival, the nights are counted and the hidden duration is filled.
const DAY = 24 * 60 * 60 * 1000;
const parseDay = (value) => (value ? new Date(value + 'T00:00:00Z') : null);
const formatDay = (date) => date.toISOString().slice(0, 10);

export function initRequests() {
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

        // Side panel: the calendar and its shortcuts in place of the two date fields
        const stayDates = form.querySelector('.js-stay-dates');
        if (stayDates) {
            initStayDates(stayDates, {
                arrival,
                departure,
                hide: [dates, form.querySelector('.c-contact-stay__top')].filter(Boolean),
            });
        }
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

    // The host's answer (request/_respond_host.html.twig): Accept, Maybe or Decline picked
    // in the stay card, then the message. Send submits as the picked button (a plain
    // message without a pick); "Change" goes back to a plain message. The dates (flexible guest) show for Accept
    // and Maybe; Decline may be sent without a message.
    document.querySelectorAll('.js-respond').forEach((form) => {
        if (form.dataset.respondReady) {
            return;
        }
        form.dataset.respondReady = '1';
        const send = form.querySelector('.js-respond-send');
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
        // "Change dates" sits in the picked accept and maybe bands
        const datesButtons = form.querySelectorAll('.js-decide-dates');
        if (stayDates) {
            datesButtons.forEach((datesButton) => {
                datesButton.addEventListener('click', () => {
                    const open = stayDates.hidden;
                    stayDates.hidden = !open;
                    datesButtons.forEach((other) => other.setAttribute('aria-expanded', String(open)));
                });
            });
        }
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
            if (stayDates) {
                // Other dates only on "Change dates" (accept or maybe)
                const withDates = choice === 'accept' || choice === 'maybe';
                if (!withDates) {
                    resetStay();
                }
                stayDates.hidden = true;
                datesButtons.forEach((datesButton) => {
                    datesButton.hidden = !withDates;
                    datesButton.setAttribute('aria-expanded', 'false');
                });
            }
            if (message) {
                // Phones: a short placeholder once an answer is picked, so it stays on one line
                const short = choice !== 'reply' && window.matchMedia('(max-width: 767.98px)').matches ? message.dataset.placeholderShort : null;
                message.placeholder = short || message.dataset['placeholder' + choice.charAt(0).toUpperCase() + choice.slice(1)] || message.placeholder;
            }
        };

        // Accept, Maybe and Decline are in the stay card above the messages (request/_stay_answer.html.twig)
        const stayAnswer = document.querySelector('.js-stay-answer');
        const stayPicks = document.querySelectorAll('.js-stay-pick');
        const resetStayAnswer = () => {
            stayPicks.forEach((other) => other.classList.remove('is-picked'));
        };
        stayPicks.forEach((pick) => {
            pick.addEventListener('click', () => {
                // A second click on the picked answer takes it back: a plain message again
                if (pick.classList.contains('is-picked')) {
                    choose('reply');
                    resetStayAnswer();
                    return;
                }
                choose(pick.dataset.choice, pick.dataset.button);
                show('write');
                stayPicks.forEach((other) => other.classList.toggle('is-picked', other === pick));
                if (message) {
                    message.focus();
                }
            });
        });
        // The cross on the picked answer: back to a plain message
        form.querySelectorAll('.js-decide-change').forEach((change) => {
            change.addEventListener('click', () => {
                choose('reply');
                resetStayAnswer();
            });
        });
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

            // The reply templates are in the "⋯" menu of the stay card (request/_card_menu.html.twig),
            // outside this form: they name the field.
            document.querySelectorAll('.js-conversation-template').forEach((button) => {
                if (button.dataset.conversationTemplateEditorId !== text.id) {
                    return;
                }
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
                if (text.value.trim() === '' && !hasPhotos(form)) {
                    // Decline without a message: its own form (request/_respond_host.html.twig)
                    const declineForm = form.parentElement.querySelector('.js-respond-decline-form');
                    if (write && write.dataset.choice === 'decline' && declineForm) {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        declineForm.submit();
                        return;
                    }
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
                html.value = toHtml(text.value) + photosHtml(form);
                const name = text.name;
                text.removeAttribute('name');
                form.appendChild(html);
                // A plain message (no answer picked, no other dates, no photos): the bubble shows at
                // once and the page stays. An answer reloads the page, its card changes.
                const datesOpen = form.querySelector('.js-respond-stay:not([hidden]), .js-decide-stay:not([hidden])');
                const plain = (!write || write.dataset.choice === 'reply') && !datesOpen && !hasPhotos(form)
                    && !(event.submitter && event.submitter.name && /\[(cancel|accept|tentatively|decline)\]$/.test(event.submitter.name));
                if (plain && sendInPlace(form, event.submitter, html.value, (sent) => {
                    html.remove();
                    text.name = name;
                    if (sent) {
                        text.value = '';
                        text.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                })) {
                    event.preventDefault();
                }
            });
        }

        // The guest opens the dates and travellers on demand, from the "⋯" menu of the stay card.
        const toggle = form.querySelector('.js-respond-stay-toggle') || document.querySelector('.js-respond-stay-toggle');
        const stay = form.querySelector('.js-respond-stay');
        if (toggle && stay) {
            const show = (open) => {
                stay.hidden = !open;
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    stay.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            };
            toggle.addEventListener('click', () => show(stay.hidden));
            const close = stay.querySelector('.js-respond-stay-close');
            if (close) {
                close.addEventListener('click', () => { show(false); toggle.focus(); });
            }
        }
    });

    disableButtonOnSubmit();

    initMessagePhotos();
    initBubblePhotos();
    initPlainTextFields();
    initContactSendState();

    // Answered: "Change" in the stay card shows Accept, Maybe and Decline again (request/_stay_answer.html.twig)
    // Declined: the edit button next to the status does it (a second click hides them again)
    document.querySelectorAll('.js-stay-change').forEach((change) => {
        change.addEventListener('click', () => {
            const answer = change.closest('.c-stay2').querySelector('.js-stay-answer');
            if (!answer) {
                return;
            }
            const band = answer.querySelector('.js-stay-band');
            const choices = answer.querySelector('.js-stay-choices');
            if (band) {
                band.hidden = true;
                choices.hidden = false;
                return;
            }
            choices.hidden = !choices.hidden;
            change.setAttribute('aria-expanded', String(!choices.hidden));
        });
    });
}
