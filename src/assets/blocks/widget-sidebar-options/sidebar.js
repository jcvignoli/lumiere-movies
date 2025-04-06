import { __ , sprintf  } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch,useSelect, useDispatch } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { ToggleControl, TextControl, PanelRow, SelectControl } from '@wordpress/components';
import { RawHTML } from '@wordpress/element';
import { registerPlugin } from '@wordpress/plugins'; 

const iconLumiere = (
  <svg width={35} height={35} viewBox="0 0 200 200">
    <path d="M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"/>
  </svg>
);
const htmlToElem = ( htmlText ) => RawHTML( { children: htmlText } ); /* type of block that can include html */

const Lum_Sidebar_Options = (props) => {

	const postType = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostType() );

	// Render component for post type 'post' only
	if ( 'post' !== postType ) return null;

	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	/**
	 * Return a value pass lum_* to _lum_*_widget
	 * @param item A bit like lum_*
	 * @return A bit like _lum_*_widget
	 */
	function makeWidgetRow( item ) {
		return '_' + item + '_widget';		
	}

	/**
	 * Set a row _lum_*_widget to blank
	 * Meant to be used in a foreach loop
	 */
	function cleanOptions( items ) {
		const column = makeWidgetRow( items['value'] );
		setMeta( { [ column ]: '' } )
	}
	
	const FuncSelector = () => {
		return (
			<SelectControl
				label={ __( 'Display items in widget', 'lumiere-movies' ) }
				className="lum_form_type_query"
				value={ meta['_lum_form_type_query'] } // e.g: value = 'a'
				onChange={ ( value ) => setMeta( { [ '_lum_form_type_query' ]: value } ) }
				__nextHasNoMarginBottom
				options={ lumiere_admin_vars.select_type_search }
			/ >
	 	)
	 };

	const FuncFreeText = () => {
		let getSavedValue = meta._lum_form_type_query || 'lum_movie_title'; // The saved selection, with a default value if not saved	
		//const getSavedValue = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ), [] ) || 'lum_movie_title'; // === meta[ savedValue ]

		let widget = makeWidgetRow( getSavedValue ); // => _lum_*_widget
		let widget_value = meta[widget];

		return (
			<TextControl
				label="Tite/name/IMDb ID"
				value={ widget_value }
				help={ htmlToElem( sprintf( __('You can get the IMDb ID number by %1$ssearching in the popup%2$s and then copy the ID found here.', 'lumiere-movies'), '<a data-lumiere_admin_search_popup="noInfoNeeded" class="link-imdblt-highslidepeople" target="_blank">', '</a>' ) ) }
				onChange=
				{ ( value ) => {
						lumiere_admin_vars.select_type_search.forEach(cleanOptions); // Clean all _lum_*_widget rows on change
						setMeta( { [ widget ]: value } ); // Set the curent value to _lum_*_widget row
					}
				} 
				__nextHasNoMarginBottom
			/>
		)
	};
	
	const autoTitleFieldName = lumiere_admin_vars.auto_title_field_name
	/* Translators: %1$s and %2$s are html tags */
	const textAutoTitleDeactivated=htmlToElem( sprintf( __( '%1$sAuto title widget%2$s is unactive, related options are hidden', 'lumiere-movies' ), '<a id="link_to_imdbautopostwidget" href="' + lumiere_admin_vars.wordpress_path + '/wp-admin/admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget">', '</a>' ) ); 
		
	const FuncAutotitleToogle = () => {
		if ( lumiere_admin_vars.auto_title_activated === '0' ) return ( <PanelRow><div className="lum_widget_options_subtitle">{textAutoTitleDeactivated}</div></PanelRow> );
		return (
		<ToggleControl
			label={ __('Deactivate Auto Title Widget for this post', 'lumiere-movies') }
			/* Translators: %1$s and %2$s are html tags */
			help={ htmlToElem( sprintf( __( 'Will prevent %1$sAuto Title Widget%2$s to be displayed on this post', 'lumiere-movies' ), '<a id="link_to_imdbautopostwidget" href="' + lumiere_admin_vars.wordpress_path + '/wp-admin/admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget">', '</a>' ) ) }
			value={ meta[ autoTitleFieldName ] } // e.g: value = 'a'
			onChange={ ( boolSelected ) => setMeta( { [ autoTitleFieldName ]: boolSelected } ) }
			checked={ meta[ autoTitleFieldName ] }
			__nextHasNoMarginBottom
		/>
	)};
	
	return(

		<PluginDocumentSettingPanel
			title={ __( 'Lumière widget settings', 'lumiere-movies' ) }
			className="lum_widget_options_title"
			icon={ iconLumiere }
			initialOpen={true}
		>
			<PanelRow>
				<div className="lum_widget_options_subtitle">{ __( 'Theses features are functional only if you added a Lumiere widget', 'lumiere-movies' ) }</div>
			</PanelRow>
			
			<PanelRow className="lum_sidebar_options_select">
				<FuncSelector />
			</PanelRow>
				<FuncFreeText />
			<>
				<PanelRow>
					<div className="lum_widget_options_subtitle">Auto title widget options</div>
				</PanelRow>
				<PanelRow>			
					<FuncAutotitleToogle />
				</PanelRow>
			</>
		</PluginDocumentSettingPanel>

	);
}
 
registerPlugin( 'widget-sidebar-options', {
	render: Lum_Sidebar_Options
} );

