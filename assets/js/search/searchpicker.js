$( function() {
    $.widget("custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super();
            this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
        },
        _renderMenu: function( ul, items ) {
            var that = this,
                currentCategory = "";
            $.each( items, function( index, item ) {
                var li;
                if ( item.category !== currentCategory ) {
                    ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
                    currentCategory = item.category;
                }
                li = that._renderItemData( ul, item );
                if ( item.category ) {
                    li.attr( "aria-label", item.category + " : " + item.label );
                }
            });
        }
    });

    $(".search-picker").on("keydown", function (event) {
        $("#" + this.id + "_geoname_id").val("");
        $("#" + this.id + "_latitude").val("");
        $("#" + this.id + "_longitude").val("");
        $("#" + this.id + "_admin_unit").val("");
    }).catcomplete({
        source: function (request, response) {
            $.ajax({
                url: "/search/locations/all",
                dataType: "jsonp",
                data: {
                    name: request.term
                },
                success: function (data) {
                    if (data.status !== "success") {
                        // TODO i18n for name property
                        data.locations = [{name: 'No matches found.', category: "Information", cnt: 0}];
                    }
                    response(
                        $.map(data.locations, function (item) {
                            return {
                                label: (item.name ? item.name : "") + (item.admin1 ? (item.name ? ", " : "") + item.admin1 : "") + (item.country ? ", " + item.country : ""),
                                value: item.geonameId,
                                latitude: item.latitude,
                                longitude: item.longitude,
                                isAdminUnit: item.isAdminUnit,
                                category: item.category
                            };
                        }));
                }
            });
        },
        focus: function (event, ui) {
            if (typeof ui.item === 'undefined' || ui.item === null) {
                $("#" + this.id + "_geoname_id").val("");
                $("#" + this.id + "_latitude").val("");
                $("#" + this.id + "_longitude").val("");
                $("#" + this.id + "_admin_unit").val("");
            } else {
                $(this).val(ui.item.label);
                $("#" + this.id + "_geoname_id").val(ui.item.value);
                $("#" + this.id + "_latitude").val(ui.item.latitude);
                $("#" + this.id + "_longitude").val(ui.item.longitude);
                $("#" + this.id + "_admin_unit").val(ui.item.isAdminUnit);
            }
            return false;
        },
        change: function (event, ui) {
            if (typeof ui.item === 'undefined' || ui.item === null) {
                $("#" + this.id + "_geoname_id").val("");
                $("#" + this.id + "_latitude").val("");
                $("#" + this.id + "_longitude").val("");
                $("#" + this.id + "_admin_unit").val("");
            } else {
                $(this).val(ui.item.label);
                $("#" + this.id + "_geoname_id").val(ui.item.value);
                $("#" + this.id + "_latitude").val(ui.item.latitude);
                $("#" + this.id + "_longitude").val(ui.item.longitude);
                $("#" + this.id + "_admin_unit").val(ui.item.isAdminUnit);
            }
        },
        select: function (event, ui) {
            if (typeof ui.item === 'undefined' || ui.item === null) {
                return false;
            }

            let showOnMap = $('search[showOnMap]');
            if (showOnMap.length) {
                showOnMap.val(0);
            }
            $("#" + this.id + "_geoname_id").val(ui.item.value);
            $("#" + this.id + "_latitude").val(ui.item.latitude);
            $("#" + this.id + "_longitude").val(ui.item.longitude);
            $("#" + this.id + "_admin_unit").val(ui.item.isAdminUnit);

            $(this).val(ui.item.label);

            return false;
        },
        minLength: 1,
        delay: 500
    });
});
