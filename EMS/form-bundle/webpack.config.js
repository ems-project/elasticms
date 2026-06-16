import path from 'path'
import { createRequire } from 'module'
import { WebpackManifestPlugin } from 'webpack-manifest-plugin'
import { CleanWebpackPlugin } from 'clean-webpack-plugin'
import TerserPlugin from 'terser-webpack-plugin'
import ESLintPlugin from 'eslint-webpack-plugin'

const require = createRequire(import.meta.url)
const __dirname = path.dirname(new URL(import.meta.url).pathname)

export default {
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
            emitWarning: true
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
