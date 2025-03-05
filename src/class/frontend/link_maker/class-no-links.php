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

namespace Lumiere\Link_Maker;

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
	public function lumiere_link_popup_people( array $imdb_data_people, int $number ): string {
		// Function in abstract class, before last param defines the output.
		return parent::lumiere_link_popup_people_abstract( $imdb_data_people[ $number ]['imdb'], $imdb_data_people[ $number ]['name'], 2, '' );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_link_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::lumiere_link_picture_abstract( $photo_localurl_false, $photo_localurl_true, $movie_title, 3, '', 'imdbelementPICimg' );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {
		// Function in abstract class, last param cut the links.
		return parent::lumiere_medaillon_bio_abstract( $bio_array, 1, $limit_text_bio );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_imdburl_to_internalurl( string $text ): string {

		// Function in abstract class, last param cut the links.
		return parent::lumiere_imdburl_to_internalurl_abstract( $text, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_imdburl_of_taxonomy( string $text ): string {
		// Function in abstract class, last param for removing all links.
		return parent::lumiere_imdburl_of_taxonomy_abstract( $text, 2 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_imdburl_of_soundtrack( string $text ): string {
		// Function in abstract class, last param for removing all links.
		return parent::lumiere_imdburl_of_soundtrack_abstract( $text, 2 );
	}

	/**
	 * @inheritdoc
	 */
	public function popup_film_link( array $link_parsed, ?string $popuplarg = null, ?string $popuplong = null ): string {
		// Function in abstract class, fourth param for No links.
		return parent::popup_film_link_abstract( $link_parsed, $popuplarg, $popuplong, 2 );
	}

	/**
	 * @inherit
	 */
	public function popup_film_link_inbox( string $title, string $imdbid, ?string $popuplarg = null, ?string $popuplong = null ): string {
		// Function in abstract class, fifth param for No links.
		return parent::popup_film_link_inbox_abstract( $title, $imdbid, $popuplarg, $popuplong, 2 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_trailer_details( string $url, string $website_title ): string {
		// Function in abstract class, third param for removing links.
		return parent::lumiere_movies_trailer_details_abstract( $url, $website_title, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_prodcompany_details( string $name, string $comp_id = '', string $notes = '' ): string {
		// Function in abstract class, fifth param for links.
		return parent::lumiere_movies_prodcompany_details_abstract( $name, '', '', 1 );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_officialsites_details( string $url, string $name ): string {
		// Function in abstract class, third param for no links.
		return parent::lumiere_movies_officialsites_details_abstract( $url, $name, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_plot_details( string $plot ): string {
		// Function in abstract class
		return parent::lumiere_movies_plot_details_abstract( $plot );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_source_details( string $mid ): string {
		// Function in abstract class, second param to remove links.
		return parent::lumiere_movies_source_details_abstract( $mid, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_rating_picture ( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::lumiere_movies_rating_picture_abstract( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );
	}
}
