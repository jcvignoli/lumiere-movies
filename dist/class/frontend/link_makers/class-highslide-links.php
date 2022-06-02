<?php declare( strict_types = 1 );
/**
 * Class to build Highslide links
 * Is called by the Link Factory class, implements abstract Link Maker class
 *
 * This class is used when highslide option is ticked
 *
 * Highslide Popup links are created, included in taxonomy
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;

class Highslide_Links extends Abstract_Link_Maker {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

		// Registers javascripts and styles.
		add_action( 'init', [ $this, 'lumiere_highslide_register_assets' ], 0 );

		// Execute javascripts and styles only if the vars in lumiere_highslide_options were not already enqueued
		// (prevents a bug if the vars are displayed twice, the popup doesn't open).
		add_action(
			'wp_enqueue_scripts',
			function (): void {
				if ( ! wp_script_is( 'lumiere_highslide_options', 'enqueued' ) ) {
					$this->lumiere_highslide_execute_assets();
				}
			},
			0
		);

	}

	/**
	 *  Register scripts and style.
	 *
	 */
	public function lumiere_highslide_register_assets(): void {

		wp_register_script(
			'lumiere_highslide_core',
			$this->config_class->lumiere_js_dir . 'highslide/highslide-with-html.min.js',
			[],
			$this->config_class->lumiere_version,
			true
		);
		wp_register_script(
			'lumiere_highslide_options',
			$this->config_class->lumiere_js_dir . 'highslide-options.min.js',
			[ 'lumiere_highslide_core' ],
			$this->config_class->lumiere_version,
			true
		);
		wp_enqueue_style(
			'lumiere_highslide_core',
			$this->config_class->lumiere_css_dir . 'highslide.min.css',
			[],
			$this->config_class->lumiere_version
		);
		wp_register_script(
			'lumiere_highslide_scripts',
			$this->config_class->lumiere_js_dir . 'lumiere_highslide_links.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);
	}

	/**
	 * Enqueue stylesheet & javascript.
	 *
	 */
	public function lumiere_highslide_execute_assets (): void {

		wp_enqueue_style( 'lumiere_highslide_core' );

		wp_enqueue_script( 'lumiere_highslide_core' );

		wp_enqueue_script( 'lumiere_highslide_options' );

		wp_enqueue_script( 'lumiere_highslide_scripts' );

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

		return "\n\t\t\t" . '<a class="linkincmovie modal_window_people highslide" data-modal_window_people="' . sanitize_text_field( $imdb_data_people[ $number ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . sanitize_text_field( $imdb_data_people[ $number ]['name'] ) . '</a>';

	}

	/**
	 * Build picture of the movie
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $movie_title Title of the movie
	 * @return string
	 */
	public function lumiere_link_picture ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {

		$output = '';

		// Make sure $photo_localurl_true is a string so we can use esc_html() function
		$photo_localurl_true = is_string( $photo_localurl_true ) ? $photo_localurl_true : '';

		// Select picture: if 1/ big picture exists, so use it, use thumbnail otherwise
		$photo_url = $photo_localurl_false !== false && is_string( $photo_localurl_false ) ? esc_html( $photo_localurl_false ) : esc_html( $photo_localurl_true );

		// Select picture: if 2/ big/thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url;

		$output .= "\n\t\t\t" . '<div class="imdbelementPIC">';

		// Link
		$output .= "\n\t\t\t\t" . '<a class="highslide_pic" href="'
			. $photo_url_final
			. '" title="'
			. esc_attr( $movie_title )
			. '">';

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t" . '<img loading="eager" ';

		$output .= 'class="imdbelementPICimg" src="';

		$output .= $photo_url_final
			. '" alt="'
			. esc_html__( 'Photo of', 'lumiere-movies' )
			. ' '
			. esc_attr( $movie_title ) . '"';

		// add width only if "Display only thumbnail" is unactive
		// @since 3.7
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

			$output .= ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

			// add 100px width if "Display only thumbnail" is active
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

			$output .= ' width="100em"';

		}

		$output .= ' />';

		$output .= "\n\t\t\t\t</a>";

		$output .= "\n\t\t\t" . '</div>';

		return $output;

	}

	/**
	 * Build picture of the movie in taxonomy pages
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $person_name Name of the person
	 *
	 * @return string
	 */
	public function lumiere_link_picture_taxonomy ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $person_name ): string {

		// Function in abstract class, last param defines the output.
		return $this->lumiere_link_picture_taxonomy_abstract( $photo_localurl_false, $photo_localurl_true, $person_name, 0 );

	}

	/**
	 * Display mini biographical text, not all people have one
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 *
	 * @return ?string
	 */
	public function lumiere_medaillon_bio ( array $bio_array ): ?string {

		// Function in abstract class.
		return $this->lumiere_medaillon_bio_abstract( $bio_array );

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
	 * Build links using highslide popup
	 *
	 * @param string $text Text that includes IMDb URL to convert into a popup link
	 */
	public function lumiere_imdburl_to_popupurl ( string $text ): string {

		// Function in abstract class.
		return $this->lumiere_imdburl_to_popupurl_abstract( $text, 0, 'highslide' );

	}

	/**
	 * Highslide popup function
	 * Build an HTML link to open a popup with highslide for searching a movie (using js/lumiere_highslide_links.js)
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param string $popuplarg -> window width, if nothing passed takes database value
	 * @param string $popuplong -> window height, if nothing passed takes database value
	 */
	public function lumiere_popup_film_link ( array $link_parsed, string $popuplarg = null, string $popuplong = null ): string {

		// Function in abstract class, fourth param for bootstrap.
		return $this->lumiere_popup_film_link_abstract( $link_parsed, $popuplarg, $popuplong );
	}

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 */
	public function lumiere_movies_trailer_details ( string $url, string $website_title ): string {

		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html__( 'Watch on IMBb website the trailer for ', 'lumiere-movies' ) . esc_html( $website_title ) . "'>" . sanitize_text_field( $website_title ) . '</a>';

	}

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $url Url to the prod company
	 * @param string $notes prod company notes
	 */
	public function lumiere_movies_prodcompany_details ( string $name, string $url, string $notes ): string {

		$output = '';
		$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
		$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
		$output .= "\n\t\t\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html( $name ) . "'>";
		$output .= esc_attr( $name );
		$output .= '</a>';
		$output .= "\n\t\t\t\t</div>";
		$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
		if ( strlen( $notes ) !== 0 ) {
			$output .= esc_attr( $notes );
		} else {
			$output .= '&nbsp;';
		}
		$output .= '</div>';
		$output .= "\n\t\t\t</div>";

		return $output;

	}

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the prod company
	 * @param string $name prod company name
	 */
	public function lumiere_movies_officialsites_details ( string $url, string $name ): string {

		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_attr( $name ) . "'>"
			. esc_html( $name )
			. '</a>';
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
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function lumiere_movies_source_details ( string $mid ): string {

		return "\n\t\t\t" . '<img class="imdbelementSOURCE-picture" alt="link to imdb" width="33" height="15" src="' . esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/imdb-link.png' ) . '" />'
		. '<a class="link-incmovie-sourceimdb" title="'
				. esc_html__( 'Go to IMDb website for this movie', 'lumiere-movies' ) . '" href="'
				. esc_url( 'https://www.imdb.com/title/tt' . $mid ) . '" >'
				. '&nbsp;&nbsp;'
				. esc_html__( "IMDb's page for this movie", 'lumiere-movies' ) . '</a>';

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
