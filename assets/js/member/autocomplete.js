import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.min.css';

// Not every page that loads this has a username field
const field = document.querySelector('.member-autocomplete-start');
if (field) new TomSelect(field, {
    load: function(query, callback) {
        const url = '/member/autocomplete/start?term=' + encodeURIComponent(query);
        fetch(url)
            .then(response => response.json())
            .then(json => {
                callback(json.items);
            }).catch(()=>{
            callback();
        });
    },
    maxItems: 1,
    valueField: 'id',
    labelField: 'id',
    searchField: 'id',
});
