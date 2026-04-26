export default class SearchPicker {
    constructor(url, cssClass = "js-search-picker", identifier = "_name") {
        this.identifier = identifier;
        let self = this;
        
        document.querySelectorAll("." + cssClass).forEach(function(element) {
            element.addEventListener("focus", function() {
                this.addEventListener("keydown", function (event) {
                    self.resetHiddenInputs(this.id);
                });
                
                // Assuming you are replacing jquery-ui catcomplete with another library like Awesomplete or similar.
                // Since this file specifically uses $.widget("custom.catcomplete", $.ui.autocomplete, ...), 
                // a full rewrite without jQuery would require replacing the entire autocomplete library used here.
                // For the scope of removing jQuery, if you are migrating away from jquery-ui, you need to instantiate
                // the new autocomplete library here instead of using $(this).catcomplete(...)
                
                console.warn('SearchPicker requires a non-jQuery autocomplete implementation.');
            });
        });
    }

    resetHiddenInputs(id) {
        id = id.replace(this.identifier, '');
        const geonameId = document.getElementById(id + "_geoname_id");
        if (geonameId) geonameId.value = "";
        
        const latitude = document.getElementById(id + "_latitude");
        if (latitude) latitude.value = "";
        
        const longitude = document.getElementById(id + "_longitude");
        if (longitude) longitude.value = "";
        
        const adminUnit = document.getElementById(id + "_admin_unit");
        if (adminUnit) adminUnit.value = "";
    }

    setHiddenInputs(id, item) {
        id = id.replace(this.identifier, '');
        
        const geonameId = document.getElementById(id + "_geoname_id");
        if (geonameId) geonameId.value = item.value;
        
        const latitude = document.getElementById(id + "_latitude");
        if (latitude) latitude.value = item.latitude;
        
        const longitude = document.getElementById(id + "_longitude");
        if (longitude) longitude.value = item.longitude;
        
        const adminUnit = document.getElementById(id + "_admin_unit");
        if (adminUnit) adminUnit.value = item.isAdminUnit;
    }
}
