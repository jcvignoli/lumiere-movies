<?php
/**
 * Class for displaying persons module Title.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module\Person;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display title for Persons
 *
 * @since 4.6 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'title', array<array-key, string>, \Lumiere\Vendor\Imdb\Name>
 */
final class Person_Title extends Parent_Module {

	/**
	 * Display the title and possibly the year
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$perso_name = $imdb_class->name() ?? '';
		$born = $imdb_class->born();
		/** Translators: 'born in' is followed by a year */
		$year_born_txt = isset( $born ) && isset( $born['year'] ) ? ' (' . esc_html__( 'born in', 'lumiere-movies' ) . '&nbsp;' . strval( $born['year'] ) . ')' : '';

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup(
				'title',
				[
					'persoName' => $perso_name,
					'year' => $year_born_txt,
				],
				0
			);
		}

		return $this->output_class->misc_layout(
			'frontend_title',
			$perso_name . $year_born_txt
		);
	}

	/**
	 * Display the Popup version of the module
	 * This one is never used, kept for compatibility
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {
		return $this->output_class->misc_layout(
			'popup_title_perso',
			esc_html( $item_results['persoName'] )
		);
	}
}
