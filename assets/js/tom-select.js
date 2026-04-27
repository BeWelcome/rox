import TomSelect from 'tom-select';
import 'tom-select/dist/esm/plugins/remove_button/plugin';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';

let tomSelects = [];

export function initializeTomSelects(cssClass = ".js-tom-select") {
    document
        .querySelectorAll(cssClass)
        .forEach((element) => {
            try {
                const tomSelectOptions = element.dataset;

                const autocompleteChoices = tomSelectOptions.autocompleteChoices !== undefined;
                let settings = {
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
                if (tomSelectOptions.plugins !== undefined) {
                    settings.plugins = tomSelectOptions.plugins.split(",");
                }
                if (autocompleteChoices) {
                    settings.valueField = 'title';
                    settings.labelField = 'title';
                    settings.searchField = 'title';
                    settings.options = JSON.parse(tomSelectOptions.autocompleteChoices);
                }
                settings.maxOptions = null;
                settings.sortField = [{field:'$order'},{field:'$score'}];
                tomSelects.push(new TomSelect(element, settings));
            } catch(e) { }

        })
    ;
}

export function destroyTomSelects(cssClass = "js-tom-select") {
    tomSelects.forEach( (tomSelect) => {
        tomSelect.sync()
        tomSelect.destroy()
    })
}

initializeTomSelects();
