var Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/contacts', './assets/js/contacts.js')

    .addEntry('js/attempt/index', './assets/js/attempt/index.ts')
    .addEntry('js/attempt/solve', './assets/js/attempt/solve.ts')

    .addEntry('js/profile/index', './assets/js/profile/index.js')
    .addEntry('js/homework/index', './assets/js/homework/index.ts')
    .addEntry('js/teacher/index', './assets/js/teacher/index.js')

    .addEntry('js/student/index', './assets/js/student/index.ts')
    .addEntry('js/student/attempts', './assets/js/student/attempts.ts')
    .addEntry('js/student/examples', './assets/js/student/examples.ts')

    .addEntry('js/task/index', './assets/js/task/index.ts')
    .addEntry('js/task/new', './assets/js/task/new.ts')
    .addEntry('js/task/edit', './assets/js/task/edit.ts')
    .addEntry('js/task/show', './assets/js/task/show.ts')
    .addEntry('js/task/contractor/attempts', './assets/js/task/contractor/attempts.ts')
    .addEntry('js/task/contractor/examples', './assets/js/task/contractor/examples.ts')

    .addEntry('js/security/login', './assets/js/security/login.ts')
    .addEntry('js/security/register', './assets/js/security/register.ts')

    .addEntry('js/homepage/student', './assets/js/homepage/student.ts')
    .addEntry('js/homepage/teacher', './assets/js/homepage/teacher.ts')

    .addStyleEntry('css/app', './assets/css/app.css')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .disableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    .enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')
;

module.exports = Encore.getWebpackConfig();
