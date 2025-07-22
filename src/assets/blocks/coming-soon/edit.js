import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import './index.css';
import jsonData from './block.json';
import imdbCountries from '../../js/imdb-list-countries';

/**
 * Trying to convert { "Albania":"al" } to { label: "Albania", value:"al" } in source, so source is cleaner, but can't make it
let countryArr = new Array();
const buildOptions = ( imdbCountries ) => (
	// Build an <option> HTML tag based on a two columns array with label and value (meant to be used with a javascript array).
	imdbCountries.forEach(function(element, index) {
		Object.keys(element).forEach(function(key) {
			countryArr.push( { label: key, value: element[key] } );
		});
	})
);
console.log(countryArr);
*/

export default function Edit ( { attributes, setAttributes } ) {

	return (
		<div { ...useBlockProps() }>
			<div className="lum_block_calendar_container">
				<img className="lum_block_calendar_logo" src={ lumiere_admin_vars.ico80 } alt="" />
				<div className="lum_block_calendar_container_title">
					{ __( 'Lumière! Calendar upcoming movies', 'lumiere-movies') }
				</div>
				<div className="lum_block_calendar_container_explain">
					{ __( 'Click here to change the block options', 'lumiere-movies') }
				</div>

				<InspectorControls>
					<PanelBody title={__('Settings', 'lumiere-movies')}>
						<SelectControl
							label={ __( 'Country', 'lumiere-movies' ) }
							value={attributes.region}
							onChange={region => setAttributes({ region })}
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							options={ imdbCountries }
						/>
						<SelectControl
							label={ __( 'Type of search', 'lumiere-movies' ) }
							value={attributes.type}
							onChange={type => setAttributes({ type })}
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							options={ [
								{ label: 'Movies', value: 'MOVIE' },
								{ label: 'TV', value: 'TV' },
								{ label: 'TV episodes', value: 'TV_EPISODE' },
							] }
						/>
						<TextControl
							label={__('Starting date (in number of days)', 'lumiere-movies')}
							type="number"
							value={attributes.startDateOverride}
							onChange={val => setAttributes({ startDateOverride: parseInt(val) || 0 })}
							__nextHasNoMarginBottom={ true }
							__next40pxDefaultSize={ true }
						/>
						<TextControl
							label={__('Ending date (in number of days)', 'lumiere-movies')}
							type="number"
							value={attributes.endDateOverride}
							onChange={val => setAttributes({ endDateOverride: parseInt(val) || 0 })}
							__nextHasNoMarginBottom={ true }
							__next40pxDefaultSize={ true }
						/>
					</PanelBody>
				</InspectorControls>
			</div>
			<ServerSideRender
				block={ jsonData.name }
				attributes={ attributes }				
			/>
	    	</div>
	);
}
