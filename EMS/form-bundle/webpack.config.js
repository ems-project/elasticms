const path = require('path');
const webpack = require('webpack');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    mode: 'production',
    entry: {
        'form': './src/Resources/assets/js/form.js',
        'formDebug': './src/Resources/assets/js/formDebug.js',
        'debug': './src/Resources/assets/js/debug.js',
        'backend': './src/Resources/assets/js/backend.js',
        'dynamicFields': './src/Resources/assets/js/dynamicFields.js',
        'validation': './src/Resources/assets/js/validation.js'
    },
    optimization: {
        minimizer: [new TerserPlugin({
            extractComments: false,
        })],
    },
    plugins: [
        new WebpackManifestPlugin({'publicPath': 'bundles/emsform/'}),
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: ['**/*', '!static/**'],
        }),
        new webpack.ProvidePlugin({
            Promise: 'core-js-pure/features/promise'
        })
    ],
    output: {
        filename: 'js/[name].js',
        path: path.resolve(__dirname, 'src/Resources/public')
    },
    resolve: {
        fallback: {
            "crypto": require.resolve("crypto-browserify"),
            "buffer": require.resolve("buffer"),
            "stream": require.resolve("stream-browserify"),
            "vm": require.resolve("vm-browserify")
        }
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.(png|jpg|gif)$/i,
                use: [
                    { loader: 'url-loader', options: { limit: 10000, name: 'img/[name].[ext]' } }
                ]
            },
            {
                enforce: 'pre',
                test: /\.js$/,
                exclude: /node_modules/,
                loader: 'eslint-loader',
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: { loader: 'babel-loader' }
            }
        ],
    }
};
