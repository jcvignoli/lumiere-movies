import React from 'react';
import { useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import jsonData from './block.json';
import Edit from './edit.js';

const iconLumiere = (
  <svg width={35} height={35} viewBox="0 0 200 200">
    <path d="M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"/>
  </svg>
);

registerBlockType( jsonData.name, {
	title: __('Add a movie or person into your post', 'lumiere-movies'),
	description: __('Add a block in your posts that displays movie/person data.', 'lumiere-movies'),
	icon: iconLumiere,
	category: jsonData.category,
	keywords: jsonData.keywords,
	example: jsonData.example,
	attributes: {
		lumiere_imdblt_select: {
			type: 'string',
			default: 'lum_movie_title'
		},
		content: {
			type: 'string',
			default: __('Enter the name or the IMDb ID movie', 'lumiere-movies')
		},
	},
	edit: Edit,
	save: ( props ) => {
		const blockPropsSave = useBlockProps.save();
		return (
			<div {...blockPropsSave}>
				<span data-lum_movie_maker={ props.attributes.lumiere_imdblt_select }>{ props.attributes.content }</span>
			</div>
		);
	},
});
