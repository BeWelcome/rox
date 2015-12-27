function monkeyPatchAutocomplete() {
    jQuery.ui.autocomplete.prototype._renderItem = function (ul, item) {

        var keywords = jQuery.trim(this.term).split(' ').join('|');
        var output = item.label.replace(new RegExp("(" + keywords + ")", "gi"), '<span class="ui-menu-item-highlight">$1</span>');

        return jQuery("<li>")
            .append(jQuery("<a>").html(output))
            .appendTo(ul);
    };
}

jQuery(function () {
    monkeyPatchAutocomplete();
});

jQuery.widget( "custom.catcomplete", jQuery.ui.autocomplete, {
    _renderMenu: function( ul, items ) {
        var that = this,
            currentCategory = "";
        jQuery.each( items, function( index, item ) {
            if ( item.category != currentCategory ) {
                ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
                currentCategory = item.category;
            }
            that._renderItemData( ul, item );
        });
    }
});

function enableAutoComplete(addMarker) {
    jQuery( ".search-picker" ).on( "keydown", function( event ) {
        jQuery( "#" + this.id + "_geoname_id" ).val( "" );
        jQuery( "#" + this.id + "_latitude" ).val( "" );
        jQuery( "#" + this.id + "_longitude" ).val( "" );
    }).catcomplete({
        source: function (request, response) {
            jQuery.ajax({
                url: "/search/locations/all",
                dataType: "jsonp",
                data: {
                    name: request.term
                },
                success: function (data) {
                    if (data.status != "success") {
                        data.locations = [{name: noMatchesFound, category: "Information", cnt: 0}];
                    }
                    response(
                        jQuery.map(data.locations, function (item) {
                            return {
                                label: (item.name ? item.name : "") + (item.admin1 ? (item.name ? ", " : "") + item.admin1 : "") + (item.country ? ", " + item.country : "") + (item.cnt != 0 ? " (" + item.cnt + ")" : ""),
                                labelnocount: (item.name ? item.name : "") + (item.admin1 ? (item.name ? ", " : "") + item.admin1 : "") + (item.country ? ", " + item.country : ""),
                                value: item.geonameid, latitude: item.latitude, longitude: item.longitude,
                                category: item.category
                            };
                        }));
                }
            });
        },
        focus: function( event, ui ) {
            if ((ui.item) === undefined) {
                jQuery("#" + this.id + "_geoname_id").val("");
                jQuery( "#" + this.id + "_latitude" ).val( "" );
                jQuery( "#" + this.id + "_longitude" ).val( "" );
            } else {
                jQuery(this).val(ui.item.labelnocount);
                jQuery("#" + this.id + "_geoname_id").val(ui.item.value);
                jQuery("#" + this.id + "_latitude").val(ui.item.latitude);
                jQuery("#" + this.id + "_longitude").val(ui.item.longitude);
            }
            return false;
        },
        change: function (event, ui) {
            if ((ui.item) === undefined) {
                jQuery("#" + this.id + "_geoname_id").val("");
                jQuery( "#" + this.id + "_latitude" ).val( "" );
                jQuery( "#" + this.id + "_longitude" ).val( "" );
            } else {
                jQuery(this).val(ui.item.labelnocount);
                jQuery("#" + this.id + "_geoname_id").val( ui.item.value );
                jQuery("#" + this.id + "_latitude").val(ui.item.latitude);
                jQuery("#" + this.id + "_longitude").val(ui.item.longitude);
            }
        },
        select: function (event, ui) {
            if ((ui.item) === undefined) return false;

            jQuery("#" + this.id + "_geoname_id").val(ui.item.value);
            jQuery("#" + this.id + "_latitude").val(ui.item.latitude);
            jQuery("#" + this.id + "_longitude").val(ui.item.longitude);
            jQuery(this).val(ui.item.labelnocount);

            addMarker(this.id, ui.item.labelnocount, ui.item.value, ui.item.latitude, ui.item.longitude);

            return false;
        },
        minLength: 1,
        delay: 500
    });
}

jQuery(function() {
    if (typeof addMarker === 'undefined') {
        addMarker = function() {};
    }
    enableAutoComplete(addMarker);
});