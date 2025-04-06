import './index.css';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { RawHTML, createElement } from '@wordpress/element';

export default function Edit ( props ) {

    	const blockProps = useBlockProps();
	const htmlToElem = ( html ) => RawHTML( { children: html } ); // this type of block can include html.
	const buildOptions = ( array ) => (
		// Build an <option> HTML tag based on a two columns array with label and value (meant to be used with a javascript array).
		array.map(row => ( <option value={ row.value }>{ row.label }</option> ) )
	);

	return (
		<div {...blockProps}>
			<div className="lumiere_block_intothepost">
				<img className="lumiere_block_intothepost-image" src={ lumiere_admin_vars.ico80 } alt="" />
				<div className="lumiere_block_intothepost-title">
					Lumière! movies
				</div>
				<div className="lumiere_block_intothepost-explanation">
					{ __('This block is visible only in your admin area. In your blog frontpage, it will be replaced by the movie you selected here.', 'lumiere-movies') }
					<br />
					{ __('"By Movie title/Person name": Enter the title/name.', 'lumiere-movies') }
					<br />
					{
						/* translators: %1$s and %2$s are html tags */
						htmlToElem( sprintf( __('You can get the IMDb ID number by %1$ssearching in the popup%2$s and then copy the ID found here.', 'lumiere-movies'), '<a data-lumiere_admin_search_popup="noInfoNeeded" class="link-imdblt-highslidepeople" target="_blank">', '</a>' ) )
					}
				</div>
				<div className="lumiere_block_intothepost-container">
					<div className="lumiere_block_intothepost-select">
						<select
							value={props.attributes.lumiere_imdblt_select}
							onChange={(event) => {
							  props.setAttributes( { content: '' });
							  props.setAttributes( { lumiere_imdblt_select: event.target.value } );
							}}
							name="movie_type_selection"
						>
						{ buildOptions( lumiere_admin_vars.select_type_search ) }
						</select>
					</div>
					<RichText
						tagName="div"
						className="lumiere_block_intothepost-content"
						value={ props.attributes.content }
						onChange={ (content) => props.setAttributes( { content } ) }
					/>
				</div>
			</div>
		</div>
	);
}
