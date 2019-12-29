const CKEditorWebpackPlugin = require( '@ckeditor/ckeditor5-dev-webpack-plugin' );
const { styles } = require( '@ckeditor/ckeditor5-dev-utils' );
var Encore = require('@symfony/webpack-encore');

Encore
//    .configureRuntimeEnvironment('dev')
    .addPlugin( new CKEditorWebpackPlugin( {
        // Main language that will be built into the main bundle.
        language: 'en',

        // Additional languages that will be emitted to the `outputDirectory`.
        // This option can be set to an array of language codes or `'all'` to build all found languages.
        // The bundle is optimized for one language when this option is omitted.
        additionalLanguages: 'all',

        // Optional directory for emitted translations. Relative to the webpack's output.
        // Defaults to `'translations'`.
        outputDirectory: 'cktranslations',

        // Whether the build process should fail if an error occurs.
        // Defaults to `false`.
        // strict: true,

        // Whether to log all warnings to the console.
        // Defaults to `false`.
        // verbose: true
    } ) )
    .enableSingleRuntimeChunk()
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .addEntry('bewelcome', './assets/js/bewelcome.js')
//     .addEntry('print', './assets/scss/print.scss')
    .addEntry('jquery_ui', './assets/js/jquery_ui.js')
    .addEntry('backwards', './assets/js/backwards.js')
    .addEntry('signup/signup', './assets/js/signup.js')
    .addEntry('landing', './assets/js/landing/landing.js')
    .addEntry('scrollmagic', './assets/js/scrollmagic.js')
    .addEntry('search/searchpicker', './assets/js/search/searchpicker.js')
    .addEntry('search/loadcontent', './assets/js/search/loadajax.js')
    .addEntry('search/search', './assets/js/search/search.js')

    .addEntry('tempusdominus', './assets/js/tempusdominus.js')
    .addEntry('requests', './assets/js/requests.js')
    .addEntry('treasurer', './assets/js/treasurer.js')
    .addEntry('activities', './assets/js/activities/edit_create.js')
    .addEntry('leaflet', './assets/js/leaflet.js')
    .addEntry('member/autocomplete', './assets/js/member/autocomplete.js')
    .addEntry('admin/faqs', './assets/js/admin/faqs.js')
    .addEntry('chartjs', './node_modules/chart.js/dist/Chart.js')
    .addEntry('offcanvas', './assets/js/offcanvas.js')
    .addEntry('profile/profile', './assets/js/profile.js')
    .addEntry( 'updatecounters', './assets/js/updateCounters.js')
    .addEntry( 'lightbox', './assets/js/lightbox.js')
    .addEntry( 'gallery', './assets/js/gallery.js')
    .addEntry('bsfileselect', './assets/js/bsfileselect.js')
    .addEntry('email', './assets/scss/email.scss')
    .addEntry( 'roxeditor', './assets/js/roxeditor.js')

    .enableSassLoader()
    // allow legacy applications to use $/jQuery as a global variable, make popper visible for bootstrap
    .autoProvidejQuery()
    .autoProvideVariables({
        Popper: ['popper.js', 'default'],
    })
    .addAliases({
        'TweenLite': 'gsap/src/minified/TweenLite.min.js',
        'TweenMax': 'gsap/src/minified/TweenMax.min.js',
        'TimelineLite': 'gsap/src/minified/TimelineLite.min.js',
        'TimelineMax': 'gsap/src/minified/TimelineMax.min.js',
        'ScrollMagic': 'scrollmagic/scrollmagic/minified/ScrollMagic.min.js',
        'animation.gsap': 'scrollmagic/scrollmagic/minified/plugins/animation.gsap.min.js',
        'debug.addIndicators': 'scrollmagic/scrollmagic/minified/plugins/debug.addIndicators.min.js'
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
    .enableVersioning(true)
    .addPlugin( new CKEditorWebpackPlugin( {
        // See https://ckeditor.com/docs/ckeditor5/latest/features/ui-language.html
        language: 'en',
        additionalLanguages: 'all',
    } ) )

    // Use raw-loader for CKEditor 5 SVG files.
    .addRule( {
        test: /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/,
        loader: 'raw-loader'
    } )

    // Configure other image loaders to exclude CKEditor 5 SVG files.
    .configureLoaderRule( 'images', loader => {
        loader.exclude = /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/;
    } )

    // Configure PostCSS loader.
    .addLoader({
        test: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css$/,
        loader: 'postcss-loader',
        options: styles.getPostCssConfig( {
            themeImporter: {
                themePath: require.resolve('@ckeditor/ckeditor5-theme-lark')
            }
        } )
    } );

// console.log(JSON.stringify(Encore.getWebpackConfig(), null, 4));

module.exports = Encore.getWebpackConfig();
