const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CopyPlugin = require("copy-webpack-plugin");
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = (env, argv) => {
    return {
        plugins: [
            new webpack.ProvidePlugin({
                $: "jquery",
                jQuery: "jquery"
            }),
            new webpack.ProvidePlugin({
                Buffer: ['buffer', 'Buffer'],
            }),
            new webpack.ProvidePlugin({
                process: 'process/browser',
            }),
            new WebpackManifestPlugin({'publicPath': 'bundles/emsadminui/'}),
            new CleanWebpackPlugin({
                cleanOnceBeforeBuildPatterns: ['**/*', '!static/**'],
            }),
            new CopyPlugin({
                "patterns": [
                    {from: './assets/images', to: 'images/[name].[hash].[ext]'},
                ]
            }),
            new MiniCssExtractPlugin({
                filename: "css/[name].[contenthash].css",
                chunkFilename: "[id].css"
            }),
        ],
        optimization: {
            minimizer: [new TerserPlugin({
                extractComments: false,
            })],
        },
        context: path.resolve(__dirname, './'),
        entry: {
            'app': './assets/js/app.js',
        },
        output: {
            path: path.resolve(__dirname, 'src/Resources/public'),
            filename: 'js/[name].[contenthash].js',
        },
        devtool: 'source-map',
        module: {
            rules: [
                {
                    test: /\.(sa|sc|c)ss$/,
                    use: [
                        {loader: MiniCssExtractPlugin.loader, options: {publicPath: '../'}},
                        {loader: 'css-loader', options: {sourceMap: (argv.mode !== 'production')}},
                        {loader: 'sass-loader', options: {sourceMap: (argv.mode !== 'production')}}
                    ],
                },
                {
                    test: /\.(png|jpg|gif|svg)$/,
                    type: 'asset/inline',
                },
                {
                    test: /\.(woff|woff2|eot|ttf|otf)$/,
                    type: 'asset/resource',
                    generator: {
                        filename: 'media/[name][contenthash][ext]'
                    }
                }
            ]
        }
    }
}
