<?php declare( strict_types = 1 );
/**
 * Class to build no HTML Links
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

/**
 * Is called by the Link Factory class, implements abstract Link Maker class
 * This class is used when "kill all links" option is selected in Lumière advanced admin
 * No external HTML link, no popup will be made
 * Links to 1/ taxonomy pages and 2/ internal links => are kept 3/ all popups are either turned into internal links or removed
 *
 * @since 3.8
 */
class No_Links extends Implement_Methods implements Interface_Linkmaker {

	/**
	 * @inheritdoc
	 */
	public function get_rating_picture ( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in parent class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::get_rating_picture_details( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in parent class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::get_picture_details( $photo_localurl_false, $photo_localurl_true, $movie_title, parent::LINK_OPTIONS['nolinks'], '', 'imdbelementPICimg' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {
		// Function in abstract class, last param cut the links.
		return parent::get_medaillon_bio_details( $bio_array, parent::LINK_OPTIONS['nolinks'], $limit_text_bio );
	}

	/**
	 * @inheritdoc
	 */
	public function get_plot( string $plot ): string {
		return parent::get_plot_details( $plot, parent::LINK_OPTIONS['nolinks'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_popup_people( string $imdb_id, string $name ): string {
		// Function in parent class, last param could be used for a specific <A> class needed for no links.
		return parent::get_popup_people_details( $imdb_id, $name, parent::LINK_OPTIONS['nolinks'], '' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_popup_film_title( string $title, string $a_class = '' ): string {
		return parent::get_popup_film_title_details( $title, parent::LINK_OPTIONS['nolinks'], $a_class );
	}

	/**
	 * @inherit
	 */
	public function get_popup_film_id( string $title, string $imdbid, string $a_class = '' ): string {
		return parent::get_popup_film_id_details( $title, $imdbid, parent::LINK_OPTIONS['nolinks'], $a_class );
	}

	/**
	 * @inherit
	 */
	public function get_external_url( string $title, string $url, string $a_class = '' ): string {
		return parent::get_external_url_details( $title, $url, parent::LINK_OPTIONS['nolinks'], $a_class );
	}

	/**
	 * @inheritdoc
	 */
	public function get_trailer( string $url, string $website_title ): string {
		return parent::get_trailer_details( $url, $website_title, parent::LINK_OPTIONS['nolinks'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_prodcompany( string $name, string $comp_id = '', string $notes = '' ): string {
		return parent::get_prodcompany_details( $name, '', '', parent::LINK_OPTIONS['nolinks'] );

	}

	/**
	 * @inheritdoc
	 */
	public function get_officialsites( string $url, string $name ): string {
		return parent::get_officialsites_details( $url, $name, parent::LINK_OPTIONS['nolinks'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_source( string $mid ): string {
		return parent::get_source_details( $mid, parent::LINK_OPTIONS['nolinks'] );
	}
}
