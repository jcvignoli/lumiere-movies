<?php declare( strict_types = 1 );
/**
 * Class to build no HTML Links
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Is called by the Link Factory class, implements abstract Link Maker class
 * This class is used when "kill all links" option is selected in Lumière advanced admin
 * No external HTML link, no popup will be made
 * Links to 1/ taxonomy pages and 2/ internal links => are kept 3/ all popups are either turned into internal links or removed
 *
 * @since 3.8
 */
class No_Links extends Implement_Link_Maker implements Interface_Link_Maker {

	/**
	 * @inheritdoc
	 */
	public function get_popup_people( string $imdb_id, string $name ): string {
		// Function in abstract class, before last param defines the output.
		return parent::get_popup_people_details( $imdb_id, $name, 2, '' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::get_picture_details( $photo_localurl_false, $photo_localurl_true, $movie_title, 3, '', 'imdbelementPICimg' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {
		// Function in abstract class, last param cut the links.
		return parent::get_medaillon_bio_details( $bio_array, 1, $limit_text_bio );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_imdburl_to_internalurl( string $text ): string {

		// Function in abstract class, last param cut the links.
		return parent::lumiere_imdburl_to_internalurl_details( $text, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_imdburl_of_taxonomy( string $text ): string {
		// Function in abstract class, last param for removing all links.
		return parent::lumiere_imdburl_of_taxonomy_details( $text, 2 );
	}

	/**
	 * @inheritdoc
	 */
	public function replace_span_to_popup( string $title_or_name, ?string $popuplarg = null, ?string $popuplong = null ): string {
		// Function in abstract class, fourth param for No links.
		return parent::replace_span_to_popup_details( $title_or_name, $popuplarg, $popuplong, 2 );
	}

	/**
	 * @inherit
	 */
	public function get_popup_film( string $title, string $imdbid, ?string $popuplarg = null, ?string $popuplong = null ): string {
		// Function in abstract class, fifth param for No links.
		return parent::get_popup_film_details( $title, $imdbid, $popuplarg, $popuplong, 2 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_trailer( string $url, string $website_title ): string {
		// Function in abstract class, third param for removing links.
		return parent::get_trailer_details( $url, $website_title, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_prodcompany( string $name, string $comp_id = '', string $notes = '' ): string {
		// Function in abstract class, fifth param for links.
		return parent::get_prodcompany_details( $name, '', '', 1 );

	}

	/**
	 * @inheritdoc
	 */
	public function get_officialsites( string $url, string $name ): string {
		// Function in abstract class, third param for no links.
		return parent::get_officialsites_details( $url, $name, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_plot( string $plot ): string {
		// Function in abstract class
		return parent::get_plot_details( $plot );
	}

	/**
	 * @inheritdoc
	 */
	public function get_source( string $mid ): string {
		// Function in abstract class, second param to remove links.
		return parent::get_source_details( $mid, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_rating_picture ( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::get_rating_picture_details( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );
	}
}
