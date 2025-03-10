<?php declare( strict_types = 1 );
/**
 * Parent class for all modules
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Module;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Frontend\Layout\Output;
use Lumiere\Frontend\Main;

/**
 * Simplify coding, using most usefull classes
 * @see \Lumiere\Frontend\Movie\Movie_Taxonomy extra class is only used in modules that need it
 *
 * @since 4.4.3 new class
 */
class Parent_Module {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
	) {
		/**
		 * Get the properties and the linkmakers.
		 */
		$this->start_main_trait(); // In Trait Main.
	}

	/**
	 * Build local link for person
	 * Add a nounce
	 * Caution: these links are not changed according to Linkmaker
	 * @TODO: all link_maker->popup_internal_link methods should come here, then Link_Maker should be rewritten to take imdb_id and name
	 * @param string $imdb_id The imdb id that get afer the mid
	 * @param string $name The person's name
	 */
	protected function get_person_url( string $imdb_id, string $name = '' ): string {
		return "\n\t\t\t\t\t\t" . $this->output_class->get_link(
			'internal_with_spinner',
			wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . '?mid=' . $imdb_id ),
			$name,
		);
	}

	/**
	 * Build local link for person
	 * Add a nounce
	 * Caution: these links are not changed according to Linkmaker
	 * @TODO: all link_maker->popup_internal_link methods should come here, then Link_Maker should be rewritten to take imdb_id and title
	 * @param string $imdb_id The imdb id that get afer the mid
	 * @param string $title The movie's title
	 */
	protected function get_film_url( string $imdb_id, string $title = '' ): string {
		return "\n\t\t\t\t\t\t" . $this->output_class->get_link(
			'internal_with_spinner',
			wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?mid=' . $imdb_id ),
			$title,
		);
	}
}
