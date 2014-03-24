jQuery( function() {
    jQuery( "a[name='search-advanced']").on( "click", function( e ) {
        e.preventDefault();
        href = jQuery(this).attr('href');
        that = jQuery(this);
        if (href.indexOf('/advanced') != -1 ) {
            jQuery('#search-advanced-loading').show();
            jQuery('#search-advanced').load('/search/members/advanced',
                function() {
                    jQuery(that).attr( 'href', '/search/members/text');
                    jQuery(that).html( searchSimple );
                    jQuery(".multiselect").multiselect();
                    jQuery('#search-advanced-loading').hide();
                });
        } else {
            jQuery('#search-advanced').html( '' );
            jQuery(this).attr( 'href', '/search/members/text/advanced');
            jQuery(this).html( searchAdvanced );
        }
    } );
});