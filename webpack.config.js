/**
 * This config file is a standalone, extending WordPress default config
 */
 
 // Configs
import wpConfig from '@wordpress/scripts/config/webpack.config.js';	/* WordPress webpack config */
import extCred from './.env.ssh.js';			/* private credentials for ssh */

// Plugins
import TerserPlugin from "terser-webpack-plugin";
import CopyPlugin from "copy-webpack-plugin";
import MiniCssExtractPlugin from "mini-css-extract-plugin";
import CssMinimizerPlugin from "css-minimizer-webpack-plugin";
import ImageMinimizerPlugin from "image-minimizer-webpack-plugin";

// Utilities
import { resolve, relative, dirname, join, parse } from 'path';
import { fileURLToPath } from 'url';

// Constants
const __dirname = process.cwd();

// Starting message
console.log('Running ./webpack.config.js');

export default {
	...wpConfig,
	output: {
	    path: resolve('./dist/'),
	},
	plugins: [
		...wpConfig.plugins,
		new CopyPlugin( {
			patterns: [
			{
				from: resolve( './src/' ),
				globOptions: {
					concurrency: 100,
					ignore: [
						"**/assets/**",
						"**/vendor/bin/*",
						"**/duck7000/imdb-graphql-php/doc/*",
						"**/duck7000/imdb-graphql-php/src/Psr/**",
						"**/vendor/twbs/bootstrap/build/*",
						"**/vendor/twbs/bootstrap/js/**",
						"**/vendor/twbs/bootstrap/nuget/*",
						"**/vendor/twbs/bootstrap/scss/**",
						"**/vendor/twbs/bootstrap/site/**",
						"**/vendor/twbs/bootstrap/.github/**",
						"**/vendor/twbs/bootstrap/package.js",
						"**/vendor/twbs/bootstrap/hugo.yml",
						"**/vendor/twbs/bootstrap/package.json",
						"**/vendor/twbs/bootstrap/package-lock.json",
						"**/vendor/twbs/bootstrap/.*",
						"**/vendor/twbs/bootstrap/**/*.(map)",
						"**/class/updates/.add_only_updates",
					],
				},
			},
			{
				from: resolve( './src/assets/css/' ),
				globOptions: {
					concurrency: 100,
				},
				to({ context, absoluteFilename }) {
					/** add .min to filename */
					return 'assets/css/[name].min[ext]';
				},	
			},
			{
				from: resolve( './src/assets/js/*.(js)' ),
				globOptions: {
					concurrency: 100,
				},
				to({ context, absoluteFilename }) {
					/** add .min to filename */
					return 'assets/js/[name].min[ext]';
				},	
			},
			{
				from: resolve( './src/assets/js/highslide/**/*.*' ),
				globOptions: {
					concurrency: 100,
				},
				to({ context, absoluteFilename }) {
					/**
					 * @description Remove first & last item from ${path} array.
					 * @example
					 *      Orginal Path: 'src/images/avatar/image.jpg'
					 *      Changed To: 'images/avatar'
					 * We don't add .min to filename
					 */
					const path = absoluteFilename.split("/").slice(7, -1).join("/");
					return `${path}/[name][ext]`;
				},
			},
			{
				from: resolve( './src/assets/pics/**/*.*' ),
				globOptions: {
					concurrency: 100,
				},
				to({ context, absoluteFilename }) {
					/**
					 * @description Remove first & last item from ${path} array.
					 * @example
					 *      Orginal Path: 'src/images/avatar/image.jpg'
					 *      Changed To: 'images/avatar'
					 */
					const path = absoluteFilename.split("/").slice(7, -1).join("/");
					return `${path}/[name][ext]`;
				},	
			},
			]
		} ),
	],
	optimization: {
		minimize: true,
		minimizer: [
			new TerserPlugin({
				parallel: 10,
				test: /\.js$/i,
				exclude: [ /assets\/blocks\//, /assets\/js\/highslide\//, /vendor\// ],
			}),
			new CssMinimizerPlugin({
				minimizerOptions: {
					preset: [
						"default",
						{
							discardComments: { removeAll: true },
						},
					],
				},
				parallel: 10,
				test: /\.css$/i,
				exclude: [ /assets\/blocks\//, /vendor\// ],
			}),
			new ImageMinimizerPlugin({
				minimizer: {
					implementation: ImageMinimizerPlugin.imageminMinify,
					options: {
						// Lossless optimization with custom option
						plugins: [
							["gifsicle", { interlaced: true }],
							["mozjpeg", { progressive: true }],
							["optipng", { optimizationLevel: 5 }],
						],
					},
				},
				exclude: [ /assets\/blocks\//, /assets\/js\/highslide\//, /vendor\// ],
			}),
		],
	}
};
