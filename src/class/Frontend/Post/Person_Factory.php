<?php declare( strict_types = 1 );
/**
 * Class for displaying movies data.
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Post;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Vendor\Imdb\Name;
use Lumiere\Config\Get_Options_Person;
use Lumiere\Frontend\Post\Front_Parser;

/**
 * Those methods are utilised by class Movie to display the sections
 * The class uses \Lumiere\Link_Maker\Link_Factory to automatically select the appropriate Link maker class to display data ( i.e. Classic links, Highslide/Bootstrap, No Links, AMP)
 * It uses ImdbPHP Classes to display movies/people data
 * It uses Layout defined in Output
 * It extends Front_Parser
 *
 * @since 4.6 new class
 */
class Person_Factory extends Front_Parser {

	/**
	 * Build the methods to be called in class Movie_Factory
	 * Use imdbphp class to get the Name class
	 *
	 * @param string $mid_premier_resultat IMDb ID, not as int since it loses its heading 0s
	 */
	public function factory_person_items_methods( string $mid_premier_resultat ): string {

		$outputfinal = '';

		// Find the Name based on $mid_premier_resultat.
		$name_object = $this->plugins_classes_active['imdbphp']->get_name_class(
			esc_html( $mid_premier_resultat ),
			$this->logger->log,
		);

		foreach ( $this->imdb_data_person_values['order'] as $data_detail => $order ) {

			$key_data_values = $data_detail . '_active';

			if (
				// Use order to select the position of the data detail.
				isset( $this->imdb_data_person_values['order'][ $data_detail ] )
				&& $this->imdb_data_person_values['order'][ $data_detail ] === $order
				// Is the data detail activated?
				&& isset( $this->imdb_data_person_values['activated'][ $key_data_values ] )
				&& $this->imdb_data_person_values['activated'][ $key_data_values ] === '1'
			) {
				// Get module.
				$text = $this->get_module_person( $name_object, $data_detail );
				if ( strlen( $text ) === 0 ) {
					continue;
				}
				// If the module exists and returned text, display and wrap it.
				$outputfinal .= $this->output_class->front_item_wrapper(
					$text,
					$data_detail,
					$this->imdb_admin_values
				);
			}
		}
		return $outputfinal;
	}

	/**
	 * Get movies modules in module folder
	 *
	 * @param Name $name_object IMDbPHP name class
	 * @param string $item_name The name of the item
	 */
	private function get_module_person( Name $name_object, string $item_name ): string {

		$class_name = Get_Options_Person::LUM_PERSON_MODULE_CLASS . ucfirst( strtolower( $item_name ) ); // strtolower to avoid camelCase names.

		// Exit if class doesn't exist.
		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		// Not yet in use
		/*if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			return $module->get_module_taxo( $movie_object, $item_name );
		}*/

		/** @phpstan-ignore method.notFound */
		return $module->get_module( $name_object, $item_name );
	}

}
