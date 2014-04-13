jQuery(function () {
    jQuery("#username").autocomplete({
        source: function (request, response) {
            jQuery.ajax({
                url: "/search/members/username",
                dataType: "jsonp",
                data: {
                    username: request.term
                },
                success: function (data) {
                    if (data.status != "success") {
                        data.usernames = [
                            { username: "No match found" }
                        ];
                    }
                    response(
                        jQuery.map(data.usernames, function (item) {
                            return {
                                value: item.username
                            };
                        }));
                }
            });
        },
        change: function (event, ui) {
            if (ui.item == null) {
                jQuery("#username").val('');
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