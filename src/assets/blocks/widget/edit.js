import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { RawHTML } from '@wordpress/element';

export default function Edit ( { attributes, setAttributes } ) {

	/* translators: %1$s and %2$s are html tags */
	const textExplanation = sprintf( __('This widget fills your selected area, such as a sidebar, with movie/person data according to the data inserted in your metabox in post edit or the title of your post (if %1$sLumière auto widget option%2$s is active). Once this widget is saved, either the data you filled in the Lumière metabox in your post or the post\'s title will be used to display the movie/person data accordingly.', 'lumiere-movies' ), '<a href="admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget" target="_blank">', '</a>' );

	const htmlToElem = ( html ) => RawHTML( { children: html } ); // this type of block can include html.
	const blockProps = useBlockProps({ className: 'lumiere_block_widget' });
	const { lumiere_input } = attributes;

	return (
		<div {...blockProps}>
			<img class="lumiere_block_widget_image" src={lumiere_admin_vars.ico80} alt={ __( 'Lumière Icon', 'lumiere-movies') } />
			<div class="lumiere_block_widget_title">Lumière! Widget</div>
			<div class="lumiere_block_widget_explanation">{ htmlToElem( textExplanation ) }</div>
			<div class="lumiere_block_widget_container">
				<div class="lumiere_block_widget_entertitle">{ __('Enter widget title:', 'lumiere-movies') }</div>
				<div class="lumiere_block_widget_enterinput">
					<input
						value={ lumiere_input }
						class="lumiere_block_widget_input"
						onChange={
							( value ) => setAttributes( { lumiere_input: value.target.value } )
						}
					/>
				</div>
			</div>
		</div>
	);
};

