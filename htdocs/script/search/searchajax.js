jQuery( function() {
    jQuery( "a[name='search-advanced']").on( "click", function( e ) {
        e.preventDefault();
        href = jQuery(this).attr('href');
        if (href.indexOf('/advanced') != -1 ) {
            jQuery('#search-advanced').load('/search/members/advanced');
            jQuery(this).attr( 'href', '/search/members/text');
            jQuery(this).html( searchSimple );
        } else {
            jQuery('#search-advanced').html( '' );
            jQuery(this).attr( 'href', '/search/members/text/advanced');
            jQuery(this).html( searchAdvanced );
        }
    } );
});