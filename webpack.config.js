var Encore = require('@symfony/webpack-encore');

const CKEditorWebpackPlugin = require( '@ckeditor/ckeditor5-dev-webpack-plugin' );

const { styles } = require( '@ckeditor/ckeditor5-dev-utils' );

Encore
    .configureRuntimeEnvironment('dev')
    .enableSingleRuntimeChunk()
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()

    .createSharedEntry('bewelcome', './src/AppBundle/Resources/js/bewelcome.js')
    .addEntry('jquery_ui', './src/AppBundle/Resources/js/jquery_ui.js')
    .addEntry('backwards', './src/AppBundle/Resources/js/backwards.js')
    .addEntry('skrollr', './src/AppBundle/Resources/js/skrollr.js')
    .addEntry('signup', './src/AppBundle/Resources/js/signup.js')
    .addEntry('landing', './src/AppBundle/Resources/public/js/landing/landing.js')

    .addEntry('search/searchpicker', './src/AppBundle/Resources/public/js/search/searchpicker.js')
    .addEntry('search/createmap', './src/AppBundle/Resources/public/js/search/createmap.js')
    .addEntry('tempusdominus', './src/AppBundle/Resources/js/tempusdominus.js')
    .addEntry('requests', './src/AppBundle/Resources/js/requests.js')
    .addEntry('treasurer', './src/AppBundle/Resources/js/treasurer.js')
    .addEntry('leaflet', './src/AppBundle/Resources/js/leaflet.js')
    .addEntry('member/autocomplete', './src/AppBundle/Resources/js/member/autocomplete.js')
    .addEntry('admin/faqs', './src/AppBundle/Resources/js/admin/faqs.js')
    .addEntry('chartjs', './node_modules/chart.js/dist/Chart.js')
    .addEntry('offcanvas', './src/AppBundle/Resources/public/js/offcanvas.js')
    .addEntry('profile/profile', './src/AppBundle/Resources/js/profile.js')
    .addEntry('ckeditor5', './src/AppBundle/Resources/js/ckeditor5.js')

    .enableSassLoader()
    // allow legacy applications to use $/jQuery as a global variable, make popper visible for bootstrap
    .autoProvidejQuery()
    .autoProvideVariables({
        Popper: ['popper.js', 'default'],
    })

    .addPlugin(
        new CKEditorWebpackPlugin( {
            // See https://docs.ckeditor.com/ckeditor5/latest/features/ui-language.html
            language: 'en',

            // Additional languages that will be emitted to the `outputDirectory`.
            // This option can be set to an array of language codes or `'all'` to build all found languages.
            // The bundle is optimized for one language when this option is omitted.
            additionalLanguages: 'all',
        } )
    )
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
    .addLoader({
        test: /\.svg$/,
        use: [{
            loader: "raw-loader"
        }]
    })
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
