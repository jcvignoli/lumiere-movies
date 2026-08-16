<?php
/**
 * Interface for Modules
 *
 * @copyright (c) 2026, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Defines methods utilised in Modules
 * @since 4.8.2
 * @template ModName of string
 * @template PopArray of array
 * @template ImdbClass of object
 */
interface Interface_Module {
	/**
	 * Display the main module version
	 *
	 * @param ImdbClass $object IMDbPHP title class
	 * @param ModName $item_name The name of the item
	 */
	public function get_module( object $object, string $item_name ): string;

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 *
	 * @param ModName $item_name
	 * @param PopArray $item_results
	 * @param int $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string;
}
