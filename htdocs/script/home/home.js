$(document).ready(function() {
    updateBlock('messages');

    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        updateBlock($(e.target).attr('aria-controls'));
    })
});

function updateBlock(block) {
    var path = '/widget/' + block;
    $.ajax({
        type: "GET",
        url: path,
        success: function(data){
            $("#" + block + "display").html(data);
            // Set click event in case we updated notifications
            if (block == "notifications") {
                $('.notify').click(function () {
                    var that = $(this);
                    var id = $(this).attr('id');
                    $.ajax({
                        type: "GET",
                        url: '/notify/' + id.replace('notify-','') + '/check',
                        success: function(data){
                            // remove the row as the notification was read
                            updateBlock('notifications');
                        }
                    });
                })
            }
        }
    });
}