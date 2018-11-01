$( function() {
    $( "#faqs" ).sortable({
        axis: 'y',
        update: function (event, ui) {
            var data = $(this).sortable( "serialize", { key : "faq" } );
            $("#form_sortOrder").val(data);
        }
    });
});