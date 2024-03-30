<?php declare( strict_types = 1 );
/**
 * Class to build Classic Links
 * Is called by the Link Factory class, implements abstract Link Maker class
 *
 * This class is used when none option is selected in admin
 *
 * Classic Popup links are created, included in taxonomy
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.1
 * @since 3.7
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

class Classic_Links extends Abstract_Link_Maker {

	/**
	 * Constructor
	 *
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
	 *
	 */
	public function lumiere_classic_register_assets(): void {

		wp_register_script(
			'lumiere_classic_links',
			$this->config_class->lumiere_js_dir . 'lumiere_classic_links.min.js',
			[],
			$this->config_class->lumiere_version,
			true
		);

	}
	/**
	 * Add javascript values to the frontpage
	 *
	 */
	public function lumiere_classic_execute_assets (): void {

		wp_enqueue_script( 'lumiere_classic_links' );

	}

	/**
	 * Build link to popup for IMDb people
	 *
	 * @param array<int, array<string, string>> $imdb_data_people Array with IMDB people data
	 * @param int $number The number of the loop $i
	 *
	 * @return string
	 */
	public function lumiere_link_popup_people ( array $imdb_data_people, int $number ): string {

		// Function in abstract class, before last param defines the output, last param specific <A> class.
		return $this->lumiere_link_popup_people_abstract( $imdb_data_people[ $number ]['imdb'], $imdb_data_people[ $number ]['name'], 0, 'lum_link_make_popup lum_link_with_people' );

	}

	/**
	 * Build picture of the movie
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $movie_title Title of the movie
	 * @return string
	 */
	public function lumiere_link_picture ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {

		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return $this->lumiere_link_picture_abstract( $photo_localurl_false, $photo_localurl_true, $movie_title, 0, '', 'imdbelementPICimg' );
	}

	/**
	 * Build picture of the movie in taxonomy pages
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $person_name Name of the person
	 * @return string
	 */
	public function lumiere_link_picture_taxonomy ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $person_name ): string {

		// Function in abstract class, last param defines the output.
		return $this->lumiere_link_picture_taxonomy_abstract( $photo_localurl_false, $photo_localurl_true, $person_name, 0 );
	}

	/**
	 * @inheritdoc
	 * Display mini biographical text, not all people have one
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 * @param int $limit_text_bio Optional, increasing the hardcoded limit of characters before displaying "click for more"
	 *
	 * @return ?string
	 */
	public function lumiere_medaillon_bio ( array $bio_array, int $limit_text_bio = 0 ): ?string {

		// Function in abstract class.
		return $this->lumiere_medaillon_bio_abstract( $bio_array, 0, $limit_text_bio );

	}

	/**
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 */
	public function lumiere_imdburl_to_internalurl ( string $text ): string {

		// Function in abstract class.
		return $this->lumiere_imdburl_to_internalurl_abstract( $text );

	}

	/**
	 * Convert an IMDb url into a Popup link for People and Movies
	 * Meant to be used inside in posts or widgets (not in Popups)
	 * Build links using classic popup
	 *
	 * @param string $text Text that includes IMDb URL to convert into a popup link
	 */
	public function lumiere_imdburl_to_popupurl ( string $text ): string {

		// Function in abstract class, last param for regular popups.
		return $this->lumiere_imdburl_to_popupurl_abstract( $text, 0 );

	}

	/**
	 * Classical popup function
	 * Build an HTML link to open a popup for searching a movie (using js/lumiere_classic_links.js)
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param null|string $popuplarg -> window width, if nothing passed takes database value
	 * @param null|string $popuplong -> window height, if nothing passed takes database value
	 */
	public function lumiere_popup_film_link ( array $link_parsed, ?string $popuplarg = null, ?string $popuplong = null ): string {

		// Function in abstract class, fourth param for bootstrap.
		return $this->lumiere_popup_film_link_abstract( $link_parsed, $popuplarg, $popuplong );

	}

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the offical website
	 * @param string $name offical website name
	 */
	public function lumiere_movies_officialsites_details ( string $url, string $name ): string {

		// Function in abstract class, third param for links.
		return $this->lumiere_movies_officialsites_details_abstract( $url, $name, 0 );

	}

	/**
	 * Plots data details
	 *
	 * @param string $plot Text of the plot
	 */
	public function lumiere_movies_plot_details ( string $plot ): string {

		// Function in abstract class.
		return $this->lumiere_movies_plot_details_abstract( $plot );
	}

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $url Url to the prod company
	 * @param string $notes prod company notes
	 */
	public function lumiere_movies_prodcompany_details ( string $name, string $url, string $notes ): string {

		// Function in abstract class, fifth param for links.
		return $this->lumiere_movies_prodcompany_details_abstract( $name, $url, $notes, 0 );

	}

	/**
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function lumiere_movies_source_details ( string $mid ): string {

		// Function in abstract class, second for normal display, third param to include imdbelementSOURCE-picture.
		return $this->lumiere_movies_source_details_abstract( $mid, 0, 'imdbelementSOURCE-picture' );

	}

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 */
	public function lumiere_movies_trailer_details ( string $url, string $website_title ): string {

		// Function in abstract class, third param for links.
		return $this->lumiere_movies_trailer_details_abstract( $url, $website_title, 0 );

	}

	/**
	 * Image for the ratings
	 *
	 * @param int $rating mandatory Rating number
	 * @param int $votes mandatory Number of votes
	 * @param string $votes_average_txt mandatory Text mentionning "vote average"
	 * @param string $out_of_ten_txt mandatory Text mentionning "out of ten"
	 * @param string $votes_txt mandatory Text mentionning "votes"
	 *
	 * @return string
	 */
	public function lumiere_movies_rating_picture ( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {

		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return $this->lumiere_movies_rating_picture_abstract( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );

	}
}
