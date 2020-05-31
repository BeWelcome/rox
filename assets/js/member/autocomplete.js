$( function() {
    function log( message ) {
        $( "<div>" ).text( message ).prependTo( "#log" );
        $( "#log" ).scrollTop( 0 );
    }

    $( ".member-autocomplete-start" ).autocomplete({
        source: function( request, response ) {
            $.ajax( {
                url: "/member/autocomplete/start",
                dataType: "jsonp",
                data: {
                    term: request.term
                },
                success: function( data ) {
                    response( data );
                }
            } );
        },
        minLength: 2,
        select: function( event, ui ) {
            log( "Selected: " + ui.item.value + " aka " + ui.item.id );
            $(this).val(ui.item.value);
        }
    } );

    $( ".member-autocomplete" ).autocomplete({
        source: function( request, response ) {
            $.ajax( {
                url: "/member/autocomplete",
                dataType: "jsonp",
                data: {
                    term: request.term
                },
                success: function( data ) {
                    response( data );
                }
            } );
        },
        minLength: 2,
        select: function( event, ui ) {
            log( "Selected: " + ui.item.value + " aka " + ui.item.id );
            $(this).val(ui.item.value);
        }
    } );
});