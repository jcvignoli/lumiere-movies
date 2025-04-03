// WordPress webpack config - webpack.config.js
import wpConfig from '@wordpress/scripts/config/webpack.config.js';

// Utilities
import { resolve, relative, dirname } from 'path';

console.log('Running ./webpack.config.js');

export default {
	...wpConfig,
	/**
	 * Add extra options here
	 */
};
