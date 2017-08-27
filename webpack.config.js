var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
//    .cleanupOutputBeforeBuild()


    .createSharedEntry('bewelcome', [
        'jquery',
        'popper.js',
        'bootstrap',
        'select2',
        './src/AppBundle/Resources/scss/bewelcome.scss',
        './web/script/common/common.js'
    ])
    .addEntry('jquery_ui', './src/AppBundle/Resources/js/jquery_ui.js')
    .addEntry('backwards', './src/AppBundle/Resources/js/backwards.js')
    .addEntry('landing', './src/AppBundle/Resources/public/js/landing/landing.js')
    .addEntry('searchpicker', './src/AppBundle/Resources/public/js/search/searchpicker.js')
    .addEntry('tempusdominus', './src/AppBundle/Resources/js/tempusdominus.js')

    .enableSassLoader()
    // allow legacy applications to use $/jQuery as a global variable, make popper visible for bootstrap
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
        Popper: ['popper.js', 'default'],
    })
    .addLoader({
        test: require.resolve('jquery'),
        use: [{
            loader: 'expose-loader',
            options: 'jQuery'
        },{
            loader: 'expose-loader',
            options: '$'
        }]
    })
//    .enableSourceMaps(!Encore.isProduction())

// create hashed filenames (e.g. app.abc123.css)
// .enableVersioning()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();

global.$ = global.jQuery = require('jquery');


