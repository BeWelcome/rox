$(document).ready(function(){
    var $query = $('#queries_query');
    // When query gets selected ...
    $query.change(function() {
        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected sport value.
        var data = {};
        data[$query.attr('name')] = $query.val();
        // Submit data via AJAX to the form's action path.
        $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
                // Replace current parameters ...
                $('#parameters').replaceWith(
                    // ... with the returned one(s) from the AJAX response.
                    $(html).find('#parameters')
                );
            }
        });
    });
});
