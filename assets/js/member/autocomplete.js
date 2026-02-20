import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.min.css';

new TomSelect('.member-autocomplete-start', {
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
    create: true,
    createOnBlur: true,
    valueField: 'id',
    labelField: 'id',
    searchField: 'id',
});
