<?php declare( strict_types = 1 );
/**
 * Class for displaying persons module Title.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Module\Person;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Method to display title for Persons
 *
 * @since 4.6 new class
 */
class Person_Title extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the title and possibly the year
	 *
	 * @param \Lumiere\Vendor\Imdb\Name $name IMDbPHP title class
	 * @param 'title' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Name $name, string $item_name ): string {

		$perso_name = $name->name() ?? '';
		$born = $name->born();
		/** Translators: 'born in' is followed by a year */
		$year_born_txt = isset( $born ) && isset( $born['year'] ) ? ' (' . esc_html__( 'born in', 'lumiere-movies' ) . '&nbsp;' . strval( $born['year'] ) . ')' : '';

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $perso_name, $year_born_txt );
		}

		return $this->output_class->misc_layout(
			'frontend_title',
			$perso_name . $year_born_txt
		);
	}

	/**
	 * Display the Popup version of the module
	 * This one is never used, kept for compatibility
	 *
	 * @param string $perso_name The name of the Person
	 * @param string $year_born_txt The year the Person was born
	 */
	public function get_module_popup( string $perso_name, string $year_born_txt ): string {

		return $this->output_class->misc_layout(
			'popup_title',
			$perso_name . $year_born_txt
		);
	}
}
