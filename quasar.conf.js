/* eslint-env node */
const nodePath = require('path')
const nodeFs = require('fs').promises
const fsMove = require('fs-move')
const merge = require('webpack-merge')
/*
 * This file runs in a Node context (it's NOT transpiled by Babel), so use only
 * the ES6 features that are supported by your Node version. https://node.green/
 */

// Configuration for your app
// https://v1.quasar.dev/quasar-cli/quasar-conf-js


module.exports = function (ctx) {
  const env = require('dotenv').config().parsed
  const moveDist = (from, to) => {
    return fsMove(nodePath.resolve(__dirname, 'dist', from), nodePath.resolve(__dirname, 'public', from), {
      overwrite: true
    })
  }

  return {
    // https://v1.quasar.dev/quasar-cli/supporting-ts
    supportTS: false,

    // https://v1.quasar.dev/quasar-cli/prefetch-feature
    preFetch: true,

    // app boot file (/src/boot)
    // --> boot files are part of "main.js"
    // https://v1.quasar.dev/quasar-cli/boot-files
    boot: [
      'constant',
      'storage',
      'axios',
      'i18n',
      'addressbar-color',
      'portal-vue',
      'auth',
      'breadcrumbs',
      'utils',
      'notify',
      'global-components',
      'global-listeners',
      'tour-guide'
      // 'smooth-picker'
    ],

    // https://v1.quasar.dev/quasar-cli/quasar-conf-js#Property%3A-css
    css: [
      'fonts.scss',
      'app.scss'
    ],

    // https://github.com/quasarframework/quasar/tree/dev/extras
    extras: [
      // 'ionicons-v4',
      // 'mdi-v5',
      // 'fontawesome-v6',
      // 'eva-icons',
      // 'themify',
      // 'line-awesome',
      // 'roboto-font-latin-ext', // this or either 'roboto-font', NEVER both!

      // 'roboto-font',
      'material-icons', // optional, you are not bound to it
    ],

    // Full list of options: https://v1.quasar.dev/quasar-cli/quasar-conf-js#Property%3A-build
    build: {
      env,
      vueRouterMode: 'history', // available values: 'hash', 'history'

      distDir: nodePath.join('dist'),
      // publicPath: 'http://localhost:8000/',

      // transpile: false,

      // Add dependencies for transpiling with Babel (Array of string/regex)
      // (from node_modules, which are by default not transpiled).
      // Applies only if "transpile" is set to true.
      // transpileDependencies: [],

      // rtl: false, // https://v1.quasar.dev/options/rtl-support
      // preloadChunks: true,
      // showProgress: false,
      // gzip: true,
      // analyze: true,

      // Options below are automatically set depending on the env, set them if you want to override
      // extractCSS: false,

      // https://v1.quasar.dev/quasar-cli/handling-webpack
      // "chain" is a webpack-chain object https://github.com/neutrinojs/webpack-chain
      chainWebpack (cfg, { isServer, isClient}) {
        if (ctx.dev) {
          cfg.output.publicPath(`http://localhost:${env.APP_DEV_SERVER_PORT || 8080}/`)
        }

        cfg.resolve.alias.set('src', '/resources/quasar')
        // cfg.resolve.alias.set('app', '/')
        cfg.resolve.alias.set('components', '/resources/quasar/components')
        cfg.resolve.alias.set('layouts', '/resources/quasar/layouts')
        cfg.resolve.alias.set('pages', '/resources/quasar/pages')
        cfg.resolve.alias.set('assets', '/resources/quasar/assets')
        cfg.resolve.alias.set('boot', '/resources/quasar/boot')

        // const scssLoader = nodePath.resolve(__dirname, 'scss-variables.webpack.js')

        // cfg.module.rule('scss').oneOf('modules-query').use('quasar-scss-variables-loader').loader(scssLoader)
        // cfg.module.rule('scss').oneOf('modules-ext').use('quasar-scss-variables-loader').loader(scssLoader)
        // cfg.module.rule('scss').oneOf('normal').use('quasar-scss-variables-loader').loader(scssLoader)
      },
      async beforeBuild({ quasarConf }) {
        await nodeFs.rm(nodePath.resolve(__dirname, 'public/css'), { recursive: true, force: true })
        await nodeFs.rm(nodePath.resolve(__dirname, 'public/js'), { recursive: true, force: true })
        await nodeFs.rm(nodePath.resolve(__dirname, 'public/fonts'), { recursive: true, force: true })
      },
      async afterBuild({ quasarConf }) {
        const manifestJson = {}
        const cssFiles = await nodeFs.readdir(nodePath.resolve(__dirname, 'dist', 'css'))

        cssFiles.forEach(v => {
          if (v.startsWith('app')) {
            if (typeof manifestJson['/css/app.css'] === 'undefined') {
              manifestJson['/css/app.css'] = `/css/${v}`
            }
          } else if (v.startsWith('vendor')) {
            if (typeof manifestJson['/css/vendor.css'] === 'undefined') {
              manifestJson['/css/vendor.css'] = `/css/${v}`
            }
          }
        })
        const jsFiles = await nodeFs.readdir(nodePath.resolve(__dirname, 'dist', 'js'))

        jsFiles.forEach(v => {
          if (v.startsWith('app')) {
            if (typeof manifestJson['/js/app.js'] === 'undefined') {
              manifestJson['/js/app.js'] = `/js/${v}`
            }
          } else if (v.startsWith('vendor')) {
            // if (typeof manifestJson['/js/vendor.js'] === 'undefined') {
              manifestJson['/js/vendor.js'] = `/js/${v}`
            // }
          }
        })

        await nodeFs.writeFile(nodePath.resolve(__dirname, 'public', 'quasar-manifest.json'), JSON.stringify(manifestJson, null, 2))

        await moveDist('css', 'css')
        await moveDist('js', 'js')
        await moveDist('fonts', 'fonts')

        await nodeFs.rm(nodePath.resolve(__dirname, 'dist'), { recursive: true, force: true })
      },
      // onPublish(opts) {

      // }
      // scssLoaderOptions: {
      //   additionalData: `@import '~src/css/quasar.variables.scss', 'quasar/src/css/variables.sass';\n`,
      //   sassOptions: {
      //     prependData: `@import '~src/css/quasar.variables.scss', 'quasar/src/css/variables.sass';\n`
      //   }
      // }
    },

    // Full list of options: https://v1.quasar.dev/quasar-cli/quasar-conf-js#Property%3A-devServer
    devServer: {
      https: false,
      port: env.APP_DEV_SERVER_PORT || 8080,
      public: `localhost:${env.APP_DEV_SERVER_PORT || 8080}`,
      open: false,
      headers: {
        'access-control-allow-origin': '*',
        'access-control-allow-methods': 'GET, POST, PUT, PATCH, DELETE, OPTIONS'
      }
    },

    // https://v1.quasar.dev/quasar-cli/quasar-conf-js#Property%3A-framework
    framework: {
      iconSet: 'material-icons', // Quasar icon set
      lang: 'en-us', // Quasar language pack
      config: {},

      // Possible values for "importStrategy":
      // * 'auto' - (DEFAULT) Auto-import needed Quasar components & directives
      // * 'all'  - Manually specify what to import
      importStrategy: 'auto',

      // For special cases outside of where "auto" importStrategy can have an impact
      // (like functional components as one of the examples),
      // you can manually specify Quasar components/directives to be available everywhere:
      //
      // components: [],
      // directives: [],

      // Quasar plugins
      plugins: [
        'AddressbarColor',
        'Cookies',
        'Dialog',
        'Notify',
        'Meta'
      ]
    },

    // animations: 'all', // --- includes all animations
    // https://v1.quasar.dev/options/animations
    animations: [
      'fadeIn',
      'fadeOut',
      'fadeInDown',
      'fadeOutDown',
      'fadeInLeft',
      'fadeOutLeft',
      'fadeInRight',
      'fadeOutRight'
    ],

    // https://v1.quasar.dev/quasar-cli/developing-ssr/configuring-ssr
    ssr: {
      pwa: false
    },

    // https://v1.quasar.dev/quasar-cli/developing-pwa/configuring-pwa
    pwa: {
      workboxPluginMode: 'GenerateSW', // 'GenerateSW' or 'InjectManifest'
      workboxOptions: {}, // only for GenerateSW
      manifest: {
        name: `com.ionnex.sss.console`,
        short_name: `com.ionnex.sss.console`,
        description: `SSS Console`,
        display: 'standalone',
        orientation: 'portrait',
        background_color: '#ffffff',
        theme_color: '#027be3',
        icons: [
          {
            src: 'icons/icon-128x128.png',
            sizes: '128x128',
            type: 'image/png'
          },
          {
            src: 'icons/icon-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'icons/icon-256x256.png',
            sizes: '256x256',
            type: 'image/png'
          },
          {
            src: 'icons/icon-384x384.png',
            sizes: '384x384',
            type: 'image/png'
          },
          {
            src: 'icons/icon-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    },

    // Full list of options: https://v1.quasar.dev/quasar-cli/developing-cordova-apps/configuring-cordova
    cordova: {
      // noIosLegacyBuildFlag: true, // uncomment only if you know what you are doing
    },

    // Full list of options: https://v1.quasar.dev/quasar-cli/developing-capacitor-apps/configuring-capacitor
    capacitor: {
      hideSplashscreen: true
    },

    // Full list of options: https://v1.quasar.dev/quasar-cli/developing-electron-apps/configuring-electron
    electron: {
      bundler: 'packager', // 'packager' or 'builder'

      packager: {
        // https://github.com/electron-userland/electron-packager/blob/master/docs/api.md#options

        // OS X / Mac App Store
        // appBundleId: '',
        // appCategoryType: '',
        // osxSign: '',
        // protocol: 'myapp://path',

        // Windows only
        // win32metadata: { ... }
      },

      builder: {
        // https://www.electron.build/configuration/configuration

        appId: 'sss-console'
      },

      // More info: https://v1.quasar.dev/quasar-cli/developing-electron-apps/node-integration
      nodeIntegration: true,

      extendWebpack (/* cfg */) {
        // do something with Electron main process Webpack cfg
        // chainWebpack also available besides this extendWebpack
      }
    },
    sourceFiles: {
      rootComponent: 'resources/quasar/App.vue',
      router: 'resources/quasar/router',
      store: 'resources/quasar/store',
      indexHtmlTemplate: 'resources/views/quasar.blade.php',
      registerServiceWorker: 'resources/quasar-pwa/register-service-worker.js',
      serviceWorker: 'resources/quasar-pwa/custom-service-worker.js',
      electronMainDev: 'resources/quasar-electron/main-process/electron-main.dev.js',
      electronMainProd: 'resources/quasar-electron/main-process/electron-main.js'
    },
    ignorePublicFolder: true
  }
}
