/**
 * This Webpack config file extends WordPress default config
 * It copies all php/txt files from ./src to ./dist, minimize css & js & pics, build wp blocks, and upload by ssh the outcome
 * An sync browser is also available, start it by clicking on the link in the terminal
 */

// Configs
import wpConfig from '@wordpress/scripts/config/webpack.config.js';			/* WordPress webpack config */
import extCred from './.env.ssh.js';							/* Private credentials for ssh */

// Plugins
// a. From wp-scripts
import TerserPlugin from "terser-webpack-plugin";					/* already installed through WordPress scripts */
import CopyPlugin from "copy-webpack-plugin";						/* already installed through WordPress scripts */
// b. need the NPM package to be installed
import CssMinimizerPlugin from "css-minimizer-webpack-plugin";
import ImageMinimizerPlugin from "image-minimizer-webpack-plugin";
import SSHWatchUploadWebpackPlugin from '@alexrah/ssh-watch-upload-webpack-plugin';	/* forked from ssh-watch-upload-webpack-plugin */
import BrowserSyncPlugin from 'browser-sync-webpack-plugin';
import RemoveEmptyScriptsPlugin from 'webpack-remove-empty-scripts';
import * as sassTransform  from 'sass';

// Utilities
import { resolve, relative, dirname, join, parse } from 'path';
import getCmdArgs from './scripts/cmd-line-args/index.js';				/* extract arguments from command-line */

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

		// Include WordPress plugins but filter out RTL plugin
		...wpConfig.plugins.filter(
			(plugin) => plugin.constructor.name !== 'RtlCssPlugin'
		),
		
		// Remove empty JS files after WordPress asset generation
		new RemoveEmptyScriptsPlugin({
			stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
		}),
		
		// Runs only if "--watch" is passed in command-line
		new BrowserSyncPlugin({
			proxy: {
				target: extCred.proxy.address_http, /* must be in http, not in https, certif error otherwise */
				proxyReq: [
					function(proxyReq) {
						// Allows to use lumiere codeception database
						proxyReq.setHeader('X-Testing', 'true');
					}
				],
			},
			// Don't show any notifications in the browser
			notify:true,
			// port: 8080,
			// Tunnel  the Browsersync server through a Public URL
			// tunnel: true,
			// Additional info about the process, "info", "debug", "warn", or "silent", default: "info"
			// logLevel: "debug",
			// Stop the browser from automatically opening
			open: false,
			// Time, in milliseconds, to wait before instructing the browser to reload/inject following a file change event
			reloadDelay: 5, // Need to wait until src/ is copied to dist/
			// Will not attempt to determine your network status, assumes you're OFFLINE
			online: false,
		}),
		// Runs only if "--mode development" is passed in command line
		new SSHWatchUploadWebpackPlugin({
			mode: getCmdArgs.mode==='development' ? 'development' : 'production',
			host: extCred.mainserver.hostname,
			port: extCred.mainserver.port,
			username: extCred.mainserver.username,
			privateKeyPath: extCred.mainserver.key,
			uploadPath: extCred.mainserver.dist,
		}),
		new CopyPlugin( {
			patterns: [
			/****** All */
			{
				from: resolve( './src/' ),
				globOptions: {
					concurrency: 100,
					ignore: [
						"**/assets/**",
						"**/vendor/bin/*",
						"**/duck7000/imdb-graphql-php/doc/*",
						"**/duck7000/imdb-graphql-php/src/Psr/**",
						"**/vendor/twbs/bootstrap/**",
						"**/class/updates/.add_only_updates",
					],
				},
			},
			/****** Render files in block folders */
			{
				from: resolve( './src/assets/blocks/**/render.php' ),
				to({ context, absoluteFilename }) {
					const path = absoluteFilename.split("/").slice(7, -1).join("/");
					return `${path}/[name][ext]`;
				},
			},
			/****** Bootstrap */
			{
				from: resolve( './src/vendor/twbs/bootstrap/dist/**/*.(css|js)' ),
				globOptions: {
					concurrency: 100,
					ignore: [
						"**/vendor/twbs/bootstrap/dist/**/*[^min].(css|js)",
					],
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
			/****** CSS */
			{
				from: resolve( './src/assets/css/*.css' ),
				globOptions: {
					concurrency: 100,
				},
				to({ context, absoluteFilename }) {
					/** add .min to filename */
					return 'assets/css/[name].min.css';
				},	
			},
			/****** SCSS */
			{
				from: resolve( './src/assets/**/*.scss' ),
				globOptions: {
					concurrency: 100,
				},
				to({ context, absoluteFilename }) {
					const path = absoluteFilename.split("/").slice(7, -1).join("/");
					/** add .min to filename */
					return `${path}/[name].min.css`;
				},
				noErrorOnMissing: true,
				// compile scss into css
				transform: (content, path) => {
					return sassTransform.compile(path).css
				},	
			},
			/****** JS */
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
			/****** Highslide JS */
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
					 */
					const path = absoluteFilename.split("/").slice(7, -1).join("/");
					return `${path}/[name][ext]`;
				},
			},
			/****** Pics */
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
				extractComments: false,  /* avoid creation of licence and other useless files in blocks */
				terserOptions: {
					format: {
						comments: false,
					},
				},
				parallel: 10,
				test: /\.js$/i,
				exclude: [ /assets\/js\/highslide\//, /vendor\// ],
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
				// Scss have already been renamed to .min.css
				exclude: [ /vendor\// ],
			}),
			new ImageMinimizerPlugin({
				minimizer: {
					implementation: ImageMinimizerPlugin.imageminMinify,
					options: {
						// Lossless optimization with custom option
						plugins: [
							["gifsicle", { interlaced: true }],
							["jpegtran", { progressive: true }],
							["optipng", { optimizationLevel: 5 }],
						],
					},
				},
				exclude: [ /assets\/js\/highslide\//, /vendor\// ],
			}),
		],
	},
	
	performance: {
		maxAssetSize: 512000,
	},
};
