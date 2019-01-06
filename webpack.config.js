const CKEditorWebpackPlugin = require( '@ckeditor/ckeditor5-dev-webpack-plugin' );
const { styles } = require( '@ckeditor/ckeditor5-dev-utils' );
var Encore = require('@symfony/webpack-encore');

Encore
//updatecounter    .configureRuntimeEnvironment('dev')
    .enableSingleRuntimeChunk()
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    // .cleanupOutputBeforeBuild()

    .addEntry('bewelcome', './assets/js/bewelcome.js')
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
    .addEntry('leaflet', './assets/js/leaflet.js')
    .addEntry('member/autocomplete', './assets/js/member/autocomplete.js')
    .addEntry('admin/faqs', './assets/js/admin/faqs.js')
    .addEntry('chartjs', './node_modules/chart.js/dist/Chart.js')
    .addEntry('offcanvas', './assets/js/offcanvas.js')
    .addEntry('profile/profile', './assets/js/profile.js')
    .addEntry( 'updatecounters', './assets/js/updateCounters.js')
    .addEntry( 'lightbox', './assets/js/lightbox.js')
    .addEntry('bsfileselect', './assets/js/bsfileselect.js')
    // .addEntry( 'roxeditor', './assets/js/roxeditor.js')

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
/*    .addLoader( {
        test: /\.svg$/,
        use: "raw-loader"
    })
    .enablePostCssLoader(options => {
         Object.assign(options, styles.getPostCssConfig({
             themeImporter: {
                 themePath: require.resolve( '@ckeditor/ckeditor5-theme-lark' )
             }
         }));
    })
*/
 .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(function(babelConfig) {
        // add additional presets
        // babelConfig.presets.push('@babel/preset-flow');

        // no plugins are added by default, but you can add some
        // babelConfig.plugins.push('styled-jsx/babel');
    }, {
        // node_modules is not processed through Babel by default
        // but you can whitelist specific modules to process
        include_node_modules: ['bootstrap']

        // or completely control the exclude
        // exclude: /node_modules/
    })
;

// console.log(JSON.stringify(Encore.getWebpackConfig(), null, 4));

module.exports = Encore.getWebpackConfig();
