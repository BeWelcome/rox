const CKEditorWebpackPlugin = require('@ckeditor/ckeditor5-dev-webpack-plugin');
const {styles} = require('@ckeditor/ckeditor5-dev-utils');

let Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    .addPlugin(new CKEditorWebpackPlugin({
        language: 'en',
        additionalLanguages: 'all',
        outputDirectory: 'cktranslations',
        buildAllTranslationsToSeparateFiles: true
    }))
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .addEntry('bewelcome', './assets/js/bewelcome.js')
    .addEntry('floating_labels', './assets/scss/floating_labels.scss')
    .addEntry('jquery_ui', './assets/js/jquery_ui.js')
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
    .addEntry('updatecounters', './assets/js/updateCounters.js')
    .addEntry('lightbox', './assets/js/lightbox.js')
    .addEntry('gallery', './assets/js/gallery.js')
    .addEntry('bsfileselect', './assets/js/bsfileselect.js')
    .addEntry('email', './assets/scss/email.scss')
    .addEntry('roxeditor', './assets/js/roxeditor.js')
    .addEntry('rangeslider', './assets/js/rangeslider.js')
    .addEntry('faq', './assets/js/faq.js')
    //    .addEntry('roxinlineeditor', './assets/js/roxinlineeditor.js')

    .enableSassLoader(options => {
        // Prefer using sass instead of node-sass to not depend on Python
        options.implementation = require('sass');
    })
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
        }, {
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
    // Use raw-loader for CKEditor 5 SVG files.
    .addRule({
        test: /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/,
        loader: 'raw-loader'
    })

    // Configure other image loaders to exclude CKEditor 5 SVG files.
    .configureLoaderRule('images', loader => {
        loader.exclude = /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/;
    })

    // Configure PostCSS loader.
    .addLoader({
        test: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css$/,
        loader: 'postcss-loader',
        options: styles.getPostCssConfig({
            themeImporter: {
                themePath: require.resolve('@ckeditor/ckeditor5-theme-lark')
            }
        })
    })
;

const assetsConfig = Encore.getWebpackConfig();

const path = require('path');
const WorkboxPlugin = require('workbox-webpack-plugin');

workboxConfig = {
        mode: Encore.isProduction() ? 'production' : 'development',
        entry: {
            main: "./assets/js/index.js"
        },
        output: {
            filename: "[name].js",
            chunkFilename: "[name].bundle.js",
            path: path.resolve(__dirname, "public")
        },
        plugins: [
        new WorkboxPlugin.InjectManifest({
           // these options encourage the ServiceWorkers to get in there fast
           // and not allow any straggling "old" SWs to hang around
           // clientsClaim: true,
           // skipWaiting: true,
           swSrc: './assets/js/sw.js',
            swDest: './service-worker.js'
        }),
        ],
        devtool: "source-map"
};

module.exports = [ assetsConfig, workboxConfig ];
