import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import './index.css';
import jsonData from './block.json';

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
						<TextControl
							label={__('Country (two-letters)', 'lumiere-movies')}
							value={attributes.region}
							onChange={region => setAttributes({ region })}
							__nextHasNoMarginBottom={ true }
							__next40pxDefaultSize={ true }
						/>
						<TextControl
							label={__('Type (MOVIE, TV, TV_EPISODE)', 'lumiere-movies')}
							value={attributes.type}
							onChange={type => setAttributes({ type })}
							__nextHasNoMarginBottom={ true }
							__next40pxDefaultSize={ true }
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
