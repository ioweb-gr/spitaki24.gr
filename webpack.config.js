var Encore = require('@symfony/webpack-encore');

Encore
// the project directory where all compiled assets will be stored
    .setOutputPath('wp-content/plugins/ioweb-customizer/assets/build')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/wp-content/plugins/ioweb-customizer/assets/build')

    // will create public/build/app.js and public/build/app.css
    .addEntry('ioweb-custom', './assets/js/ioweb-custom.js')
	.enableSingleRuntimeChunk()

    // allow legacy applications to use $/jQuery as a global variable
    // .autoProvidejQuery()

    // enable source maps during development
    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    .enableBuildNotifications()

    // create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning()

    // allow sass/scss files to be processed
    .enableSassLoader()
;

// export the final configuration

let config = Encore.getWebpackConfig();
config.watchOptions = {
    aggregateTimeout: 300,
    poll: 1000
};

module.exports = config;