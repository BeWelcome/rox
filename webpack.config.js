var Encore = require('@symfony/webpack-encore');

Encore
    .configureRuntimeEnvironment('dev')
    .enableSingleRuntimeChunk()
    .enablePostCssLoader()
    .setOutputPath('public/build/')
    .setPublicPath('/build')
//    .cleanupOutputBeforeBuild()

    .createSharedEntry('bewelcome', './assets/js/bewelcome.js')
    .addEntry('jquery_ui', './assets/js/jquery_ui.js')
    .addEntry('backwards', './assets/js/backwards.js')
    .addEntry('signup/signup', './assets/js/signup.js')
    .addEntry('landing', './assets/public/js/landing/landing.js')
    .addEntry('skrollr', './assets/js/skrollr.js')

    .addEntry('search/searchpicker', './assets/public/js/search/searchpicker.js')
    .addEntry('search/loadcontent', './assets/public/js/search/loadajax.js')
    .addEntry('search/search', './assets/js/search/search.js')

    .addEntry('tempusdominus', './assets/js/tempusdominus.js')
    .addEntry('requests', './assets/js/requests.js')
    .addEntry('treasurer', './assets/js/treasurer.js')
    .addEntry('leaflet', './assets/js/leaflet.js')
    .addEntry('member/autocomplete', './assets/js/member/autocomplete.js')
    .addEntry('admin/faqs', './assets/js/admin/faqs.js')
    .addEntry('chartjs', './node_modules/chart.js/dist/Chart.js')
    .addEntry('offcanvas', './assets/public/js/offcanvas.js')
    .addEntry('profile/profile', './assets/js/profile.js')
    .addEntry( 'updatecounters', './assets/js/updateCounters.js')
    .addEntry( 'lightbox', './assets/js/lightbox.js')
    .addEntry( 'ckeditor5', './assets/js/ckeditor5.js')

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
    .addLoader({
        test: require.resolve('select2'),
        use: "imports-loader?define=>false"
    })
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
