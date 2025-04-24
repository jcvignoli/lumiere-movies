//import { registerBlockType } from '@wordpress/blocks';
//import { useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import './index.css';

/**
 * This is a fake file, meant to include only sidebar.js
 *
registerBlockType( jsonData.name, {
	edit: ( props ) => {
		 return (
			<p { ...useBlockProps() }>
				{ __( 'Example Post Meta – hello from the editor!', 'lumiere-movies' ) }
			</p>
		);
	},
	save: return null;
} );
 */

/**
 * The sidebar plugin
 */
import './sidebar.js';
