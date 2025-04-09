/**
 * This config file is meant to be used along with gulp
 * It only uses the WordPress default config
 */

// Configs
import wpConfig from '@wordpress/scripts/config/webpack.config.js';

console.log('Running ./webpack.config.gulp.js');

export default {
	...wpConfig,
};
