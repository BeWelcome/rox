/*
 * adminwordajax.js
 *
 * Late script file to provide AJAX calls to set no update needed on the
 * admin words 'Update Needed' page.
 *
 * Initial version: shevek
 */
function processVote( e ) {
        jQuery( "body" ).css("cursor", "progress");
        e.preventDefault();
        var that = jQuery( this );
        var upvote = this.name.match(/upvote_/);
        var id;
        var url;
        if (upvote) {
            id = parseInt(this.name.replace(/upvote_/, ""), 10);
            url = "/suggestions/ajax/" + id + "/upvote";
        } else {
            id = parseInt(this.name.replace(/downvote_/, ""), 10);
            url = "/suggestions/ajax/" + id + "/downvote";
        }
        if (id != NaN) {
            jQuery.ajax({
                url: url,
                dataType: "jsonp",
                data: {
                },
                success: function( data ) {
                    if (data.status == "success") {
                        // Update the id containing the element
                        var parent = jQuery(that).parent();
                        var newHtml = "";
                        if (upvote) {
                            newHtml = "<i class='icon-angle-up icon-3x' title='Already upvoted'></i>"
                        } else {
                            newHtml = "<a name='upvote_" + id + "' href='/suggestions/" + id + "/upvote' class='icon-chevron-up icon-3x' title='upvote'></a>";
                        }
                        newHtml += "<br /><span class='big'>" + data.votes + "</span><br />";
                        if (upvote) {
                            newHtml += "<a name='downvote_" + id + "' href='/suggestions/" + id + "/downvote' class='icon-chevron-down icon-3x' title='downvote'></a>";
                        } else {
                            newHtml += "<i class='icon-angle-down icon-3x' title='Already downvoted'></i>";
                        }
                        parent.html(newHtml);
                        // rebind click event on new <a> tags
                        jQuery( "a[name^='upvote_" + id + "']" ).on("click", processVote );
                        jQuery( "a[name^='downvote_" + id + "']" ).on("click", processVote );
                    }
                }
            });
        }
        jQuery( "body" ).css("cursor", "auto");
    return false;
}

jQuery(function(){
    jQuery( "a[name^='upvote_']" ).on("click", processVote );
    jQuery( "a[name^='downvote_']" ).on("click", processVote );
});

