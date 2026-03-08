import './index.css';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { RawHTML } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import jsonData from './block.json';

export default function Edit ( { attributes, setAttributes } ) {

    	const blockProps = useBlockProps();
	const htmlToElem = ( html ) => RawHTML( { children: html } ); // this type of block can include html.

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={ __('Settings', 'lumiere-movies') }>
					<SelectControl
						label={ __( 'Search Type', 'lumiere-movies' ) }
						value={ attributes.lumiere_imdblt_select }
						options={ lumiere_admin_vars.select_type_search }
						onChange={ ( lumiere_imdblt_select ) => setAttributes( { lumiere_imdblt_select } ) }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<TextControl
						label={ __( 'Title / Name / IMDb ID', 'lumiere-movies' ) }
						value={ attributes.content }
						onChange={ ( content ) => setAttributes( { content } ) }
						/* translators: %1$s and %2$s are html tags */
						help={ htmlToElem( sprintf( __('You can get the IMDb ID number by %1$ssearching in the popup%2$s and then copy the ID found here.', 'lumiere-movies'), '<a data-lumiere_admin_search_popup="noInfoNeeded" class="link-imdblt-highslidepeople" target="_blank">', '</a>' ) ) }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</PanelBody>
			</InspectorControls>

			<div className="lumiere_block_intothepost">
				<ServerSideRender
					block={ jsonData.name }
					attributes={ attributes }
					emptyResponsePlaceholder={ (
						<div className="lumiere_block_intothepost-placeholder">
							<img className="lumiere_block_intothepost-image" src={ lumiere_admin_vars.ico80 } alt="" />
							<div className="lumiere_block_intothepost-title">Lumière! movies</div>
							<div className="lumiere_block_intothepost-explanation">
								{ __('Enter a movie title or IMDb ID in the sidebar to see the preview.', 'lumiere-movies') }
							</div>
						</div>
					) }
				/>
			</div>
		</div>
	);
}

