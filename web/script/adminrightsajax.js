jQuery(function () {
    jQuery("#username").autocomplete({
        source: function (request, response) {
            jQuery.ajax({
                url: "/member/autocomplete",
                dataType: "jsonp",
                data: {
                    term: request.term
                },
                success: function success(data) {
                response(data);
            }
        });
        },
        change: function (event, ui) {
            if (ui.item == null) {
            } else {
                jQuery("#username").val(ui.item.value);
            }
        },
        select: function (event, ui) {
            jQuery(this).val(ui.item.value);

            return false;
        },
        minLength: 3,
        delay: 500
    });
});