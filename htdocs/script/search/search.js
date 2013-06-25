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

    jQuery(function() {
        jQuery( "#search-location" ).catcomplete({
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
      change: function( event, ui ) {
        if (ui.item == null) {
          jQuery( "#search-geoname-id" ).val( 0 );
        } else {
          jQuery( "#search-geoname-id" ).val(ui.item.value);
        }
      },
      select: function( event, ui ) {
        jQuery( "#search-geoname-id" ).val( ui.item.value );
        jQuery( this ).val( ui.item.label );
        
        return false;
      },
      minLength: 3,
      delay: 400,
        });
    });