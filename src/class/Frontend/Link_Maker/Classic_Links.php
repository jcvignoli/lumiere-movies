<?php declare( strict_types = 1 );
/**
 * Class to build Classic Links
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */
namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;

/**
 * Is called by the Link Factory class, implements abstract Link Maker class
 *
 * This class is used by default if no option was selected in admin, or if "classic" was selected
 *
 * Classic Popup links are created, included in taxonomy
 *
 * @since 3.7
 */
final class Classic_Links extends Implement_Methods implements Interface_Linkmaker {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		// Registers javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_classic_register_assets' ] );

		// Execute javascripts and styles only if the vars in lumiere_classic_links was not already enqueued
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_classic_execute_assets' ] );
	}

	/**
	 *  Register frontpage scripts and styles
	 */
	public function lumiere_classic_register_assets(): void {
		wp_register_script(
			'lumiere_classic_links',
			Get_Options::LUM_JS_URL . 'lumiere_classic_links.min.js',
			[],
			lum_get_version(),
			true
		);
	}

	/**
	 * Add javascript values to the frontpage
	 */
	public function lumiere_classic_execute_assets(): void {
		wp_enqueue_script( 'lumiere_classic_links' );
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function get_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::get_rating_picture_details( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );
	}

	/**
	 * @inherit
	 */
	#[\Override]
	public function get_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::get_picture_details( $photo_localurl_false, $photo_localurl_true, $movie_title, parent::LINK_OPTIONS['classic'], '', 'imdbelementPICimg' );
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function get_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): string {
		return parent::get_medaillon_bio_details( $bio_array, parent::LINK_OPTIONS['classic'], $limit_text_bio );
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function get_plot( string $plot ): string {
		return parent::get_plot_details( $plot, parent::LINK_OPTIONS['nolinks'] );
	}

	/**
	 * @inherit
	 */
	#[\Override]
	public function get_popup_people( string $imdb_id, string $name ): string {
		// Function in parent class, last param for a specific <A> class needed for classic links.
		return parent::get_popup_people_details( $imdb_id, $name, parent::LINK_OPTIONS['classic'], 'lum_link_make_popup lum_link_with_people' );
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function get_popup_film_title( string $title, string $a_class = '' ): string {
		return parent::get_popup_film_title_details( $title, parent::LINK_OPTIONS['classic'], $a_class );
	}

	/**
	 * @inherit
	 */
	#[\Override]
	public function get_popup_film_id( string $title, string $imdbid, string $a_class = '' ): string {
		return parent::get_popup_film_id_details( $title, $imdbid, parent::LINK_OPTIONS['classic'], $a_class );
	}

	/**
	 * @inherit
	 */
	#[\Override]
	public function get_external_url( string $title, string $url, string $a_class = '' ): string {
		return parent::get_external_url_details( $title, $url, parent::LINK_OPTIONS['classic'], $a_class );
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function get_trailer( string $url, string $website_title ): string {
		return parent::get_trailer_details( $url, $website_title, parent::LINK_OPTIONS['classic'] );
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function get_prodcompany( string $name, string $comp_id, string $notes ): string {
		return parent::get_prodcompany_details( $name, $comp_id, $notes, parent::LINK_OPTIONS['classic'] );
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function get_officialsites( string $url, string $name ): string {
		return parent::get_officialsites_details( $url, $name, parent::LINK_OPTIONS['classic'] );
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	#[\Override]
	public function get_source( string $mid ): string {
		// Function in parent class, third param to include specific class needed for classic links..
		return parent::get_source_details( $mid, parent::LINK_OPTIONS['classic'], 'imdbelementSOURCE-picture' );
	}
}
