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

jQuery(function() {
    jQuery(".multiselect").multiselect();
    jQuery( "#search-location" ).on( "keydown", function( event ) {
		jQuery( "#search-geoname-id" ).val( 0 );
	});

    jQuery( "#search-location" ).catcomplete({
  source: function( request, response ) {
    jQuery.ajax({
      url: "/search/locations/all",
      dataType: "jsonp",
      data: {
        name: request.term
      },
      success: function( data ) {
        if (data.status != "success") {
        	data.locations = [{ name: noMatchesFound, category: "Information", cnt: 0 }];
        }
          response(
          jQuery.map( data.locations, function( item ) {
            return {
              label: (item.name ? item.name : "")+ (item.admin1 ? (item.name ? ", " : "") + item.admin1 : "") + (item.country ? ", " + item.country : "")  + (item.cnt != 0 ? " (" + item.cnt +")" : ""),
              labelnocount: (item.name ? item.name : "")+ (item.admin1 ? (item.name ? ", " : "") + item.admin1 : "") + (item.country ? ", " + item.country : ""),
              value: item.geonameid, latitude: item.latitude, longitude: item.longitude,
              category: item.category
            };
          }));
    }
  });
  },
  change: function( event, ui ) {
    if (ui.item == null) {
      jQuery( "#search-geoname-id" ).val( 0 );
    } else {
      jQuery( "#search-geoname-id" ).val(ui.item.value);
    }
  },
  search: function( event, ui ) {
	  jQuery( '#search-loading').css( 'visibility', 'visible');
  },
  response: function( event, ui ) {
	  jQuery( '#search-loading').css( 'visibility', 'hidden');
  },
  select: function( event, ui ) {
    jQuery( "#search-geoname-id" ).val( ui.item.value );
    jQuery( "#search-latitude" ).val( ui.item.latitude );
    jQuery( "#search-longitude" ).val( ui.item.longitude );
    jQuery( this ).val( ui.item.labelnocount );

    return false;
  },
  minLength: 1,
  delay: 500
    });
});