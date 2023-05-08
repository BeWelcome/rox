import TomSelect from 'tom-select';
import 'tom-select/dist/esm/plugins/remove_button/plugin';
import '../scss/tom-select/tom-select.scss';

document
    .querySelectorAll('.js-tom-select')
    .forEach((element) => {
        const tomSelectOptions = element.dataset;
        console.log(tomSelectOptions)
        const autocompleteChoices = tomSelectOptions.autocompleteChoices !== undefined;
        let settings = {
            sortField: { field: 'text' },
            plugins: ['remove_button'],
            render: {
                option_create: function (data, escape) {
                    return '<div class="create">' + tomSelectOptions.createOptionText + ' ' + escape(data.input) + '</strong>&hellip;</div>';
                },
                no_results: function (data, escape) {
                    return '<div class="no-results">' + tomSelectOptions.noResultsText + '</div>';
                },
            },
        }
        if (tomSelectOptions.create !== undefined) {
            settings.create = true;
        }
        if (tomSelectOptions.createOnBlur !== undefined) {
            settings.createOnBlur = true;
        }
        if (tomSelectOptions.closeAfterSelect !== undefined) {
            settings.closeAfterSelect = true;
        }
        if (tomSelectOptions.maxItems !== undefined) {
            settings.maxItems = tomSelectOptions.maxItems;
        }
        if (tomSelectOptions.maxOptions !== undefined) {
            settings.maxOptions = tomSelectOptions.maxOptions;
        }
        if (tomSelectOptions.preload !== undefined) {
            settings.preload = tomSelectOptions.preload;
        }

        if (autocompleteChoices) {
            settings.valueField = 'title';
            settings.labelField = 'title';
            settings.searchField = 'title';
            settings.options = JSON.parse(tomSelectOptions.autocompleteChoices);
        }

        console.log(settings);
        new TomSelect(element, settings);
    })
;
