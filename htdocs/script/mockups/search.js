$(document).ready(function(){
    $(".select2-location-picker").select2({
//        theme: 'bootstrap4',
        ajax: {
            url: "http://bootstrap/search/locations/all",
            dataType: 'json',
            delay: 250,
            data: function (params) {
            return {
                q: params.term, // search term
                page: params.page
            };
        },
        processResults: function (data, params) {
            // parse the results into the format expected by Select2
            // since we are using custom formatting functions we do not need to
            // alter the remote JSON data, except to indicate that infinite
            // scrolling can be used
            params.page = params.page || 1;

            return {
                results: data.items,
                pagination: {
                    more: (params.page * 30) < data.total_count
                }
            };
        },
        cache: true
    },
    escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
    minimumInputLength: 1
    });
}); // close out script

