import TomSelect from 'tom-select';

document
    .querySelectorAll('.tom-select')
    .forEach((element) => {
        let settings = {
            maxOptions: null,
        };
        new TomSelect(element, settings);
    })
;
