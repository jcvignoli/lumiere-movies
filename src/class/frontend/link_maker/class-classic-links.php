<?php declare( strict_types = 1 );
/**
 * Class to build Classic Links
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.1
 * @package lumiere-movies
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
class Classic_Links extends Implement_Link_Maker implements Interface_Link_Maker {

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
	 * @inherit
	 */
	public function get_popup_people( string $imdb_id, string $name ): string {
		// Function in abstract class, before last param defines the output, last param specific <A> class.
		return parent::get_popup_people_details( $imdb_id, $name, 0, 'lum_link_make_popup lum_link_with_people' );
	}

	/**
	 * @inherit
	 */
	public function get_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::get_picture_details( $photo_localurl_false, $photo_localurl_true, $movie_title, 0, '', 'imdbelementPICimg' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {
		// Function in abstract class.
		return parent::get_medaillon_bio_details( $bio_array, 0, $limit_text_bio );
	}

	/**
	 * @inherit
	 */
	public function lumiere_imdburl_to_internalurl( string $text ): string {
		// Function in abstract class.
		return parent::lumiere_imdburl_to_internalurl_details( $text );
	}

	/**
	 * @inheritdoc
	 */
	public function replace_span_to_popup( string $title_or_name, ?string $popuplarg = null, ?string $popuplong = null ): string {
		// Function in abstract class, fourth param for bootstrap.
		return parent::replace_span_to_popup_details( $title_or_name, $popuplarg, $popuplong );
	}

	/**
	 * @inherit
	 */
	public function get_popup_film( string $title, string $imdbid, ?string $popuplarg = null, ?string $popuplong = null ): string {
		// Function in abstract class, fifth param for bootstrap.
		return parent::get_popup_film_details( $title, $imdbid, $popuplarg, $popuplong );
	}

	/**
	 * @inheritdoc
	 */
	public function get_officialsites( string $url, string $name ): string {
		// Function in abstract class, third param for links.
		return parent::get_officialsites_details( $url, $name, 0 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_plot( string $plot ): string {
		// Function in abstract class.
		return parent::get_plot_details( $plot );
	}

	/**
	 * @inheritdoc
	 */
	public function get_prodcompany( string $name, string $comp_id, string $notes ): string {
		// Function in abstract class, fifth param for links.
		return parent::get_prodcompany_details( $name, $comp_id, $notes, 0 );
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function get_source( string $mid ): string {
		// Function in abstract class, second for normal display, third param to include imdbelementSOURCE-picture.
		return parent::get_source_details( $mid, 0, 'imdbelementSOURCE-picture' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_trailer( string $url, string $website_title ): string {
		// Function in abstract class, third param for links.
		return parent::get_trailer_details( $url, $website_title, 0 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::get_rating_picture_details( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );
	}
}
