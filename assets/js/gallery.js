var Masonry = require('masonry-layout');
var imagesLoaded = require('imagesloaded');

$(function () {
    // init Masonry
    var grid = document.getElementById('masonry-grid');

    var msnry = new Masonry( grid, {
        percentPosition: true
    });

    imagesLoaded( grid ).on( 'done', function() {
        // layout Masonry after each image loads
        msnry.layout();
    });

});
