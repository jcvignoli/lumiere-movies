<?php declare( strict_types = 1 );
/**
 * Class to build AMP Links
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
 * This class is used for AMP pages
 * Is called by the Link Factory class, implements abstract Link Maker class
 * 1/ External HTML links are kept
 * 2/ But no popup link is created, only links to the content of popups
 * @since 3.7.1
 */
class AMP_Links extends Implement_Methods implements Interface_Linkmaker {

	/**
	 * @inheritdoc
	 */
	public function get_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		// class="imdbelementRATING-picture" breaks AMP, so remove it.
		return parent::get_rating_picture_details( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 0 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::get_picture_details( $photo_localurl_false, $photo_localurl_true, $movie_title, parent::LINK_OPTIONS['amp'], '', 'imdbelementPICimg' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {
		// Function in abstract class, last param cut the links.
		return parent::get_medaillon_bio_details( $bio_array, parent::LINK_OPTIONS['amp'], $limit_text_bio );
	}

	/**
	 * @inheritdoc
	 */
	public function get_plot( string $plot ): string {
		// Function in abstract class.
		return parent::get_plot_details( $plot, parent::LINK_OPTIONS['amp'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_popup_people( string $imdb_id, string $name ): string {
		// Function in abstract class, before last param defines the output, last param specific <A> class.
		return parent::get_popup_people_details( $imdb_id, $name, parent::LINK_OPTIONS['amp'], 'lum_link_no_popup' );
	}

	/**
	 * @inherit
	 */
	public function get_popup_film_title( string $title, string $a_class = '' ): string {
		// Function in abstract class, second param for AMP. third alway add a specific class for links with AMP.
		return parent::get_popup_film_title_details( $title, parent::LINK_OPTIONS['amp'], $a_class );
	}

	/**
	 * @inherit
	 */
	public function get_popup_film_id( string $title, string $imdbid, string $a_class = '' ): string {
		// Function in abstract class, third param for AMP.
		return parent::get_popup_film_id_details( $title, $imdbid, parent::LINK_OPTIONS['amp'], $a_class );
	}

	/**
	 * @inheritdoc
	 */
	public function get_trailer( string $url, string $website_title ): string {
		// Function in abstract class, third param for links.
		return parent::get_trailer_details( $url, $website_title, parent::LINK_OPTIONS['amp'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_prodcompany( string $name, string $comp_id = '', string $notes = '' ): string {
		// Function in abstract class, fourth param for links.
		return parent::get_prodcompany_details( $name, '', '', parent::LINK_OPTIONS['amp'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_officialsites( string $url, string $name ): string {
		// Function in abstract class, third param for links.
		return parent::get_officialsites_details( $url, $name, parent::LINK_OPTIONS['amp'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_source( string $mid ): string {
		// Function in abstract class, third param to avoid imdbelementSOURCE-picture class which breaks AMP.
		return parent::get_source_details( $mid, parent::LINK_OPTIONS['amp'], '' );
	}
}
