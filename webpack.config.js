const Encore = require('@symfony/webpack-encore');

// Configuration de l'environnement
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Répertoire de sortie
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    
    // Points d'entrée
    .addEntry('app', './assets/app.js')
    .addEntry('react-app', './assets/react/index.js')
    
    // Configuration générale
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeEach()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    // Configuration Babel
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    
    // Activation de React
    .enableReactPreset()
    
    // Si vous utilisez Sass (optionnel)
    .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
