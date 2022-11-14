import TomSelect from 'tom-select';

document
    .querySelectorAll('.js-tom-select')
    .forEach((element) => {
        const optionsElement = document.getElementById(element.id + '-tom-select-options');
        const tomSelectOptions = JSON.parse(optionsElement.value);

        let settings = {
            create: tomSelectOptions.create,
            plugins: ['remove_button'],
            closeAfterSelect: tomSelectOptions.closeAfterSelect,
            render: {
                option_create: function (data, escape) {
                    return '<div class="create">' + tomSelectOptions.optionCreate + ' ' + escape(data.input) + '</strong>&hellip;</div>';
                },
                no_results: function (data, escape) {
                    return '<div class="no-results">' + tomSelectOptions.noResults + '</div>';
                },
            }
        }
        if (tomSelectOptions.options !== undefined) {
            settings.options = tomSelectOptions.options;
        }
        if (tomSelectOptions.maxItems !== undefined) {
            settings.maxItems = tomSelectOptions.maxItems;
        }
console.log(settings);
        new TomSelect( element, settings);
    })
;
