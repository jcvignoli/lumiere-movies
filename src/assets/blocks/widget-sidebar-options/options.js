import { __ , sprintf  } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch,useSelect, useDispatch } from '@wordpress/data';
import { PluginDocumentSettingPanel, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { ToggleControl, TextControl, PanelRow, SelectControl } from '@wordpress/components';
import { RawHTML } from '@wordpress/element';

const iconLumiere = (
  <svg width={35} height={35} viewBox="0 0 200 200">
    <path d="M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"/>
  </svg>
);
const htmlToElem = ( htmlText ) => RawHTML( { children: htmlText } ); /* type of block that can include html */

const Lum_Sidebar_Options = ( { postType, postMeta, setPostMeta } ) => {

	// Render component for post type 'post' only
	if ( 'post' !== postType ) return null;

	const { meta } = useSelect(select => ({
		meta: select('core/editor').getEditedPostAttribute('meta')
	}))
	const { editPost } = useDispatch('core/editor')
	const setMeta = keyAndValue => {
		editPost({ meta: keyAndValue })
	}
	
	const autoTitleFieldName = lumiere_admin_vars.auto_title_field_name
	// Dynamically created var, such as _lum_person_id_widget
	const nameSelectedTypeSearch =  '_' + meta['_lum_form_type_query'] + '_widget'
	const metaNameSelectedTypeSearch =  meta[nameSelectedTypeSearch] // or postMeta.nameSelectedTypeSearch

	const FuncFreeText = () => {
		return (
			<PanelRow>
				<TextControl
					label="Tite/name/IMDb ID"
					value={ meta[nameSelectedTypeSearch] }
					help={ htmlToElem( sprintf( __('You can get the IMDb ID number by %1$ssearching in the popup%2$s and then copy the ID found here.', 'lumiere-movies'), '<a data-lumiere_admin_search_popup="noInfoNeeded" class="link-imdblt-highslidepeople" target="_blank">', '</a>' ) ) }
					onChange={ ( value ) => { setMeta( { [ `${ nameSelectedTypeSearch }` ]: value  } );  }}
					__nextHasNoMarginBottom
				/>
			</PanelRow>
		);
	}
	
	const FuncAutotitleToogle = () => {

		const textAutoTitleDeactivated=htmlToElem( sprintf( __( '%1$sAuto title widget%2$s is unactive, related options are hidden', 'lumiere-movies' ), '<a id="link_to_imdbautopostwidget" href="' + lumiere_admin_vars.wordpress_path + '/wp-admin/admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget">', '</a>' ) ); 
		if ( lumiere_admin_vars.auto_title_activated === '0' ) return ( <PanelRow><div className="lum_widget_options_subtitle">{textAutoTitleDeactivated}</div></PanelRow> );

		return (
				<>
			<PanelRow>
				<div className="lum_widget_options_subtitle">Auto title widget options</div>
			</PanelRow>
			<PanelRow>
				<ToggleControl
					label={ __('Deactivate Auto Title Widget for this post', 'lumiere-movies') }
					help={ htmlToElem( sprintf( __( 'Will prevent %1$sAuto Title Widget%2$s to be displayed on this post', 'lumiere-movies' ), '<a id="link_to_imdbautopostwidget" href="' + lumiere_admin_vars.wordpress_path + '/wp-admin/admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget">', '</a>' ) ) }
					value={ postMeta.autoTitleFieldName } // e.g: value = 'a'
					onChange={ ( boolSelected ) => setMeta( { [ `${ autoTitleFieldName }` ]: boolSelected } ) }
					checked={ postMeta._lumiere_autotitlewidget_perpost }
					__nextHasNoMarginBottom
				/>
			</PanelRow>
				</>
		)
	}
	
	return(
		<PluginDocumentSettingPanel
			title={ __( 'Lumière widget settings', 'lumiere-movies' ) }
			className="lum_widget_options_title"
			initialOpen="true"
			icon={ iconLumiere }
			initialOpen={true}
		>
			<PanelRow>
				<div className="lum_widget_options_subtitle">{ __( 'Theses features are functional only if you added a Lumiere widget', 'lumiere-movies' ) }</div>
			</PanelRow>
			<PanelRow>
				<SelectControl
					label={ __( 'Display items in widget', 'lumiere-movies' ) }
					className="lum_form_type_query"
					value={ postMeta._lum_form_type_query } // e.g: value = 'a'
					onChange={( value ) => { 
						setMeta( { [ `${ nameSelectedTypeSearch }` ]: '' } );
						setMeta( { _lum_form_type_query: value } );
					}}
					__nextHasNoMarginBottom
					variant="default"
					options={ lumiere_admin_vars.select_type_search }
				/ >
			</PanelRow>
			
			<FuncFreeText />
			
			<FuncAutotitleToogle />
			
		</PluginDocumentSettingPanel>
	);
}
 
export default compose( [
	withSelect( ( select ) => {		
		return {
			postMeta: select( 'core/editor' ).getEditedPostAttribute( 'meta' ),
			postType: select( 'core/editor' ).getCurrentPostType(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setPostMeta( newMeta ) {
				dispatch( 'core/editor' ).editPost( { meta: newMeta } );
			}
		};
	} )
] )( Lum_Sidebar_Options );	
