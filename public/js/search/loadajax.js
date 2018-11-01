$(document).ready(function() {
    $('.ajaxload').click(Search.loadContent);
});

var Search = {
    loadContent: function (e) {
        e.preventDefault();
        $('#overlay').addClass("loading");
        let url = $(this).attr('href');
        // Get parameters
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'html',
            success: function (data) {
                let searchResults = $('#searchresults');
                searchResults.replaceWith(data);
                $('#overlay').removeClass("loading");
                $(".ajaxload").click(Search.loadContent);
            }
        });
    }
};
