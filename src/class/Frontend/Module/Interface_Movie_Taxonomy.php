<?php
/**
 * Interface for Modules Movie Taxonomy
 *
 * @copyright (c) 2026, Lost Highway
 *
 * @version       1.1
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Defines methods utilised in Modules Movie Taxonomy
 * @since 4.8.2
 */
interface Interface_Movie_Taxonomy {
	/**
	 * Display taxonomy
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	public function get_module_taxo( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string;
}
