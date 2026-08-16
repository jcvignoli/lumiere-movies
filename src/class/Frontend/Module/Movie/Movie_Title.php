<?php
/**
 * Class for displaying movies module Title.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Settings_Service;
use Lumiere\Frontend\Link_Maker\Interface_Linkmaker;
use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display title for movies
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'title', array{fullTitle: string, taglineList: array<string>}, \Lumiere\Vendor\Imdb\Title>
 */
final class Movie_Title extends Parent_Module {

	/**
	 * Constructor
	 */
	public function __construct(
		protected Settings_Service $settings,
		protected Interface_Linkmaker $link_maker
	) {
		parent::__construct( settings: $this->settings, link_maker: $this->link_maker );
	}

	/**
	 * Display the title and possibly the year
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$year = (string) $imdb_class->year();
		$title = $imdb_class->$item_name();
		$year_text = '';

		if (
			$this->settings->get_movie_option( 'imdbwidgetyear' ) !== null
			&& $this->settings->get_movie_option( 'imdbwidgetyear' ) === '1'
		) {
			$year_text = ' (' . $year . ')';
		}

		$array = [
			'fullTitle' => $title . $year_text,
			'taglineList' => $imdb_class->tagline(),
		];

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( 'title', $array, 0, );
		}

		return $this->output_class->misc_layout(
			'frontend_title',
			$array['fullTitle']
		);
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$tagline_final = null;
		if ( array_key_exists( 0, $item_results['taglineList'] ) ) {
			$tagline_final = esc_html( $item_results['taglineList'][0] );
		}

		return $this->output_class->misc_layout(
			'popup_title_film',
			$item_results['fullTitle'],
			$tagline_final ?? ''
		);
	}

	/**
	 * Wrapping method for Popup_Film
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'title' $item_name The name of the item
	 * @since 4.7.1
	 */
	public function get_module_popup_two_columns( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {
		return $this->get_module( $movie, $item_name );
	}
}
