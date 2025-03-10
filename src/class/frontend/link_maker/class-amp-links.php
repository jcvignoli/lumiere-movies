<?php declare( strict_types = 1 );
/**
 * Class to build AMP Links
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7.1
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * This class is used for AMP pages
 * Is called by the Link Factory class, implements abstract Link Maker class
 * 1/ External HTML links are kept
 * 2/ But no popup link is created, only links to the content of popups
 */
class AMP_Links extends Implement_Link_Maker implements Interface_Link_Maker {

	/**
	 * @inheritdoc
	 */
	public function lumiere_link_popup_people( array $imdb_data_people, int $number ): string {

		// Function in abstract class, before last param defines the output, last param specific <A> class.
		return $this->lumiere_link_popup_people_abstract( $imdb_data_people[ $number ]['imdb'], $imdb_data_people[ $number ]['name'], 3, 'lum_link_no_popup' );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_link_picture ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {

		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::lumiere_link_picture_abstract( $photo_localurl_false, $photo_localurl_true, $movie_title, 1, '', 'imdbelementPICimg' );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {

		// Function in abstract class, last param cut the links.
		return parent::lumiere_medaillon_bio_abstract( $bio_array, 1, $limit_text_bio );
	}

	/**
	 * @inherit
	 */
	public function lumiere_imdburl_to_internalurl( string $text ): string {

		// Function in abstract class.
		return parent::lumiere_imdburl_to_internalurl_abstract( $text );
	}

	/**
	 * @inherit
	 */
	public function lumiere_imdburl_of_taxonomy( string $text ): string {

		// Function in abstract class, last param for avoiding popups.
		return parent::lumiere_imdburl_of_taxonomy_abstract( $text, 1 );
	}

	/**
	 * @inherit
	 */
	public function lumiere_imdburl_of_soundtrack( string $text_url, string $text_name ): string {

		// Function in abstract class, second param for AMP.
		return parent::lumiere_imdburl_of_soundtrack_abstract( $text_url, $text_name, 1 );
	}

	/**
	 * @inherit
	 */
	public function popup_film_link( array $link_parsed, ?string $popuplarg = null, ?string $popuplong = null ): string {

		// Function in abstract class, fourth param for AMP.
		return parent::popup_film_link_abstract( $link_parsed, $popuplarg, $popuplong, 2 );
	}

	/**
	 * @inherit
	 */
	public function popup_film_link_inbox( string $title, string $imdbid, ?string $popuplarg = null, ?string $popuplong = null ): string {
		// Function in abstract class, fifth param for AMP.
		return parent::popup_film_link_inbox_abstract( $title, $imdbid, $popuplarg, $popuplong, 2 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_trailer_details( string $url, string $website_title ): string {

		// Function in abstract class, third param for links.
		return parent::lumiere_movies_trailer_details_abstract( $url, $website_title, 0 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_prodcompany_details( string $name, string $comp_id = '', string $notes = '' ): string {

		// Function in abstract class, fourth param for links.
		return parent::lumiere_movies_prodcompany_details_abstract( $name, '', '', 1 );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_officialsites_details( string $url, string $name ): string {

		// Function in abstract class, third param for links.
		return parent::lumiere_movies_officialsites_details_abstract( $url, $name, 0 );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_plot_details( string $plot ): string {

		// Function in abstract class.
		return parent::lumiere_movies_plot_details_abstract( $plot );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_source_details( string $mid ): string {

		// Function in abstract class, third param to avoid imdbelementSOURCE-picture class which breaks AMP.
		return parent::lumiere_movies_source_details_abstract( $mid, 0, '' );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {

		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		// class="imdbelementRATING-picture" breaks AMP, so remove it.
		return parent::lumiere_movies_rating_picture_abstract( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 0 );
	}
}
