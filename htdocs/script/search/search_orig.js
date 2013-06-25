jQuery( function() {

    jQuery.widget("custom.catcomplete", jQuery.ui.autocomplete, {
        _renderMenu: function(ul, items) {
            var self = this,
                currentCategory = "";
            jQuery.each(items, function(index, item) {
                if (item.category != currentCategory) {
                    ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                    currentCategory = item.category;
                }
                self._renderItem(ul, item);
            });
        }
    });

    jQuery("#search-location").catcomplete({
      source: function( request, response ) {
        jQuery.ajax({
          url: "/search/locations/places",
          dataType: "jsonp",
          data: {
            name: request.term
          },
          success: function( data ) {
            if (data.result == "success") {
              response( 
              jQuery.map( data.locations, function( item ) {
                return {
                  label: item.name + (item.admin1 ? ", " + item.admin1 : "") + ", " + item.country,
                  value: item.geonameid,
                  category: item.category
                }
              }));
            }
        }
      }) },
      minLength: 3,
      delay: 200
    }).data("catcomplete")._renderItem = function(ul, item) {
        return jQuery("<li></li>").data("item.autocomplete", item).append(jQuery("<a class='ui-menu-item'></a>").text(item.label)).appendTo(ul);
    };
});