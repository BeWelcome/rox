let Encore = require('@symfony/webpack-encore');

const { CKEditorTranslationsPlugin } = require( '@ckeditor/ckeditor5-dev-translations' );
const { styles} = require('@ckeditor/ckeditor5-dev-utils');
const path = require('path');

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
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
//    .configureFontRule({
//        type: 'asset',
//        maxSize: 8*1024*1024,
//    })
    .addEntry('bewelcome', './assets/js/bewelcome.js')
    .addEntry('home', './assets/js/home.js')
    .addEntry('jquery_ui', './assets/js/jquery_ui.js')
    .addEntry('signup/signup', './assets/js/signup.js')
    .addEntry('members/editmyprofile', './assets/js/editmyprofile.js')
    .addEntry('landing', './assets/js/landing/landing.js')
    .addEntry('search/loadpicker', './assets/js/search/loadpicker.js')
    .addEntry('search/loadcontent', './assets/js/search/loadajax.js')
    .addEntry('search/locations', './assets/js/search/locations.js')
    .addEntry('search/map', './assets/js/search/map.js')
    .addEntry('tempusdominus', './assets/js/tempusdominus.js')
    .addEntry('requests', './assets/js/requests.js')
    .addEntry('message', './assets/js/message.js')
    .addEntry('trips', './assets/js/trips.js')
    .addEntry('micromodal', './assets/js/micromodal.js')
    .addEntry('treasurer', './assets/js/treasurer.js')
    .addEntry('activities', './assets/js/activities/edit_create.js')
    .addEntry('leaflet', './assets/js/leaflet.js')
    .addEntry('member/autocomplete', './assets/js/member/autocomplete.js')
    .addEntry('admin/faqs', './assets/js/admin/faqs.js')
    .addEntry('chartjs', './node_modules/chart.js/dist/chart.js')
    .addEntry('offcanvas', './assets/js/offcanvas.js')
    .addEntry('profile/profile', './assets/js/profile/profile.js')
    .addEntry('profile/setlocation', './assets/js/profile/setlocation.js')
    .addEntry('signup/setlocation', './assets/js/signup/setlocation.js')
    .addEntry('updatecounters', './assets/js/updateCounters.js')
    .addEntry('lightbox', './assets/js/lightbox.js')
    .addEntry('gallery', './assets/js/gallery.js')
    .addEntry('notes_filter', './assets/js/notes/filter.js')
    .addEntry('conversations', './assets/js/conversations.js')
    .addEntry('report', './assets/js/conversations/report.js')
    .addEntry('bsfileselect', './assets/js/bsfileselect.js')
    .addEntry('scrollingtabs', './assets/js/scrollingtabs.js')
    .addEntry('email', './assets/scss/email.scss')
    // CKEditor
    .addPlugin(new CKEditorTranslationsPlugin({
        language: 'en',
        additionalLanguages: 'all',
        outputDirectory: 'cktranslations',
        buildAllTranslationsToSeparateFiles: true
    }))
    .addEntry('roxeditor', './assets/js/roxeditor.js')
    .addEntry('rangeslider', './assets/js/rangeslider.js')
    .addEntry('highlight', './assets/js/highlight.js')
    .addEntry('faq', './assets/js/faq.js')
    .addEntry('translations', './assets/js/admin/translations.js')
    .addEntry('readmore', './assets/js/readmore.js')
    .addEntry('searchresults', './assets/js/searchresults.js')
    .addEntry('admin/tools/login_message', './assets/js/admin/tools/login_message.js')
    .addEntry('tailwind', './assets/tailwindcss/tailwind.css')
    // .addEntry('tom-select', './assets/js/tom-select')
    // react
    .configureBabel(function(babelConfig) {
        babelConfig.presets = [ "@babel/preset-env", '@babel/preset-react' ]
        babelConfig.plugins = [ '@babel/plugin-transform-runtime' ]
    })
    .addEntry('avatar', './assets/js/react/avatar/AvatarMount.jsx')

    .enableSassLoader(options => {
        // Prefer using sass instead of node-sass to not depend on Python
        options.implementation = require('sass');
    }, {
        resolveUrlLoader: true
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
    .enablePostCssLoader((options) => {
        // new option outlined here https://webpack.js.org/loaders/postcss-loader/
        options.postcssOptions = {
            config: './postcss.config.js',
        }
    })
    // Configure PostCSS loader.
    .addLoader({
        test: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css$/,
        loader: 'postcss-loader',
        options: {
            postcssOptions: styles.getPostCssConfig( {
                themeImporter: {
                    themePath: require.resolve( '@ckeditor/ckeditor5-theme-lark' )
                },
                minify: true
            } )
        }
    })
;

const assetsConfig = Encore.getWebpackConfig();

const WorkboxPlugin = require('workbox-webpack-plugin');

workboxConfig = {
        mode: 'production', /* Encore.isProduction() ? 'production' : 'development', */
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
