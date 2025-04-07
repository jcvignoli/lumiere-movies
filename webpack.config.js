// WordPress webpack config - webpack.config.js
import wpConfig from '@wordpress/scripts/config/webpack.config.js';
const CopyPlugin = require("copy-webpack-plugin");

// Utilities
import { resolve, relative, dirname } from 'path';

console.log('Running ./webpack.config.js');

export default {
	...wpConfig,
	/**
	 * Add extra options here
	 */
//	optimization: {
//		minimize: true,
//		minimizer: [
//			new TerserPlugin({
//				parallel: 4,
//				test: /\.js(\?.*)?$/i,
//				exclude: [ /\/src\/assets\/js\/highslide\//, /\/src\/assets\/blocks\//, /\/src\/vendor\// ],
//			}),
//		],
//	},
//	plugins: [
//		new CopyPlugin({
//			patterns: [
//				{ from: "./src/**/*", to: "./dest" },
//				{ from: "other", to: "public" },
//			],
//			options: {
//				concurrency: 100,
//				gitignore: true,
//				ignore: [
//					"./src/vendor/bin/**",
//					"./src/vendor/duck7000/imdb-graphql-php/src/Psr/**",
//					"./src/vendor/duck7000/imdb-graphql-php/doc/**",
//					"./src/vendor/twbs/bootstrap/build/**",
//					"./src/vendor/twbs/bootstrap/js/**",
//					"./src/vendor/twbs/bootstrap/nuget/**",
//					"./src/vendor/twbs/bootstrap/scss/**",
//					"./src/vendor/twbs/bootstrap/site/**",
//					"./src/vendor/twbs/bootstrap/.github/**",
//					"./src/assets/blocks/**",
//				],
//			},
//		}),
//	],
};
