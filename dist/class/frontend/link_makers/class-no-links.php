<?php declare( strict_types = 1 );
/**
 * Class to build no HTML Links
 * Is called by the Link Factory class, implements abstract Link Maker class
 *
 * This class is used when kill all links is ticked
 * No external HTML link, no popup will be made
 *
 * Links to 1/ taxonomy pages and 2/ internal links are kept
 * and 3/ all popups are either turned into internal links or removed
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7.1
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;

class No_Links extends Abstract_Link_Maker {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

	}

	/**
	 * Build link (a popup in other link classes) internal link for IMDb people
	 *
	 * @param array<int, array<string, string>> $imdb_data_people Array with IMDB people data
	 * @param int $number The number of the loop $i
	 *
	 * @return string
	 */
	public function lumiere_link_popup_people ( array $imdb_data_people, int $number ): string {

		return esc_attr( $imdb_data_people[ $number ]['name'] );

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
		return $this->lumiere_link_picture_abstract( $photo_localurl_false, $photo_localurl_true, $movie_title, 3, '', 'imdbelementPICimg' );

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
		return $this->lumiere_link_picture_taxonomy_abstract( $photo_localurl_false, $photo_localurl_true, $person_name, 3 );

	}

	/**
	 * Display mini biographical text, not all people have one
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 *
	 * @return ?string
	 */
	public function lumiere_medaillon_bio ( array $bio_array ): ?string {

		// Function in abstract class, last param cut the links.
		return $this->lumiere_medaillon_bio_abstract( $bio_array, 1 );

	}

	/**
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 */
	public function lumiere_imdburl_to_internalurl ( string $text ): string {

		// Function in abstract class, last param cut the links.
		return $this->lumiere_imdburl_to_internalurl_abstract( $text, 1 );

	}

	/**
	 * Remove an IMDb url from the text
	 * In this class, no popup is allowed so no popup link is built
	 *
	 * @param string $text Text that includes IMDb URL to be removed
	 */
	public function lumiere_imdburl_to_popupurl ( string $text ): string {

		// Function in abstract class, last param for removing all links.
		return $this->lumiere_imdburl_to_popupurl_abstract( $text, 2 );

	}

	/**
	 * No Link function for movies links
	 * Builds an internal when movie's are entered, because if not, the whole purpose of the plugins is killed
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param string $popuplarg Not in use
	 * @param string $popuplong Not in use
	 */
	public function lumiere_popup_film_link ( array $link_parsed, string $popuplarg = null, string $popuplong = null ): string {

		// Function in abstract class, fourth param for AMP.
		return $this->lumiere_popup_film_link_abstract( $link_parsed, $popuplarg, $popuplong, 2 );

	}

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 */
	public function lumiere_movies_trailer_details ( string $url, string $website_title ): string {

		return "\n\t\t\t" . sanitize_text_field( $website_title ) . ', ' . esc_url( $url );

	}

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $url Url to the prod company
	 * @param string $notes prod company notes
	 */
	public function lumiere_movies_prodcompany_details ( string $name, string $url = '', string $notes = '' ): string {

		return esc_attr( $name ) . '<br />';

	}

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the prod company
	 * @param string $name prod company name
	 */
	public function lumiere_movies_officialsites_details ( string $url, string $name ): string {

		return "\n\t\t\t" . sanitize_text_field( $name ) . ', ' . esc_url( $url );

	}

	/**
	 * Plots data details
	 *
	 * @param string $plot Text of the plot
	 */
	public function lumiere_movies_plot_details ( string $plot ): string {

		// Function in abstract class, last param to remove links.
		return $this->lumiere_movies_plot_details_abstract( $plot, 1 );

	}

	/**
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function lumiere_movies_source_details ( string $mid ): string {

		return "\n\t\t\t" . '<img class="imdbelementSOURCE-picture" alt="link to imdb" width="33" height="15" src="' . esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/imdb-link.png' ) . '" />'
			. ' https://www.imdb.com/title/tt' . $mid;

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
