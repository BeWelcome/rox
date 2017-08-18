var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/assets/')
    .setPublicPath('/assets')
    .cleanupOutputBeforeBuild()

    .addEntry('rox', './src/AppBundle/Resources/js/app.js')

    // will output as web/build/global.css
    .addStyleEntry('bewelcome', './src/AppBundle/Resources/scss/bewelcome.scss')
    .enableSassLoader()
    .autoProvidejQuery()
    .enableSourceMaps(!Encore.isProduction())

// create hashed filenames (e.g. app.abc123.css)
// .enableVersioning()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();