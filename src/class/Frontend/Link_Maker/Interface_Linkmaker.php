<?php declare( strict_types = 1 );
/**
 * Interface for building links
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.1
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Defines methods utilised in Link Maker classes
 * @since   3.8
 */
interface Interface_Linkmaker {

	/**
	 * Source the rating picture
	 *
	 * @param int $rating The movie's score
	 * @param int $votes Number of votes
	 * @param string $votes_average_txt Vote averages in string
	 * @param string $out_of_ten_txt Of ten, text
	 * @param string $votes_txt Proper vote text
	 */
	public function get_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string;

	/**
	 * Build picture of the movie
	 *
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $movie_title Title of the movie
	 * @return string
	 */
	public function get_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string;

	/**
	 * Display mini biographical text, not all people have one
	 *
	 * 1- Cut the maximum of characters to be displayed with $click_text
	 * 2- Detect if there is html tags that can break with $esc_html_breaker
	 * 3- Build links either to internal (popups) or popups (inside posts/widgets) with $popup_links
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 * @param int $limit_text_bio Optional, increasing the hardcoded limit of characters before displaying "click for more"
	 */
	public function get_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string;

	/**
	 * Plots data details
	 *
	 * @param string $plot Text of the plot
	 */
	public function get_plot( string $plot ): string;

	/**
	 * Build link to popup for IMDb people
	 *
	 * @param string $imdb_id Movie's IMDB
	 * @param string $name Name of the person
	 * @return string
	 */
	public function get_popup_people( string $imdb_id, string $name ): string;

	/**
	 * Build a Popup movie link based on the title/name
	 *
	 * @param string $title The movie's title
	 * @param string $a_class A class to be added in popup building link, none by default
	 */
	public function get_popup_film_title( string $title, string $a_class = '' ): string;

	/**
	 * Build a Popup movie link based on the title/name *ID*
	 *
	 * @param string $title The movie's title
	 * @param string $imdbid The movie's imdbid
	 * @param string $a_class A class to be added in popup building link, none by default
	 */
	public function get_popup_film_id( string $title, string $imdbid, string $a_class = '' ): string;

	/**
	 * Build an external URL
	 *
	 * @param string $title The URL's title
	 * @param string $url The external URL
	 * @param string $a_class A class to be added in popup building link, none by default
	 */
	public function get_external_url( string $title, string $url, string $a_class = '' ): string;

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 */
	public function get_trailer( string $url, string $website_title ): string;

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $comp_id ID of the prod company
	 * @param string $notes prod company notes
	 */
	public function get_prodcompany( string $name, string $comp_id, string $notes ): string;

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the prod company
	 * @param string $name prod company name
	 */
	public function get_officialsites( string $url, string $name ): string;

	/**
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function get_source( string $mid ): string;
}
