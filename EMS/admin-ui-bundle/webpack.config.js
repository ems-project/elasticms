const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CopyPlugin = require("copy-webpack-plugin");
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { CKEditorTranslationsPlugin } = require( '@ckeditor/ckeditor5-dev-translations' );
const { styles } = require( '@ckeditor/ckeditor5-dev-utils' );

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
            new CKEditorTranslationsPlugin( {
                // See https://ckeditor.com/docs/ckeditor5/latest/features/ui-language.html
                language: 'en'
            } ),
        ],
        optimization: {
            minimizer: [new TerserPlugin({
                extractComments: false,
            })],
        },
        context: path.resolve(__dirname, './'),
        entry: {
            'app': './assets/js/app.js',
            'action': './assets/js/action.js',
            'hierarchical': './assets/js/hierarchical.js',
            'i18n': './assets/js/i18n.js',
            'managed-alias': './assets/js/managed-alias.js',
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
                    exclude: /ckeditor5-/,
                    use: [
                        {loader: MiniCssExtractPlugin.loader, options: {publicPath: '../'}},
                        {loader: 'css-loader', options: {sourceMap: (argv.mode !== 'production')}},
                        {loader: 'sass-loader', options: {sourceMap: (argv.mode !== 'production')}}
                    ],
                },
                {
                    test: /\.(png|jpg|gif|svg)$/,
                    exclude: /ckeditor5-/,
                    type: 'asset/inline',
                },
                {
                    test: /\.(woff|woff2|eot|ttf|otf)$/,
                    exclude: /ckeditor5-/,
                    type: 'asset/resource',
                    generator: {
                        filename: 'media/[name][contenthash][ext]'
                    }
                },
                {
                    test: /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/,
                    use: [ 'raw-loader' ]
                },
                {
                    test: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css$/,
                    use: [
                        {
                            loader: 'style-loader',
                            options: {
                                injectType: 'singletonStyleTag',
                                attributes: {
                                    'data-cke': true
                                }
                            }
                        },
                        'css-loader',
                        {
                            loader: 'postcss-loader',
                            options: {
                                postcssOptions: styles.getPostCssConfig( {
                                    themeImporter: {
                                        themePath: require.resolve( '@ckeditor/ckeditor5-theme-lark' )
                                    },
                                    minify: true
                                } )
                            }
                        }
                    ]
                }
            ]
        }
    }
}
