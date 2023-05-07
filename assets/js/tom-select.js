import TomSelect from 'tom-select';
import 'tom-select/dist/esm/plugins/remove_button/plugin';
import '../scss/tom-select/tom-select.scss';

document
    .querySelectorAll('.js-tom-select')
    .forEach((element) => {
        const tomSelectOptions = element.dataset;
        console.log(tomSelectOptions)
        let settings = {
            create: tomSelectOptions.create !== undefined,
            createOnBlur: tomSelectOptions.createOnBlur !== undefined,
            plugins: ['remove_button'],
            closeAfterSelect: tomSelectOptions.closeAfterSelect !== undefined,
            render: {
                option_create: function (data, escape) {
                    return '<div class="create">' + tomSelectOptions.createOptionText + ' ' + escape(data.input) + '</strong>&hellip;</div>';
                },
                no_results: function (data, escape) {
                    return '<div class="no-results">' + tomSelectOptions.noResultsText + '</div>';
                },
            },
            maxItems: (tomSelectOptions.maxItems === undefined) ? null : tomSelectOptions.maxItems,
            maxOptions: (tomSelectOptions.maxItems === undefined) ? null : tomSelectOptions.maxOptions,
            sortField: { field: 'text' },
            preload: (tomSelectOptions.preload === undefined) ? false : tomSelectOptions.preload,
        }

        console.log(settings);
        new TomSelect(element, settings);
    })
;
