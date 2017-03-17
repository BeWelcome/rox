$(document).ready(function(){
    $(".memberpicker").select2({
        minimumInputLength: 2,
        minimumResultsForSearch: 10,
        ajax: {
            url: '/search/members/username',
            dataType: "json",
            type: "GET",
            data: function (data) {
                return data;
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.tag_value,
                            id: item.tag_id
                        }
                    })
                };
            }
        }
    });
}); // close out script
