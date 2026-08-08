import './index.css';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { RawHTML } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import jsonData from './block.json';

export default function Edit( { attributes, setAttributes } ) {
	const {
		lumiere_imdblt_select = 'lum_movie_title',
		content = '',
		values = {},
	} = attributes;

	const blockProps = useBlockProps();
	const htmlToElem = ( html ) => RawHTML( { children: html } );
	const isInitialState = ! content || content === '';

	const handleSelectChange = ( newSelectValue ) => {
		// Preserve current content under active key before switching
		const updatedValues = {
			...values,
			[ lumiere_imdblt_select ]:
				values[ lumiere_imdblt_select ] ?? content,
		};

		// Retrieve saved content for the new selection key, or fallback to empty string
		const nextContent = updatedValues[ newSelectValue ] ?? '';

		setAttributes( {
			lumiere_imdblt_select: newSelectValue,
			content: nextContent,
			values: updatedValues,
		} );
	};

	const handleContentChange = ( newContent ) => {
		setAttributes( {
			content: newContent,
			values: {
				...values,
				[ lumiere_imdblt_select ]: newContent,
			},
		} );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Movie/Person post', 'lumiere-movies' ) }
				>
					<SelectControl
						label={ __( 'Search Type', 'lumiere-movies' ) }
						value={ lumiere_imdblt_select }
						options={ lumiere_admin_vars.select_type_search }
						onChange={ handleSelectChange }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<TextControl
						label={ __(
							'Title / Name / IMDb ID',
							'lumiere-movies'
						) }
						value={ content }
						onChange={ handleContentChange }
						help={ htmlToElem(
							sprintf(
								__(
									'You can get the IMDb ID number by %1$ssearching in the popup%2$s and then copy the ID found here.',
									'lumiere-movies'
								),
								'<a data-lumiere_admin_search_popup="noInfoNeeded" class="link-imdblt-highslidepeople" target="_blank">',
								'</a>'
							)
						) }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</PanelBody>
			</InspectorControls>

			<div className="lumiere_block_intothepost">
				{ isInitialState ? (
					<div className="lumiere_block_intothepost-placeholder">
						<img
							className="lumiere_block_intothepost-image"
							src={ lumiere_admin_vars.ico80 }
							alt=""
						/>
						<div className="lumiere_block_intothepost-title">
							Lumière! movies block
						</div>
						<div className="lumiere_block_intothepost-explanation">
							{ __(
								'Enter a Title/Name/IMDb ID in the sidebar',
								'lumiere-movies'
							) }
						</div>
					</div>
				) : (
					<ServerSideRender
						block={ jsonData.name }
						attributes={ attributes }
					/>
				) }
			</div>
		</div>
	);
}
