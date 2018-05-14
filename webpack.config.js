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
        './web/script/common/common.js',
        './src/AppBundle/Resources/scss/bewelcome.scss'
    ])
    .addEntry('jquery_ui', './src/AppBundle/Resources/js/jquery_ui.js')
    .addEntry('backwards', './src/AppBundle/Resources/js/backwards.js')
    .addEntry('skrollr', './src/AppBundle/Resources/js/skrollr.js')
    .addEntry('signup', './src/AppBundle/Resources/js/signup.js')
    .addEntry('landing', './src/AppBundle/Resources/public/js/landing/landing.js')

    .addEntry('search/searchpicker', './src/AppBundle/Resources/public/js/search/searchpicker.js')
    .addEntry('search/createmap', './src/AppBundle/Resources/public/js/search/createmap.js')
    .addEntry('tempusdominus', './src/AppBundle/Resources/js/tempusdominus.js')
    .addEntry('requests', './src/AppBundle/Resources/js/requests.js')
    .addEntry('leaflet', './src/AppBundle/Resources/js/leaflet.js')
    .addEntry('member/autocomplete', './src/AppBundle/Resources/js/member/autocomplete.js')

    .enableSassLoader()
    // allow legacy applications to use $/jQuery as a global variable, make popper visible for bootstrap
    .autoProvidejQuery()
    .autoProvideVariables({
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

