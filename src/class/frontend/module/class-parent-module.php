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
 * @since 4.5 new class
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
		$this->start_linkmaker();
	}

	/**
	 * Build internal link for person (no popup)
	 * Add a nounce
	 * Should be used in POPUPS
	 * Caution: these links ARE NOT changed according to Linkmaker classes
	 * @param string $imdb_id The imdb id of the person
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
	 * Build internal link for movies (no popup)
	 * Add a nounce
	 * Should be used in POPUPS
	 * Caution: these links ARE NOT changed according to Linkmaker classes
	 * @param string $imdb_id The imdb id of the movie
	 * @param string $title The movie's title
	 */
	protected function get_film_url( string $imdb_id, string $title = '' ): string {
		return "\n\t\t\t\t\t\t" . $this->output_class->get_link(
			'internal_with_spinner',
			wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?mid=' . $imdb_id ),
			$title,
		);
	}

	/**
	 * Build Popup link for person
	 * Should be used in FRONTEND, to get a popup link (if relevant according to the current Link_Maker used)
	 * Add a nounce
	 * These links ARE changed according to Linkmaker
	 * @param string $imdb_id The imdb id of the person
	 * @param string $name The person's name
	 */
	protected function get_popup_person( string $imdb_id, string $name ): string {
		return $this->link_maker->get_popup_people( $imdb_id, $name );
	}

	/**
	 * Build Popup link for films using the imdb_id
	 * Should be used in FRONTEND, to get a popup link (if relevant according to the current Link_Maker used)
	 * Add a nounce
	 * These links ARE changed according to Linkmaker
	 * @param string $imdb_id The imdb id of the movie
	 * @param string $title The movie's title
	 */
	protected function get_popup_film_byid( string $imdb_id, string $title ): string {
		return $this->link_maker->get_popup_film_id( $imdb_id, $title, '' /* specific extra class */ );
	}

	/**
	 * Build Popup link for films using the movie's title
	 * Should be used in FRONTEND, to get a popup link (if relevant according to the current Link_Maker used)
	 * Add a nounce
	 * These links ARE changed according to Linkmaker
	 * @param string $title The movie's title
	 */
	protected function get_popup_film_bytitle( string $title ): string {
		return $this->link_maker->get_popup_film_title( $title, '' /* specific extra class */ );
	}
}
