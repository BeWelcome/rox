/*
 * adminwordajax.js
 *
 * Late script file to provide AJAX calls to set no update needed on the
 * admin words 'Update Needed' page.
 *
 * Initial version: shevek
 */
jQuery(function(){
    jQuery( "input[name^='ThisIsOk_']" ).on("click", function( e ) {
        jQuery( "body" ).css("cursor", "progress");
        e.preventDefault();
        var that = jQuery( this );
        var id = parseInt(this.name.replace(/ThisIsOk_/, ""), 10);
        if (id != NaN) {
            jQuery.ajax({
                url: "/admin/word/noupdate/" + id ,
                dataType: "jsonp",
                data: {
                },
                success: function( data ) {
                    if (data.status == "success") {
                        jQuery(that).hide();
                    }
                }
            });
        }
        jQuery( "body" ).css("cursor", "auto");
    });
});

