const path = require('path')
const webpack = require('webpack')
const { WebpackManifestPlugin } = require('webpack-manifest-plugin')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
const TerserPlugin = require('terser-webpack-plugin')
const ESLintPlugin = require('eslint-webpack-plugin')

module.exports = {
    mode: 'production',
    entry: {
        form: './assets/js/form.js',
        formDebug: './assets/js/formDebug.js',
        debug: './assets/js/debug.js',
        backend: './assets/js/backend.js',
        dynamicFields: './assets/js/dynamicFields.js',
        validation: './assets/js/validation.js'
    },
    optimization: {
        minimizer: [
            new TerserPlugin({
                extractComments: false
            })
        ]
    },
    plugins: [
        new ESLintPlugin({
            extensions: ['js', 'ts'],
            emitWarning: true // optionnel
        }),
        new WebpackManifestPlugin({ publicPath: 'bundles/emsform/' }),
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: ['**/*', '!static/**']
        })
    ],
    output: {
        filename: 'js/[name].js',
        path: path.resolve(__dirname, 'public')
    },
    resolve: {
        fallback: {
            crypto: false,
            buffer: require.resolve('buffer'),
            stream: require.resolve('stream-browserify'),
            vm: require.resolve('vm-browserify')
        }
    },
    module: {
        rules: [
            {
                test: /\.m?js$/,
                resolve: { fullySpecified: false }
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.(png|jpg|gif)$/i,
                use: [{ loader: 'url-loader', options: { limit: 10000, name: 'img/[name].[ext]' } }]
            }
        ]
    }
}
