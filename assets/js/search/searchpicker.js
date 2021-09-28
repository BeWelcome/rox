import $ from 'jquery';
import 'jquery-ui/ui/widgets/autocomplete';
import 'jquery-ui/themes/base/autocomplete.css';

export default class SearchPicker {
    constructor(url, cssClass = "js-search-picker", identifier = "_name") {
        this.identifier = identifier;
        let self = this;
        $("." + cssClass).on("focus", function() {
            $(this).on("keydown", function (event) {
                self.resetHiddenInputs(this.id);
            }).catcomplete({
                source: function (request, response) {
                    $.ajax({
                        url: url,
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
                        self.resetHiddenInputs(this.id);
                    } else {
                        $(this).val(ui.item.label);
                        self.setHiddenInputs(this.id, ui.item);
                    }
                    return false;
                },
                change: function (event, ui) {
                    if (typeof ui.item === 'undefined' || ui.item === null) {
                        self.resetHiddenInputs(this.id);
                    } else {
                        $(this).val(ui.item.label);
                        self.setHiddenInputs(this.id, ui.item);
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
                    $(this).val(ui.item.label);
                    self.setHiddenInputs(this.id, ui.item);


                    return false;
                },
                minLength: 1,
                delay: 500
            });
        });
    }

    resetHiddenInputs(id) {
        id = id.replace(this.identifier, '');
        $("#" + id + "_geoname_id").val("");
        $("#" + id + "_latitude").val("");
        $("#" + id + "_longitude").val("");
        $("#" + id + "_admin_unit").val("");
    }

    setHiddenInputs(id, item) {
        console.log('before ' + id);
        id = id.replace(this.identifier, '');
        console.log('after ' + id);
        $("#" + id + "_geoname_id").val(item.value);
        $("#" + id + "_latitude").val(item.latitude);
        $("#" + id + "_longitude").val(item.longitude);
        $("#" + id + "_admin_unit").val(item.isAdminUnit);
    }
}

$.widget("custom.catcomplete", $.ui.autocomplete, {
    _create: function () {
        this._super();
        this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
    },
    _renderMenu: function (ul, items) {
        var that = this,
            currentCategory = "";
        $.each(items, function (index, item) {
            var li;
            if (item.category !== currentCategory) {
                ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                currentCategory = item.category;
            }
            li = that._renderItemData(ul, item);
            if (item.category) {
                li.attr("aria-label", item.category + " : " + item.label);
            }
        });
    }
});

