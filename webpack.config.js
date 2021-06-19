const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require('path');

module.exports = {
	...defaultConfig,
	entry: {
		'lumiere-block': './tmp/sidebar.js'
	},
	output: {
		path: path.join(__dirname, './build'),
		filename: '[name].js'
	}
}
