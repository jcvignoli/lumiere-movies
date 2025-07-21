import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import jsonData from './block.json';
import Edit from './edit.js';

const iconLumiere = (
  <svg width={35} height={35} viewBox="0 0 200 200">
    <path d="M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10z M170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80z M50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30z M57 44 c-17 -17 -4 -24 44 -24 36 0 49 4 47 13 -5 13 -79 22 -91 11z" />
  </svg>
);

registerBlockType( jsonData.name, {
	title: __('Coming soon', 'lumiere-movies'),
	description: __('Add upcoming list of movies in your post', 'lumiere-movies'),
	icon: iconLumiere,
	category: jsonData.category,
	keywords: jsonData.keywords,
	example: jsonData.example,
	attributes: jsonData.attributes,
	edit: Edit,
	save: () => null // Dynamic block, content rendered by PHP.
});
