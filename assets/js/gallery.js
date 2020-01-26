let Masonry = require('masonry-layout');
let imagesLoaded = require('imagesloaded');

$(function () {
    // init Masonry
    let grid = document.getElementById('masonry-grid');

    if (grid !== null)
    {
        var msnry = new Masonry( grid, {
            percentPosition: true
        });

        imagesLoaded( grid ).on( 'done', function() {
            // layout Masonry after each image loads
            msnry.layout();
        });
    }
});
