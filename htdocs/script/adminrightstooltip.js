jQuery(function () {
    jQuery(document).tooltip({
        items: "#username, #right, #scope, #level",
        content: function (callback) {
            jQuery.get('admin/rights/tooltip',{
                    tooltip: jQuery(this).attr('id')
                }, function (data) {
                    callback(data);
                }
            );
        }
    });
});