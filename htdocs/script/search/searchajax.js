jQuery( function() {
    jQuery( "a[name='search-advanced']").on( "click", function( e ) {
        e.preventDefault();
        jQuery('#search-advanced').load('/search/members/advanced');
        jQuery(this).html( 'Simple search only' );
        return false;
    } );
});