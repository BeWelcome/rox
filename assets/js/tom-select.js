import TomSelect from 'tom-select';

document
    .querySelectorAll('.js-tom-select')
    .forEach((element) => {
        const optionsElement = document.getElementById(element.id + '-tom-select-options');
        const tomSelectOptions = JSON.parse(optionsElement.value);
        console.log(tomSelectOptions);

        const maxOptions = null;
        const create = true;
        new TomSelect( element, {
            create: tomSelectOptions.create,
            options: tomSelectOptions.options,
            maxOptions: tomSelectOptions.maxOptions,
            plugins: ['remove_button'],
        });
    })
;
