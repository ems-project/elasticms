require('webpack');
const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

let config = {
    mode: 'production',
    watchOptions: {
        poll: true
    },
    plugins: [
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: ['**/*'],
        }),
        new CopyPlugin({
            patterns: [{
                from: "img/**",
                globOptions: {
                    dot: true,
                    gitignore: true,
                    ignore: [".github/**/*", "**/*.php"],
                }
            },{
                from: "other/**",
                globOptions: {
                    dot: true,
                    gitignore: true,
                    ignore: [".github/**/*", "**/*.php"],
                }
            }],
        }),
        new MiniCssExtractPlugin({
            filename: "css/[name].css",
            chunkFilename: "[id].css"
        }),
    ],
    context: path.resolve(__dirname, './src/'),
    entry: {
        'index': './index.js',
        'admin': './admin.js',
        'reveal': './slideshow.js',
    },
    output: {
        path: path.resolve(__dirname, "./dist"),
        filename: "./js/[name].js"
    },
    devtool: 'source-map',
    module: {
        rules: [
            {
                test: /\.less$/,
                use: [{
                    loader: MiniCssExtractPlugin.loader,
                    options: {
                        // you can specify a publicPath here
                        // by default it use publicPath in webpackOptions.output
                        publicPath: '../'
                    }
                },{
                    loader: 'css-loader',
                    options: {
                        sourceMap: true
                    }
                }, {
                    loader: 'less-loader'
                }]
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [{
                    loader: MiniCssExtractPlugin.loader,
                    options: {
                        // you can specify a publicPath here
                        // by default it use publicPath in webpackOptions.output
                        publicPath: '../'
                    }
                },{
                    loader: 'css-loader',
                    options: {
                        sourceMap: true
                    }
                },{
                    loader: 'sass-loader',
                    options: {
                      sourceMap: true,
                    }
                }],
            },
            {
                test: /\.(png|jpg|gif|svg|eot|ttf|woff|woff2)$/,
                loader: 'url-loader',
                options: {
                    limit: 10000,
                    name: 'media/[name].[ext]',
                }
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: [{
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env']
                        ]
                    }
                }]
            },
        ]
    }
}

module.exports = config;
