import { createPopper } from '@popperjs/core';

const trigger = document.getElementById('trigger');
const filter = document.getElementById('filter');

const popperInstance = createPopper(trigger, filter, {
    placement: 'left',
});

let open = false;

function show() {
    console.log('show');
    filter.setAttribute('data-show', '');

    open = true;

    // Enable the event listeners
    popperInstance.setOptions((options) => ({
        ...options,
        modifiers: [
            ...options.modifiers,
            { name: 'eventListeners', enabled: true },
        ],
    }));

    // Update its position
    popperInstance.update();
}

function hide() {
    console.log('hide');
    open = false;

    // Hide the tooltip
    filter.removeAttribute('data-show');

    // Disable the event listeners
    popperInstance.setOptions((options) => ({
        ...options,
        modifiers: [
            ...options.modifiers,
            { name: 'eventListeners', enabled: false },
        ],
    }));
}

window.addEventListener('click', function(e){
    if (trigger.contains(e.target)) {
        console.log('Triggered: ' + open)
        if (open) {
            hide();
        } else {
            show();
        }
    }
});
