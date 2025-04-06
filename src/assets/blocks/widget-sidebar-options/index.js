//import { registerBlockType } from '@wordpress/blocks';
//import { useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import jsonData from './block.json';
import './index.css';


/**
 * This is a fake file, meant to include only the options.js
 *
registerBlockType( jsonData.name, {
	edit: ( props ) => {
		 return (
			<p { ...useBlockProps() }>
				{ __( 'Example Post Meta – hello from the editor!', 'lumiere-movies' ) }
			</p>
		);
	},
	save: ( props ) => {
		return (
			<p { ...useBlockProps.save() }>
				{ 'Example Post Meta – hello from the saved content!' }
			</p>
		);
	}
} );
 */

/**
 * The sidebar plugin
 */
import './sidebar.js';
