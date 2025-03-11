<?php declare( strict_types = 1 );
/**
 * Interface for building links
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.1
 * @since   3.8
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Defines methods utilised in Link Maker classes
 */
interface Interface_Link_Maker {

	/**
	 * Build link to popup for IMDb people
	 *
	 * @param string $imdb_id Movie's IMDB
	 * @param string $name Name of the person
	 * @return string
	 */
	public function get_popup_people( string $imdb_id, string $name ): string;

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
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 */
	public function lumiere_imdburl_to_internalurl( string $text ): string;

	/**
	 * Inside a post Popup movie builder
	 * Build an HTML link inside the posts to open a popup
	 *
	 * @param string $title_or_name Either the movie's title or person name found in inside the post
	 * @param null|string $popuplarg -> window width, if nothing passed takes database value
	 * @param null|string $popuplong -> window height, if nothing passed takes database value
	 */
	public function replace_span_to_popup( string $title_or_name, ?string $popuplarg = null, ?string $popuplong = null ): string;

	/**
	 * Inside a post Popup film builder
	 * Build an HTML link to open a popup for a movie inside the posts
	 *
	 * @param string $title The movie's title
	 * @param string $imdbid The movie's imdbid
	 * @param null|string $popuplarg -> window width, if nothing passed takes database value
	 * @param null|string $popuplong -> window height, if nothing passed takes database value
	 */
	public function get_popup_film( string $title, string $imdbid, ?string $popuplarg = null, ?string $popuplong = null ): string;

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
	 * Plots data details
	 *
	 * @param string $plot Text of the plot
	 */
	public function get_plot( string $plot ): string;

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
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function get_source( string $mid ): string;
}
